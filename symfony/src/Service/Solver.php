<?php

namespace App\Service;

use App\Entity\Service\Coord;
use App\Entity\Service\Pair;
use App\Entity\Service\Point;
use App\Entity\Service\Table;

class Solver
{
    /** @var string $projectDir */
    protected $projectDir;

    const R = 6371000.0;

    /** @var FileReader $fileReader */
    protected $fileReader;

    public function __construct(
        string $projectDir,
        FileReader $fileReader
    )
    {
        $this->projectDir = $projectDir;
        $this->fileReader = $fileReader->setLevel(4)->setSubLevel('5');
    }

    public function solveFirstLevel()
    {
        $data = $this->fileReader->read(' ');

        /** @var Coord[] $coords */
        $coords = $this->readCoords($data);
        $result = [];

        foreach ($coords as $coord) {
            $result[] = $this->getCoordsAtTime($coord);
        }

        $this->fileReader->write($result);

        return $this->jsonify($result, 10, false);
    }

    public function getCoordsAtTime(Coord $coord)
    {
        /** @var Coord[] $flightData */
        $flightData = $this->fileReader->getFlightData($coord->getFlightId());
        $timeOffset = $coord->getTime() - $flightData[0]->getTime();

        $c = count($flightData);
        for ($i = 0; $i < $c; $i++) {
            if ($flightData[$i]->getTimeOffset() === $timeOffset) {
                return [
                    $flightData[$i]->getLat(),
                    $flightData[$i]->getLong(),
                    $flightData[$i]->getAlt()
                ];
            }

            if ($flightData[$i]->getTimeOffset() > $timeOffset) {
                $before = $flightData[$i - 1];
                $after = $flightData[$i];
                return [
                    $this->getInterLat($before, $after, $timeOffset),
                    $this->getInterLong($before, $after, $timeOffset),
                    $this->getInterAlt($before, $after, $timeOffset),
                ];
            }
        }

        return [0, 0, 0];
    }

    public function getInterAlt(Coord $coord1,Coord $coord2, $x2)
    {
        $x1 = $coord1->getTimeOffset();
        $x3 = $coord2->getTimeOffset();
        $y1 = $coord1->getAlt();
        $y3 = $coord2->getAlt();
        return (((($x2 - $x1) * ($y3 - $y1)) / ($x3 - $x1)) + $y1);
    }

    public function getInterLat(Coord $coord1,Coord $coord2, $x2)
    {
        $x1 = $coord1->getTimeOffset();
        $x3 = $coord2->getTimeOffset();
        $y1 = $coord1->getLat();
        $y3 = $coord2->getLat();
        $y2 = ((($x2 - $x1) * ($y3 - $y1)) / ($x3 - $x1)) + $y1;
        return $y2;
    }

    public function getInterLong(Coord $coord1,Coord $coord2, $x2)
    {
        $x1 = $coord1->getTimeOffset();
        $x3 = $coord2->getTimeOffset();
        $y1 = $coord1->getLong();
        $y3 = $coord2->getLong();
        $y2 = ((($x2 - $x1) * ($y3 - $y1)) / ($x3 - $x1)) + $y1;

        return $y2;
    }


    public function transform($lat, $long, $alt)
    {
        $rLat = ($lat * M_PI / 180);
        $rLong = ($long * M_PI / 180);
        $x = (self::R + $alt) * cos($rLat) * cos($rLong);
        $y = (self::R + $alt) * cos($rLat) * sin($rLong);
        $z = (self::R + $alt) * sin($rLat);

        return [$x, $y, $z];
    }

    /**
     * @param Coord[] $array
     * @return array
     */
    public function distinctFlights($array)
    {
        $arr = [];
        $c = count($array);
        for ($i = 0; $i < $c - 1; $i++) {
            $found = true;
            for ($j = $i + 1; $j < $c; $j++) {
                if (
                    $array[$i]->getFrom() === $array[$j]->getFrom() &&
                    $array[$i]->getTo() === $array[$j]->getTo()
                ) {
                    $key = $array[$i]->getFrom() . $array[$i]->getTo();
                    $found = false;
                    if (array_key_exists($key, $arr)) {
                        if ($arr[$key]->getTakeoff() !== $array[$i]->getTakeoff() && $arr[$key]->getId() !== $array[$i]->getId()) {
                            $arr[$key]->inc();
                        }
                    } else {
                        $arr[$key] = $array[$i]->inc();
                    }
                }
            }

            if ($found) {
                $key = $array[$i]->getFrom() . $array[$i]->getTo();
                if (array_key_exists($key, $arr)) {
                    if ($arr[$key]->getTakeoff() !== $array[$i]->getTakeoff() && $arr[$key]->getId() !== $array[$i]->getId()) {
                        $arr[$key]->inc();
                    }
                } else {
                    $arr[$key] = $array[$i]->inc();
                }
            }
        }

        return $arr;
    }

    /**
     * @param Coord[] $array
     * @return array
     */
    public function distinct2($array)
    {
        $arr = [];
        $times = [];
        $c = count($array);
        /** @var Coord $item */
        foreach ($array as $item) {
            $key = $item->getFrom() . $item->getTo();
            if (!array_key_exists($key, $arr)) {
                $arr[strtoupper($key)] = $item;
            }
        }

        foreach ($array as $item) {
            $key = strtoupper($item->getFrom() . $item->getTo());
            if (!array_key_exists($key, $times)) {
                $times[$key] = [];
            }

            $times[$key][] = $item->getTakeoff();
        }

        foreach ($times as $key => $time) {
            $arr[$key]->setCount($this->countUniques($time));
        }

        return $arr;
    }

    public function countUniques($array)
    {
        $c = count($array);
        $count = 0;
        $items = [];
        $found = false;
        foreach ($array as $item) {
            if (!in_array($item, $items, true)) {
                $items[] = $item;
            }
        }

        return count($items);
    }

    public function removeDuplicates($array)
    {
        $arr = [];
        foreach ($array as $item) {
            if (!in_array($item, $arr, true)) {
                $arr[] = $item;
            }
        }

        return $arr;
    }

    public function getMinMax($array, $direction, $property = 'time')
    {
        $property = ucfirst($property);

        $min = 999999.0;
        $max = -99990.0;
        $c = count($array);
        for ($i = 0; $i < $c - 1; $i++) {
            if ($array[$i]->{'get' . $property}() < $min) {
                $min = $array[$i]->{'get' . $property}();
            }

            if ($array[$i]->{'get' . $property}() > $max) {
                $max = $array[$i]->{'get' . $property}();
            }
        }

        return $direction ? $min : $max;
    }

    public function readCoords($data)
    {
        $arr = [];
        $rows = (int)$data[0][0];
        $c = count($data);
        $a = 0;
        for ($i = 1; $i <= $rows; $i++) {
            $arr[] = new Coord((int)$data[$i][0], (int)$data[$i][1]);
        }

        return $arr;
    }

    public function getItemByIndex($array, $index)
    {
        $a = 0;

        foreach ($array as $item) {
            if ($a++ === $index) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param Table $table
     * @param $path
     * @return mixed
     */
    public function followPath($table, $path)
    {
        $u = 0;
        [$color, $starting, $length] = $path;
        $c = count($path);
        $steps = 0;
        for ($i = 3; $i < $c; $i++) {
            try {
                $starting = $table->doAction($starting, $color, $path[$i]);
            } catch (\Exception $exception) {
                $steps++;
                break;
            }

            $steps++;
        }

        return [$steps, $starting];
    }

    /**
     * @param Point[]|array $points
     * @param $index
     * @param $color
     * @return Point|null
     */
    private function findNextPointByColor($points, $index, $color): ?Point
    {
        $c = count($points);
        for ($i = $index; $i < $c; $i++) {
            if ($points[$i]->getColor() === $color) {
                return $points[$i];
            }
        }

        return null;
    }

    /**
     * @param $array
     * @return Pair[]|array
     */
    private function findPairs($array)
    {
        $pairs = [];

        $c = count($array);
        $lastPair = null;
        for ($i = 0; $i < ($c - 1); $i++) {
            for ($j = ($i + 1); $j < $c; $j++) {
                $sum = $array[$i] + $array[$j];
                if ($sum === 1 || $sum === -1) {
                    $pairs[] = new Pair($array[$i], $array[$j], $i, $j);
                }
            }
        }

//        Pair::sortByX($pairs);
        return $pairs;
    }

    private function isSorted(&$array)
    {
        $c = count($array);
        for ($i = 0; $i < $c - 1; $i++) {
            for ($j = $i + 1; $j < $c; $j++) {
                if ($array[$i] > $array[$j]) {
                    return false;
                }
            }
        }

        return true;
    }

    public function jsonify($data, $limit = 100, $withCount = false)
    {
        if (!is_array($data)) {
            return "" . $data;
        }

        $str = '';


        if (is_array($data) && count($data) && !is_array($this->getItemByIndex($data, 0))) {
            $c = count($data);
            if ($withCount) {
                $str .= $c . ' ';
            }

            $keys = array_keys($data);
            for ($i = 0; $i < $c; $i++) {
                $str .= "{$data[$keys[$i]]} ";
                if ($i && $i % $limit === 0) {
                    $str .= "<br />";
                }
            }
        }

        if (is_array($data) && count($data) && is_array($this->getItemByIndex($data, 0))) {
            foreach ($data as $row) {
                $c = count($row);
                $keys = array_keys($data);
                for ($i = 0; $i < $c; $i++) {
                    $str .= "{$row[$keys[$i]]}, ";
                    if ($i && $i % $limit === 0) {
                        $str .= "<br />";
                    }
                }
                $str .= '---------------------------------------------------------<br />';
            }
        }

        return $str;
    }
}
