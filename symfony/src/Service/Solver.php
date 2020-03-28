<?php

namespace App\Service;

class Solver
{
    /** @var string $projectDir */
    protected $projectDir;

    /** @var FileReader $fileReader */
    protected $fileReader;

    public function __construct(
        string $projectDir,
        FileReader $fileReader
    )
    {
        $this->projectDir = $projectDir;
        $this->fileReader = $fileReader->setLevel(3);
    }

    /**
     * @param mixed ...$args
     * @return array|bool
     * @throws \Exception
     */
    public function solve(...$args)
    {
        $data = $this->fileReader->read(' ');
        return $this->fileReader->write($data, ' ') ? $data : false;
    }
}
