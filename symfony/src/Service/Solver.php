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
        $this->fileReader = $fileReader->setLevel(3)->setSubLevel('02');
    }

    public function solve($level = 1, $sublevel = 0, $run = false)
    {
        if ($run) {
            $this->fileReader->setLevel($level)->setSubLevel($sublevel);
        }

        $input = $this->fileReader->read();
        $intValue = [];
        foreach ($input as $item) {
            $intItem = (int) $item;
            if (!$intItem) {
                $intValue[] = $item;
            } else {
                $intValue[] = (int)$item;
            }
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
        $p = 0;
        $coords = [];
        for ($i = 0; $i < $nrOfPoints * 2; $i += 2) {
            $points[] = new Point($data[$i], $data[$i + 1]);
            $p = $i + 2;
        }

        foreach ($points as $point) {
            $table->fillPoint($point);
        }

        $nrOfPaths = $data[$p++];
        $pathsData = array_slice($data, $p);
        $c = count($pathsData);

        $paths = [];

        for ($i = 0; $i < $nrOfPaths; $i++) {
            $paths[] = $this->slicePath($pathsData);
        }

        $exit = [];
        $results = [];

        foreach ($paths as $path) {
            [$length, $position] = $this->followPath($table, $path);
            $flag = false;
            foreach ($points as $point) {
                if ($position !== $path[1] && $point->getPosition() === $position) {
                   $flag = true;
                }
            }

            $results[] = $flag ? 1 : -1;
            $results[] = $length;
        }

        $this->fileReader->write($results, ' ');
//        $sortedPoints = $points;
//        Point::sortByPosition($sortedPoints);

//        /** @var Point $point */
//        foreach ($points as $point) {
//            $table->fillPoint($point);
//        }

        return $this->jsonify($results, 30, false);
    }

    public function slicePath(&$data)
    {
        $length = $data[2];
        $start = $length + 3;

        $return = array_slice($data, 0, $start);
        $data = array_slice($data, $start);
        return $return;
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

        if (is_array($data) && isset($data[array_key_first($data)]) && is_array($data[array_key_first($data)])) {
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
