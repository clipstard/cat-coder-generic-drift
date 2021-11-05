<?php

namespace App\Entity;

class Player
{
    public $id;
    public $score;
    public $wins;
    public $rating = 1000;
    public $ea = 0;

    public function __construct($id, $score, $wins = null)
    {
        $this->id = $id;
        $this->score = $score;
        $this->wins = $wins ?? 0;
    }

    public function inc()
    {
        $this->wins++;
    }

    public function __toArray(): array
    {
        return [$this->id, $this->rating];
    }

    /**
     * @param Player[]|array $players
     * @return mixed
     */
    public static function convertOutput(array &$players)
    {
        return array_map(function($player) {
            return $player->__toArray();
        }, $players);
    }

    /**
     * @param Player[] $players
     */
    public static function sort(array &$players)
    {

        for ($i = 0; $i < count($players) - 1; $i++) {
            for ($j = $i; $j < count($players); $j++) {
                $player1 = $players[$i];
                $player2 = $players[$j];

                if ($player1->score < $player2->score) {
                    $players[$i] = $player2;
                    $players[$j] = $player1;
                } elseif ($player1->score === $player2->score && $player1->id > $player2->id) {
                    $players[$i] = $player2;
                    $players[$j] = $player1;
                }
            }
        }
    }

    /**
     * @param Player[] $players
     */
    public static function sortByWin(array &$players)
    {

        for ($i = 0; $i < count($players) - 1; $i++) {
            for ($j = $i; $j < count($players); $j++) {
                $player1 = $players[$i];
                $player2 = $players[$j];

                if ($player1->wins < $player2->wins || ($player1->wins === $player2->wins && $player1->id > $player2->id)) {
                    $players[$i] = $player2;
                    $players[$j] = $player1;
                }
            }
        }
    }

    /**
     * @param Player[] $players
     */
    public static function sortByRating(array &$players)
    {

        for ($i = 0; $i < count($players) - 1; $i++) {
            for ($j = $i; $j < count($players); $j++) {
                $player1 = $players[$i];
                $player2 = $players[$j];

                if ($player1->rating < $player2->rating || ($player1->rating === $player2->rating && $player1->id > $player2->id)) {
                    $players[$i] = $player2;
                    $players[$j] = $player1;
                }
            }
        }
    }
}
