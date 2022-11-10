<?php


namespace App\Helper;

class _Array extends \ArrayObject {

    protected $data;

    public function __construct($array = [], $flags = 0, $iteratorClass = "ArrayIterator")
    {
        parent::__construct($array, $flags, $iteratorClass);
        $this->data = $array;
    }

    public function __invoke(): array
    {
        return $this->data;
    }

    public function find($callback)
    {
        foreach ($this->data as $item) {
            if ($callback($item)) {
                return $item;
            }
        }

        return null;
    }

    public function findIndex($callback)
    {
        foreach ($this->data as $key => $item) {
            if ($callback($item)) {
                return $key;
            }
        }

        return null;
    }

    public function __get($n) { return $this[$n]; }
}