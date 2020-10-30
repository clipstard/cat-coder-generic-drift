<?php

namespace App\Service;

class Helper
{
    /** @var FileReader $fileReader */
    private $fileReader;

    public function __construct(FileReader $fileReader)
    {
        $this->fileReader = $fileReader;
    }

    public function jsonify($data, $limit = 100, $withCount = true)
    {
        if (is_string($data)) {
            return $data;
        }

        $str = '';

        if (is_array($data) && (count($data)) && !is_array($data[array_key_first($data)])) {
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

        if (is_array($data) && count($data) && is_array($data[array_key_first($data)])) {
            foreach ($data as $row) {
                $c = count($row);
                for ($i = 0; $i < $c; $i++) {
                    $str .= "{$row[$i]}, ";
                    if ($i && $i % $limit === 0) {
                        $str .= "<br />";
                    }
                }

                $str .= '<br />';
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

    function readLevel($data) {
        $yPoint = null;
        $nrOfTasks = null;
        $lines = [];
        $tasks = [];

        foreach ($data as $index => $row) {
            if ($index === 0) {
                if (is_array($row)) {
                    $yPoint = (int)$row[0];
                } else {
                    $yPoint = (int)$row;
                }
            }

            if ($index > 0 && $index <= $yPoint) {
                $lines[] = is_array($row) ? $row[0] : $row;
            }

            if ($index > $yPoint) {
                if ($index === $yPoint + 1) {
                        $nrOfTasks =  is_array($row) ? (int) $row[0] : $row;
                } else {
                    $tasks[] = $row;
                }
            }
        }

        return [$yPoint, $lines, $nrOfTasks, $tasks];
    }

    function writeLevel($data)
    {
        $this->fileReader->write($data);
    }

    public function matrixToArray($data)
    {
        $items = [];
        if (is_array($data) && count($data)) {
            foreach ($data as $item) {
                if (is_array($item) && count($item)) {
                    $items = array_merge($items, $this->matrixToArray($item));
                } else {
                    $items[] = $item;
                }
            }
        } else {
            $items[] = $data;
        }

        return $items;
    }

    public function sumNext($lines, $start, $amount)
    {
        $sum = 0;
        for ($i = $start; $i < $start + $amount; $i++ ) {
            $sum += $lines[$i];
        }

        return $sum;
    }

}
