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
        $this->fileReader = $fileReader->setLevel(3);
    }

    /**
     * @param mixed ...$args
     * @return array|bool
     * @throws \Exception
     */
    public function solve(...$args)
    {
        $data = '';
        $arr = explode(' ', $data);
        $intValue = [];
        foreach ($arr as $item) {
            $intValue[] = (int)$item;
        }

        $c = count($intValue);

        $pairs = $this->findPairs($intValue);
        return $this->jsonify($pairs);

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

        return $pairs;
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

    public function sortByX(&$array)
    {
        $c = count($array);
        for ($i = 0; $i < ($c - 1); $i++) {
            for ($j = 1; $j < $c; $j++) {
                if ($array[$i]['x'] > $array[$j]['x']) {
                    [$array[$i], $array[$j]] = [$array[$j], $array[$i]];
                }
            }
        }
    }

}
