<?php

namespace App\Service;

use App\Entity\Service\Earning;
use App\Entity\Service\Pair;
use App\Entity\Service\Point;
use App\Entity\Service\Table;

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
        $this->fileReader = $fileReader->setLevel(1)->setSubLevel('0');
    }

    public function solveFirstLevel()
    {
        $data = $this->fileReader->read();
//        $data = "F 1 739 F 2 164 F 3 227 F 4 778 F 5 423 F 6 538 F 7 155 F 8 425 F 9 878 B 1 739 B 2 164 B 3 227 B 4 778 B 5 423 B 6 538 B 7 155 B 8 425 B 9 878";

//        $arrData = explode(' ', $data);

        /** @var Earning[] $earnings */
        $earnings = $this->readEarnings($data);

        Earning::orderBy($earnings);

        $results = [];
        $fEarnings = [];
        /** @var Earning[] $bEarnings */
        $bEarnings = [];

        foreach ($earnings as $earning) {
            if ($earning->getDestination() === 'B') {
                $bEarnings[] = $earning;
            } else {
                $fEarnings[] = $earning;
            }
        }

        $i = 0;
        $c = count($bEarnings);
        $sum = 0;

        /** @var Earning $f */
        foreach ($fEarnings as $f) {
            $amount = $f->getAmount();
            $earningsInRange = $this->getEarningsInRange($bEarnings, $f->getFrom(), $f->getTo());
            $payed = $this->findExactAmount($earningsInRange, $amount);
            if (!$payed) {
                $results[] = $f->getFrom();
            }
        }

        $results = $this->removeDuplicates($results);

//        $lastDay = $fEarnings[count($fEarnings) - 1]->getDay();
        return $this->jsonify(array_merge($fEarnings, $bEarnings), 1, false);
    }

    /**
     * @param Earning[]|array $earnings
     * @param $from
     * @param $to
     * @return Earning[]|array
     */
    private function getEarningsInRange($earnings, $from, $to)
    {
        /** @var Earning[] $arr */
        $arr = [];
        /** @var Earning $earning */
        foreach ($earnings as $earning) {
            $d = $earning->getDay();
            if ($d >= $from && $d < $to) {
                $arr[] = $earning;
            }
        }

        return $arr;
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

    private function findGreaterAmount($earnings, $amount): bool
    {
        $sum = 0;
        foreach ($earnings as $earning) {
            $sum += $earning->getAmount();
        }

        return $sum >= $amount;
    }

    private function findExactAmount($bEarnings, $amount): int
    {
        $canBe = [];
        /** @var Earning $earning */
        foreach ($bEarnings as $earning) {
            if (!$earning->isActive()) continue;

            $payed = $earning->getAmount();
            if ($payed === $amount) {
               $earning->setActive(false);
               return $amount;
            }

            if ($payed < $amount) {
                $canBe[] = $earning;
            }
        }

        return $this->findPayedSums($canBe, $amount);
    }

    /**
     * @param Earning[] $earnings
     * @param int $amount
     * @return int
     */
    private function findPayedSums($earnings, $amount)
    {
        if ($payed = $this->findSameSum($earnings, $amount)) {
            return $payed;
        }


        $c = count($earnings);

        if ($c && $c < 4) {
            return $this->isMakingSum($amount, ...$earnings) ? $amount : 0;
        }

        for ($i = 0; $i < $c - 3; $i++) {
            for ($j = $i + 1; $j < $c - 2; $j++) {
                if ($this->isMakingSum($amount, $earnings[$i], $earnings[$j])) {
                    return $amount;
                }

                for ($k = $j + 1; $k < $c - 1; $k++) {
                    if (
                        $this->isMakingSum($amount, $earnings[$j], $earnings[$k]) ||
                        $this->isMakingSum($amount, $earnings[$j], $earnings[$k], $earnings[$i])
                    ) {
                        return $amount;
                    }

                    for($l = $k + 1; $l < $c; $l++) {
                        if (
                            $this->isMakingSum($amount, $earnings[$l], $earnings[$k]) ||
                            $this->isMakingSum($amount, $earnings[$j], $earnings[$k], $earnings[$l]) ||
                            $this->isMakingSum($amount, $earnings[$j], $earnings[$k], $earnings[$l], $earnings[$i])
                        ) {
                            return $amount;
                        }
                    }
                }
            }
        }

        return 0;
    }

    private function isMakingSum($amount, ...$args): bool
    {
        $sum = 0;
        /** @var Earning $arg */
        foreach ($args as $arg) {
            $sum += $arg->getAmount();
        }

        if ($amount === $sum) {
            foreach ($args as $arg) {
                $arg->setActive(false);
            }
        }

        return $amount === $sum;
    }

    private function findSameSum($earnings, $amount, $notInclude = []): int
    {
        $c = count($earnings);
        $found = [];
        for ($i = 0; $i < $c - 1; $i++ ) {
            if (!$earnings[$i]->isActive()) continue;
            $a = $earnings[$i]->getAmount();
            if (count($found) && $this->getItemByIndex($found, 0)->getAmount() !== $a) continue;

            for ($j = $i + 1; $j < $c; $j++) {
                if (!$earnings[$j]->isActive()) continue;
                if ($a === $earnings[$j]->getAmount() && !in_array($a, $notInclude, true)) {
                    $found[$earnings[$i]->getId()] = $earnings[$i];
                    $found[$earnings[$j]->getId()] = $earnings[$j];
                }
            }
        }

        $f = count($found);
        if ($f && $this->getItemByIndex($found, 0)->getAmount() * $f === $amount) {
            foreach ($found as $item) {
                $item->setActive(false);
            }

            return $amount;
        }

        if ($f) {
            return $this->findSameSum($earnings, $amount, array_merge($notInclude, [$this->getItemByIndex($found, 0)->getAmount()]));
        }

        return 0;
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

    private function readEarnings($data)
    {
        $c = count ($data);
        $arr = [];
        $a = 1;
        for ($i = 0; $i < $c;) {
            $destination = $data[$i];
            $e = new Earning($destination);

            if ($destination === 'F') {
                $e
                    ->setDay($data[$i + 1])
                    ->setDriver($data[$i + 2])
                    ->setFrom($e->getDay())
                    ->setTo($e->getDay() + $data[$i + 4])
                    ->setAmount($data[$i + 4]);
                $i += 4;
            } else {
                $e->setDay($data[$i + 1])
                    ->setAmount($data[$i + 2]);
                $i += 3;
            }

            $e->setId($a++);
            $arr[] = $e;
        }

        return $arr;
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
