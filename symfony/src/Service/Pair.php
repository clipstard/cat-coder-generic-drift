<?php

namespace App\Service;

class Pair
{
    protected $x;
    protected $y;
    protected $i;
    protected $j;

    public function __construct($x = null, $y = null, $i = null, $j = null)
    {
        $this->x = $x;
        $this->y = $y;
        $this->i = $i;
        $this->j = $j;
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

    /**
     * @return null
     */
    public function getI()
    {
        return $this->i;
    }

    /**
     * @return null
     */
    public function getJ()
    {
        return $this->j;
    }

    public function __toString()
    {
        return "{$this->x} {$this->y}";
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            $this->getX(), $this->getI(), $this->getY(), $this->getJ()
        ];
    }
}
