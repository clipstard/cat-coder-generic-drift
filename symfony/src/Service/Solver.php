<?php

namespace App\Service;

class Solver
{
    /** @var string $projectDir */
    protected $projectDir;

    /** @var FileReader $fileReader */
    protected $fileReader;

    protected $helper;

    public function __construct(
        string $projectDir,
        FileReader $fileReader,
        Helper $helper
    )
    {
        $this->projectDir = $projectDir;
        $this->fileReader = $fileReader->setLevel(1)->setSubLevel('4');
        $this->helper = $helper;
    }

    /**
     * @param mixed ...$args
     * @return array|bool
     * @throws \Exception
     */
    public function solve(...$args)
    {
        $data = $this->fileReader->read(' ', true);

        return $this->solveLevel1($data);
    }

    public function findPointsInSameRegion($yPoint, $nr, $lines)
    {
        $points = [];

        foreach ($lines as $line) {
            [$x, $y] = $line;

            if ($yPoint > 0 && $y < $yPoint) {
                $points[] = $line;
            } elseif ($yPoint < 0 && $y > $yPoint) {
                $points[] = $line;
            }
        }

        return $points;
    }

    function solveLevel1($data) {
        [$yPoint, $nrOfLines, $lines] = $this->helper->readLevel($data);

        $points = $this->findPointsInSameRegion($yPoint, $nrOfLines, $lines);

        $this->helper->writeLevel($this->helper->matrixToArray($points));
        return $this->helper->jsonify($points, 100, false);
    }

    /**
     * @param $array
     * @return Pair[]|array
     */
    private function findPairs($array)
    {
        $pairs = [];

        $c = count($array);
        $lastPair = null;
        for ($i = 0; $i < ($c - 1); $i++) {
            for ($j = ($i + 1); $j < $c; $j++) {
                $sum = $array[$i] + $array[$j];
                if ($sum === 1 || $sum === -1) {
                    $pairs[] = new Pair($array[$i], $array[$j], $i, $j);
                }
            }
        }

        return $pairs;
    }
}
