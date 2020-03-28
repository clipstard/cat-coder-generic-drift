<?php

namespace App\Service;

class Solver
{
    /** @var string $projectDir */
    protected $projectDir;

    /** @var FileReader $fileReader */
    protected $fileReader;

    public function __construct(
        string $projectDir,
        FileReader $fileReader
    )
    {
        $this->projectDir = $projectDir;
        $this->fileReader = $fileReader->setLevel(2)->setSubLevel(3);
    }

    public function solve($level = 1, $sublevel = 0, $run = false)
    {
        ini_set('memory_limit', '2G');
        if ($run) {
            $this->fileReader->setLevel($level)->setSubLevel($sublevel);
        }

        $input = $this->fileReader->read();
        $intValue = [];
        foreach ($input as $item) {
            $intValue[] = (int)$item;
        }

//        $nrOfTests = $intValue[0];
        $u = 0;
        $size = [$intValue[$u++], $intValue[$u++]];
        $nrOfPoints = $intValue[$u++];
        $data = array_slice($intValue, $u);
        /** @var Point[] $points */
        $points = [];
        $table = new Table($size[0], $size[1]);

        $a = 1;
        $coords = [];
        for ($i = 0; $i < $nrOfPoints * 2; $i += 2) {
            $points[] = new Point($data[$i], $data[$i + 1]);
        }

        foreach ($points as $point) {
            [$x, $y] = $table->getCoordsByPosition($point->getPosition());
            $point->setX($x)->setY($y);
        }

        $result = [];
//        foreach ($coords as $coord) {
//            foreach ($coord as $value) {
//                $result[] = $value;
//            }
//        }

        $calculatedColors = [];
        $distances = [];
        Point::sortByPosition($points);
        $c = count($points);
        /** @var Point $point */
        for ($i = 0; $i < $nrOfPoints; $i++) {
            $color = $points[$i]->getColor();
            if (!in_array($color, $calculatedColors)) {
                $distances[$color] = $points[$i]->getManhattanDistance($this->findNextPointByColor($points, $i+1, $color));
                $calculatedColors[] = $color;
            }
        }
        ksort($distances);

        $this->fileReader->write($distances, ' ');
//        $sortedPoints = $points;
//        Point::sortByPosition($sortedPoints);

//        /** @var Point $point */
//        foreach ($points as $point) {
//            $table->fillPoint($point);
//        }

        return $this->jsonify($distances, 30, false);
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

    public function jsonify($data, $limit = 100, $withCount = true)
    {
        if (!is_array($data)) {
            return "" . $data;
        }

        $str = '';

        if (is_array($data) && !is_array($data[array_key_first($data)])) {
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

        if (is_array($data) && is_array($data[array_key_first($data)])) {
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
