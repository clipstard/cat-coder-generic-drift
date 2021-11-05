<?php

namespace App\Service;

use App\Entity\Player;

class Solver
{
    /** @var string $projectDir */
    protected $projectDir;

    /** @var FileReader $fileReader */
    protected $fileReader;
    protected $winIncrement = 0;
    protected $lossDecrement = 0;

    public function __construct(
        string     $projectDir,
        FileReader $fileReader
    )
    {
        $this->projectDir = $projectDir;
        $this->fileReader = $fileReader->setLevel(4)->setSubLevel('example');
    }

    public function solveFirstLevel()
    {
        $data = $this->fileReader->read();

        /**
         * @var Player[]
         */
        $players = $this->readPlayers($data);

//        $players = $this->collapseSamePLayers($players);
        Player::sortByRating($players);

        $converted = Player::convertOutput($players);

        $this->fileReader->write($converted);
        return $this->jsonify($converted, 10);
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

    public function readPlayers($data)
    {
        $rows = (int)$data[0][0];
        unset($data[0]);
        /**
         * @var Player[]
         */
        $players = [];

        $iterations = 0;

        foreach ($data as $game) {
            $player1 = new Player((int)$game[0], (int)$game[1]);
            $player2 = new Player((int)$game[2], (int)$game[3]);

            $player1Index = $this->findPlayer($player1->id, $players);
            $player2Index = $this->findPlayer($player2->id, $players);
            if ($player1Index === null) {
                $player1Index = $player1->id;
                $players[$player1Index] = $player1;
            }

            if ($player2Index === null) {
                $player2Index = $player2->id;
                $players[$player2Index] = $player2;
            }

            $players[$player1Index]->score = $player1->score;
            $players[$player2Index]->score = $player2->score;
//            $players[$player2Index]->wins += $player2->score > $player1->score ? 1 : 0;
//            $players[$player1Index]->wins += $player1->score > $player2->score ? 1 : 0;

            $players[$player1Index]->rating = $this->calculateRating($players[$player1Index], $players[$player2Index]);
            $players[$player2Index]->rating = $this->calculateRating($players[$player2Index], $players[$player1Index]);
        }

        return $players;
    }

    public function calculateRating(Player $player1, Player $player2)
    {
        $K = 32;
        $player1->ea += $this->getEA($player1->rating, $player2->rating);
        $SA = $player1->score > $player2->score ? 1 : 0;

        return floor($player1->rating + ($K * ($SA - $player1->ea)));
    }

    public function getEA($ra, $rb)
    {
        if ($ra - $rb >= 400) {
            return 0.9;
        }

        return 1 / (1 + (10 ^ (($rb - $ra) / 400)));
    }

    /**
     * @param $id
     * @param PLayer[] $players
     * @return int|null
     */
    public function findPlayer($id, array $players): ?int
    {
        foreach ($players as $key => $player) {
            if ($player->id === $id) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @param Player[] $players
     */
    public function collapseSamePLayers(array $players): array
    {
        $newPlayers = [];
        $len = count($players);
        $stats = [];
        for ($i = 0; $i < $len; $i++) {
            $id = $players[$i]->id;
            $win = $players[$i]->wins;

            if (array_key_exists($id, $stats)) {
                if ($win) {
                    $stats[$id] += $win;
                }
            } else {
                $stats[$id] = $win;
            }
        }

        foreach ($stats as $key => $stat) {
            $newPlayers[] = new Player((int)substr($key, 3), null, $stat);
        }

        return $newPlayers;
    }

    public function jsonify($data, $limit = 100, $withCount = false)
    {
        if (!is_array($data)) {
            return "" . $data;
        }

        $str = '';


        if (count($data) && !is_array($data[array_key_first($data)])) {
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

        if (count($data) && is_array($data[array_key_first($data)])) {
            foreach ($data as $key => $row) {
                $c = count($row);
                $keys = array_keys($row);
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
