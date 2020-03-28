<?php

namespace App\Service;

class Point
{
    protected $x;
    protected $y;
    protected $i;
    protected $j;
    protected $color;
    /** @var bool */
    protected $root;
    /** @var int */
    protected $position;

    public function __construct($position = null, $color = null)
    {
        $this->position = $position;
        $this->color = $color;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     *
     * @return Point
     */
    public function setPosition(int $position): Point
    {
        $this->position = $position;

        return $this;
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
     * @return Point
     */
    public function setX($x): Point
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
     * @return bool
     */
    public function isRoot(): bool
    {
        return $this->root;
    }

    /**
     * @param bool $root
     *
     * @return Point
     */
    public function setRoot(bool $root): Point
    {
        $this->root = $root;

        return $this;
    }

    /**
     * @param null $y
     *
     * @return Point
     */
    public function setY($y): Point
    {
        $this->y = $y;

        return $this;
    }

    /**
     * @return null
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param null $color
     *
     * @return Point
     */
    public function setColor($color): Point
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @param Point[]|array $array
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
     * @param Point[]|array $array
     */
    public static function sortByPosition(&$array)
    {
        $c = count($array);
        for ($i = 0; $i < ($c - 1); $i++) {
            for($j = $i + 1; $j < $c; $j++) {
                if ($array[$i]->getPosition() > $array[$j]->getPosition()) {
                    [$array[$i], $array[$j]] = [$array[$j], $array[$i]];
                }
            }
        }
    }

    /**
     * @param Point[]|array $array
     */
    public static function sortByColor(&$array)
    {
        $c = count($array);
        for ($i = 0; $i < ($c - 1); $i++) {
            for($j = $i + 1; $j < $c; $j++) {
                if ($array[$i]->getColor() > $array[$j]->getColor()) {
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