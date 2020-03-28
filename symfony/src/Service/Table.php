<?php

namespace App\Service;

class Table
{
    protected $size;
    protected $filled = [];
    protected $connectedColors = [];
    protected $rows;
    protected $cols;

    public function __construct($x = null, $y = null)
    {
        $this->rows = $x;
        $this->cols = $y;

        $this->size = $x * $y;
        for ($i = 0; $i < $this->size; $i++) {
            $this->filled[$i] = 0;
        }
    }

    public function isFilled(int $position)
    {
        return $this->filled[$position - 1];
    }

    public function fillPoint(Point $point)
    {
        $this->filled[$point->getPosition()] = $point->getColor();
    }

    public function getCoordsByPosition(int $position)
    {
        $x = floor(($position - 1) / $this->cols) + 1;
        $y = $position - ($this->cols * ($x -1));
        return [(int)$x, (int)$y];
    }

    public function getCosestToEdge()
    {
        foreach ($this->filled as $item) {
            if (!$item || in_array($item, $this->connectedColors, true)) continue;
//            if ($item)
        }
    }
}