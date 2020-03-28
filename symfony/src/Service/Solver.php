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
        $this->fileReader = $fileReader->setLevel(2)->setSubLevel(0);
    }

    public function solve($level = 1, $sublevel = 0, $run = false)
    {
        if ($run) {
            $this->fileReader->setLevel($level)->setSubLevel($sublevel);
        }

        $input = $this->fileReader->read();
        $intValue = [];
        foreach ($input as $item) {
            $intValue[] = (int)$item;
        }

//        $nrOfTests = $intValue[0];
        $size = [$intValue[0], $intValue[1]];
        $nrOfPoints = $intValue[2];
        $data = array_slice($intValue, 3);
        /** @var Point[] $points */
        $points = [];
        $table = new Table($size[0], $size[1]);
        $c = count($data);

        $a = 1;
        $coords = [];
        for ($i = 0; $i < $nrOfPoints; $i++) {
            $points[] = new Point($data[$i], $a++);
        }

        foreach ($points as $point) {
            $coords[] = $table->getCoordsByPosition($point->getPosition());
        }

        $result = [];
        foreach ($coords as $coord) {
            foreach ($coord as $value) {
                $result[] = $value;
            }
        }

        $this->fileReader->write($result, ' ');
//        $sortedPoints = $points;
//        Point::sortByPosition($sortedPoints);

//        /** @var Point $point */
//        foreach ($points as $point) {
//            $table->fillPoint($point);
//        }

        return $this->jsonify($result, 30, false);
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
        if (is_string($data)) {
            return $data;
        }

        $str = '';

        if (is_array($data) && !is_array($data[0])) {
            $c = count($data);
            if ($withCount) {
                $str .= $c . ' ';
            }

            for ($i = 0; $i < $c; $i++) {
                $str .= "{$data[$i]} ";
                if ($i && $i % $limit === 0) {
                    $str .= "<br />";
                }
            }
        }

        if (is_array($data) && is_array($data[0])) {
            foreach ($data as $row) {
                $c = count($row);
                for ($i = 0; $i < $c; $i++) {
                    $str .= "{$row[$i]}, ";
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
