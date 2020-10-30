<?php

namespace App\Service;

class Solver
{
    /** @var string $projectDir */
    protected $projectDir;

    /** @var FileReader $fileReader */
    protected $fileReader;

    protected $helper;

    public function __construct(
        string $projectDir,
        FileReader $fileReader,
        Helper $helper
    )
    {
        $this->projectDir = $projectDir;
        $this->fileReader = $fileReader->setLevel(3)->setSubLevel('3');
        $this->helper = $helper;
    }

    /**
     * @param mixed ...$args
     * @return array|bool
     * @throws \Exception
     */
    public function solve(...$args)
    {
        $data = $this->fileReader->read(' ', true);

        return $this->helper->jsonify($this->solveLevel3($data));
    }

    public function solveLevel3($data)
    {
        [$nrOfLines, $lines, $nrOfTasks, $tasks] = $this->helper->readLevel($data);
        $results = [[$nrOfTasks]];

        foreach ($tasks as $task) {
            [$id, $power, $start, $end] = $task;
            $min = $this->findLowerCostId($lines, $start, $end + 1);

            $results[] = [$id, $min, $power];
        }

        $this->helper->writeLevel($results);

        return $data;
    }

    public function findPointsInSameRegion($yPoint, $nr, $lines)
    {
        $points = [];

        foreach ($lines as $line) {
            [$x, $y] = $line;

            if ($yPoint > 0 && $y < $yPoint) {
                $points[] = $line;
            } elseif ($yPoint < 0 && $y > $yPoint) {
                $points[] = $line;
            }
        }

        return $points;
    }

    function solveLevel2($data) {
        [$nr, $lines, $nrOfTasks, $tasks] = $this->helper->readLevel($data);

        $result = [];

        foreach ($tasks as $task) {
            [$id, $cost] = $task;

            $minute = $this->findStartMinuteForTask($lines, $cost);
            $result[] = [$id, $minute];
        }

        $result = array_merge([[$nrOfTasks]], $result);

        $this->helper->writeLevel($result);

        return $result;
    }

    public function findStartMinuteForTask($lines, $cost)
    {
        $c = count($lines);

        $min = 99999999;
        $result = 0;
        $sum = 0;
        $start = 0;
        $finish = $cost;

        for ($i = 0; $i < $c; $i++) {
            if ($sum === 0) {
                $sum = $this->helper->sumNext($lines, 0, $cost);
            } else {
                if ($finish < $c) {
                    $sum -= $lines[$start++];
                    $sum += $lines[$finish++];
                }
            }

            if ($sum < $min) {
                $min = $sum;
                $result = $start;
            }
        }

        return $result;
    }

    function solveLevel1($data) {
        [$yPoint, $lines] = $this->helper->readLevel($data);

        $response = $this->findLowerCostId($lines);

//        $points = $this->findPointsInSameRegion($yPoint, $lines);

        $this->helper->writeLevel($response);
        return $response;
    }

    public function findLowerCostId($lines, $from = 0, $to = null)
    {
        $min = 999999;
        $solution = null;
        $c = count($lines);
        if (!$to) {
            $to = $c;
        }

        for ($i = $from; $i < $to; $i++) {
            if ($lines[$i] < $min) {
                $min = $lines[$i];
                $solution = $i;
            }
        }

        return $solution;
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
}
