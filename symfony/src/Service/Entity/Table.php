<?php

namespace App\Entity\Service;

class Table
{
    protected $size;
    protected $filled = [];
    protected $connectedColors = [];
    protected $rows;
    protected $cols;
    protected $points;

    public function __construct($x = null, $y = null)
    {
        $this->rows = $x;
        $this->cols = $y;

        $this->size = $x * $y + 1;
        for ($i = 0; $i < $this->size; $i++) {
            $this->filled[$i] = 0;
            $this->points[$i] = 0;
        }
    }

    public function isFilled(int $position)
    {
        return $this->filled[$position];
    }

    public function fillPoint(Point $point)
    {
        $this->filled[$point->getPosition()] = $point->getColor();
        $this->points[$point->getPosition()] = $point->getColor();
    }

    public function getCoordsByPosition(int $position)
    {
        $x = floor(($position - 1) / $this->cols) + 1;
        $y = $position - ($this->cols * ($x -1));
        return [(int)$x, (int)$y];
    }

    public function getPositionByCoords(int $x, int $y)
    {
        if ($y > $this->cols) {
            throw new \OutOfBoundsException('table has ' . $this->cols . ' maximum cols');
        }

        if ($x > $this->rows) {
            throw new \OutOfBoundsException('table has ' . $this->rows . ' maximum rows');
        }

       return (int) ((($x - 1) * $this->cols)) + (($y - 1) % $this->cols) + 1;
    }

    public function goE(int $position, int $color)
    {
        if ($this->isFilled($position) !== $color) {
            throw new \InvalidArgumentException('not right position');
        }

        [$x, $y] = $this->getCoordsByPosition($position);
        return $this->getPositionByCoords($x, ++$y);
    }

    private function isPoint($position, $color)
    {
        return $this->points[$position] !== 0 && $this->points[$position] !== $color;
    }

    public function goS(int $position, int $color)
    {
        if ($this->isFilled($position) !== $color) {
            throw new \InvalidArgumentException('not right position');
        }

        [$x, $y] = $this->getCoordsByPosition($position);
        return $this->getPositionByCoords(++$x, $y);
    }


    public function goN(int $position, int $color)
    {
        if ($this->isFilled($position) !== $color) {
            throw new \InvalidArgumentException('not right position');
        }

        [$x, $y] = $this->getCoordsByPosition($position);
        return $this->getPositionByCoords(--$x, $y);
    }


    public function goW(int $position, int $color)
    {
        if ($this->isFilled($position) !== $color) {
            throw new \InvalidArgumentException('not right position');
        }

        [$x, $y] = $this->getCoordsByPosition($position);
        return $this->getPositionByCoords($x, --$y);

    }

    public function doAction($position, $color, $action)
    {
        if ($this->isFilled($position) && $this->isFilled($position) === $color) {
            $pos = $this->{'go'. $action}($position, $color);

            if (!array_key_exists($pos, $this->filled)) {
                throw new \OutOfBoundsException('out of table');
            }

            if ($this->isFilled($pos) && !$this->isPoint($pos, $color)) {
                throw new \Exception('cross fill');
            }

            $this->filled[$pos] = $color;
            return $pos;
        }

        throw new \OutOfBoundsException('invalid position');
    }

    public function getCosestToEdge()
    {
        foreach ($this->filled as $item) {
            if (!$item || in_array($item, $this->connectedColors, true)) continue;
//            if ($item)
        }
    }
}
