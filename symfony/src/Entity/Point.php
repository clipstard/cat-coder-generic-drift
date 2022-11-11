<?php

namespace App\Entity;

class Point
{
    public int $x;
    public int $y;
    public ?Point $top = null;
    public ?Point $left = null;
    public ?Point $right = null;
    public ?Point $bottom = null;
    public ?Point $previous = null;
    public bool $isRoot = false;
    public bool $collected = false;
    public string $letter;
    public string $reverseLetter;

    public function __construct($x = null, $y = null)
    {
        $this->x = $x;
        $this->y = $y;
        $this->letter = '';
    }

    public function eq(Point $point) {
        return $this->x === $point->x && $this->y === $point->y;
    }

    /**
     * @param Point|null $bottom
     *
     * @return Point
     */
    public function setBottom(?Point $bottom): self
    {
        $this->bottom = $bottom;
        $bottom->previous = $this;
        $bottom->letter = 'D';
        $bottom->reverseLetter = 'U';

        return $this;
    }

    /**
     * @param Point|null $left
     *
     * @return Point
     */
    public function setLeft(?Point $left): self
    {
        $this->left = $left;
        $left->previous = $this;
        $left->letter = 'L';
        $left->reverseLetter = 'R';

        return $this;
    }

    /**
     * @param Point|null $right
     *
     * @return Point
     */
    public function setRight(?Point $right): self
    {
        $this->right = $right;
        $right->previous = $this;
        $right->letter = 'R';
        $right->reverseLetter = 'L';

        return $this;
    }

    /**
     * @param Point|null $top
     *
     * @return Point
     */
    public function setTop(?Point $top): self
    {
        $this->top = $top;
        $top->previous = $this;
        $top->letter = 'U';
        $top->reverseLetter = 'D';

        return $this;
    }

    public function isInTheTree($x, $y): bool
    {
        $point = $this;
        while ($point !== null) {
            if ($point->x === $x && $point->y == $y) {
                return true;
            }

            $point = $point->previous;
        }

        return false;
    }

    public function getPreviousMovement(): string
    {
        if ($this->right && !$this->right->collected) {
            return $this->right->letter;
        }

        if ($this->left && !$this->left->collected) {
            return $this->left->letter;
        }

        if ($this->top && $this->top->collected) {
            return $this->top->letter;
        }

        if ($this->bottom && $this->bottom->collected) {
            return $this->bottom->letter;
        }

        return $this->reverseLetter ?? '';
    }

    public function getPrevious(): ?self
    {
        if ($this->right && !$this->right->collected) {
            return $this->right;
        }

        if ($this->left && !$this->left->collected) {
            return $this->left;
        }

        if ($this->top && !$this->top->collected) {
            return $this->top;
        }

        if ($this->bottom && !$this->bottom->collected) {
            return $this->bottom;
        }

        return $this->previous;
    }
}
