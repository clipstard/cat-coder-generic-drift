<?php

namespace App\Entity;

class Point
{
    /** @var int */
    private $x;
    /** @var int */
    private $y;
    /** @var int */
    private $w;
    /** @var int */
    private $h;

    public function __construct($x, $y, $w = 0, $h = 0)
    {
        $this->h = $h;
        $this->y = $y;
        $this->w = $w;
        $this->x = $x;
    }

    /**
     * @return int
     */
    public function getH(): int
    {
        return $this->h;
    }

    /**
     * @param int $h
     *
     * @return Point
     */
    public function setH(int $h): self
    {
        $this->h = $h;

        return $this;
    }

    /**
     * @return int
     */
    public function getW(): int
    {
        return $this->w;
    }

    /**
     * @param int $w
     *
     * @return Point
     */
    public function setW(int $w): self
    {
        $this->w = $w;

        return $this;
    }

    /**
     * @return int
     */
    public function getX(): int
    {
        return $this->x;
    }

    /**
     * @param int $x
     *
     * @return Point
     */
    public function setX(int $x): self
    {
        $this->x = $x;

        return $this;
    }

    /**
     * @return int
     */
    public function getY(): int
    {
        return $this->y;
    }

    /**
     * @param int $y
     *
     * @return Point
     */
    public function setY(int $y): self
    {
        $this->y = $y;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "{$this->x} {$this->y}";
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        return [
            $this->x, $this->y, $this->w, $this->h,
        ];
    }
}
