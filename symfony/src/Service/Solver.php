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
        $this->fileReader = $fileReader->setLevel(6)->setSubLevel('5');
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

        return $this->helper->jsonify($this->solveLevel4($data));
    }

    public function solveLevel4($data)
    {
        [$maxPower, $maxCost, $maxConcurrent, $yPoint, $lines, $nrOfTasks, $tasks] = $this->helper->readLevel($data);
        $results = [];

        $tasks = $this->sortTasksByInterval($tasks);

        $lines = $this->fillPower($lines, $maxPower, $maxConcurrent);

        foreach ($tasks as $task) {
            $results[] = $this->findConsumption($lines, $task, $maxCost, $maxPower);
        }

        $this->sortById($results);

        $results = array_merge([[$nrOfTasks]], $results);
        $this->helper->writeLevel($results);

        return $results;
    }

    public function findConsumption(&$lines, $task, $maxCost, $maxPower)
    {
        [$id, $power, $start, $end] = $task;

        $cost = 0;
        $consumed = [];

        for ($i = 0; $i < $power; $i++) {
            $index = $this->findLowerCostId($lines, $start, $end + 1);

            if ($lines[$index][2] === 1 && $lines[$index][1] >= $i) {
                if (array_key_exists($index, $consumed)) {
                    $consumed[$index]+= $lines[$index][1];
                } else {
                    $consumed[$index] = $lines[$index][1];
                }

                $i += ($lines[$index][1] - 1);
            }

            if ($lines[$index][1] > 0) {
                $lines[$index][1]--;
                if (array_key_exists($index, $consumed)) {
                    $consumed[$index]++;
                } else {
                    $consumed[$index] = 1;
                }

                $cost += $lines[$index][0];
            } else {
                $index = $this->findLowerCostId($lines, $start, $end + 1);
                $lines[$index][1]--;
                if (array_key_exists($index, $consumed)) {
                    $consumed[$index]++;
                } else {
                    $consumed[$index] = 1;
                }

                $cost += $lines[$index][0];
            }

            if ($lines[$index][1]) {
                $lines[$index][0] = (int) ceil($lines[$index][0] * (1 + (((int)$maxPower - (int)$lines[$index][1]) / ((int)$maxPower))));
            }
        }


        $lines[$index][2]--;
        $values = [];

        foreach ($consumed as $key => $value) {
            $values[] = $key;
            $values[] = $value;
        }

        if ($cost > $maxCost) {
            dump($cost, $maxCost); die;
        }

        return array_merge([$id], $values);
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

    public function sortTasksByInterval($tasks)
    {
        $c = count($tasks);
        usort($tasks, function($i1, $i2){
            $v1 = $i1[3] - $i1[2];
            $v2 = $i2[3] - $i2[2];
            if ($v1 == $v2) {
                return 0;
            }

            return ($v1 < $v2) ? -1 : 1;
        });

        $this->sortTasksByConsumption($tasks);

        return $tasks;
    }

    public function sortTasksByConsumption(&$tasks)
    {
        $c = count($tasks);

        for ($i = 0; $i < $c - 1; $i++) {
            for ($j = $i + 1; $j < $c; $j++ ) {
                $left = $tasks[$i];
                $right = $tasks[$j];
                if ($left[3] - $left[2] === $right[3] - $right[2] && $left[1] > $right[1]) {
                    [$tasks[$i], $tasks[$j]] = [$tasks[$j], $tasks[$i]];
                }
            }
        }
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
        $solution = $from;
        $c = count($lines);
        if (!$to) {
            $to = $c;
        }

        for ($i = $from; $i < $to; $i++) {
            if ($lines[$i][0] < $min && $lines[$i][1] > 0 && $lines[$i][2] > 0) {
                $min = $lines[$i][0];
                $solution = $i;
            }
        }

        return $solution;
    }

    public function sortById(&$results)
    {
        $c = count($results);
        for ($i = 0; $i < $c - 1; $i++) {
            for ($j = $i + 1; $j < $c; $j++ ) {
                if ($results[$i][0] > $results[$j][0]) {
                    [$results[$i], $results[$j]] = [$results[$j], $results[$i]];
                }
            }
        }
    }

    public function fillPower($lines, $power, $maxConcurrent)
    {
        return array_map(function ($line) use ($power, $maxConcurrent) { return [$line, $power, $maxConcurrent]; }, $lines);
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
