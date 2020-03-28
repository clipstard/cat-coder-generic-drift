<?php

namespace App\Service;

class Pair
{
    protected $x;
    protected $y;

    public function __construct($x = null, $y = null)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * @return null
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @param null $x
     *
     * @return Pair
     */
    public function setX($x): Pair
    {
        $this->x = $x;

        return $this;
    }

    /**
     * @return null
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * @param null $y
     *
     * @return Pair
     */
    public function setY($y): Pair
    {
        $this->y = $y;

        return $this;
    }

    /**
     * @param Pair[]|array $array
     */
    public static function sortByX(&$array)
    {
        $c = count($array);
        for ($i = 0; $i < ($c - 1); $i++) {
            for($j = $i + 1; $j < $c; $j++) {
                if ($array[$i]->getX() > $array[$j]->getX()) {
                    [$array[$i], $array[$j]] = [$array[$j], $array[$i]];
                }
            }
        }
    }

    public function __toString()
    {
        return "{$this->x} {$this->y}";
    }
}