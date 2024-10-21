<?php

namespace App\Service;


class Solver
{

    /** @var FileReader $fileReader */
    protected FileReader $fileReader;
    private array $ghostsPositions = [];

    public function __construct(
        protected string $projectDir,
        FileReader       $fileReader,
    )
    {
        $this->fileReader = $fileReader->setLevel(3)->setSubLevel('7');
    }

    public function process(): string
    {
        $data = $this->fileReader->read();
        $data = $this->arrayFlatten($data);
        $gridSize = (int)$data[0];
        # clear the data[0] since it is already read and its not level relevant
        unset($data[0]);

        $map = $this->createMapFromSizes($data, $gridSize);

        # removed keys leaves some undefineds
        $data = array_values($data);
        [$startX, $startY, $_, $movements] = $data;
        $movements = str_split($movements);
        # unset not level relevant data
        unset($data[0], $data[1], $data[2], $data[3]);
        # removed keys leaves some undefineds
        $data = array_values($data);
        $nrOfGhosts = (int)$data[0];
        unset($data[0]);

        $ghostsMovements = $this->readGhostMovements($nrOfGhosts, $data);

        [$countCollectedCoins, $isAlive] = $this->processPlayerMovements(
            $movements,
            $ghostsMovements,
            $map,
            (int)$startX,
            (int)$startY,
        );

        $results = [$countCollectedCoins, $isAlive ? 'YES' : 'NO'];
        # output file to upload to contest page directly, formatted as required
        $this->fileReader->write($results);

        # output to index, to see the debug / results faster
        return $this->getHtmlAsRows($results, 10);
    }

    protected function processPlayerMovements(
        array $movements,
        array $ghostsMovements,
        array $map,
        int $startX,
        int $startY,
    ): array
    {
        $countCollectedCoins = 0;
        $isAlive = true;
        $countedPositions = [];
        $currentX = $startX - 1;
        $currentY = $startY - 1;
        foreach ($movements as $index => $movement) {
            $this->moveGhosts($index, $ghostsMovements);

            [$movementX, $movementY] = $this->convertLetterToAxisMovement($movement);
            $currentX += $movementX;
            $currentY += $movementY;

            if ($this->playerDies($map, $currentX, $currentY)) {
                $isAlive = false;
                break;
            }

            foreach ($countedPositions as $countedPosition) {
                [$x, $y] = $countedPosition;
                # in case we already collected the coin for this square
                if ($currentX === $x && $currentY === $y) {
                    continue 2;
                }
            }

            $countedPositions[] = [$currentX, $currentY];
            $countCollectedCoins += (int)$this->checkPositionHasCoin($map, $currentX, $currentY);
        }

        return [$countCollectedCoins, $isAlive];
    }

    protected function createMapFromSizes(array &$data, int $gridSize): array
    {
        $index = 0;
        $map = [];
        foreach ($data as $rowNum => $row) {
            if ($index++ >= $gridSize) break;

            $items = str_split($row);
            $map[] = $items;
            unset($data[$rowNum]);
        }

        return $map;
    }

    public function convertLetterToAxisMovement(string $movement): array
    {
        $x = match ($movement) {
            'U' => -1,
            'D' => 1,
            default => 0,
        };

        $y = match ($movement) {
            'L' => -1,
            'R' => 1,
            default => 0,
        };

       return [$x, $y];
    }

    function arrayFlatten($array): array
    {
        if (!is_array($array)) {
            return [];
        }

        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->arrayFlatten($value));
            } else {
                $result = array_merge($result, array($key => $value));
            }
        }

        return $result;
    }

    public function moveGhosts($moveIndex, array $ghostsMovements): void
    {
        foreach ($ghostsMovements as $ghostIndex => $movements) {
            $movement = $movements[$moveIndex];
            [$currentX, $currentY] = $this->ghostsPositions[$ghostIndex];

            [$movementX, $movementY] = $this->convertLetterToAxisMovement($movement);
            $currentX += $movementX;
            $currentY += $movementY;

            $this->ghostsPositions[$ghostIndex] = [(int)$currentX, (int)$currentY];
        }
    }

    public function playerDies(array $map, $posX, $posY): bool
    {
        # hitting a wall
        if ($map[$posX][$posY] === 'W') {
            return true;
        }

        # hitting any of a ghost
        foreach ($this->ghostsPositions as $ghostsPosition) {
            [$ghostX, $ghostY] = $ghostsPosition;
            if ($posX === $ghostX && $posY === $ghostY) {
                return true;
            }
        }

        return false;
    }

    public function checkPositionHasCoin(array $map, $posX, $posY): bool
    {
        return $map[$posX][$posY] === 'C';
    }

    public function getHtmlAsRows($data, $limit = 100, $withCount = false): string
    {
        if (!is_array($data)) {
            return "" . $data;
        }

        $str = '';


        if (count($data) && !is_array($data[array_key_first($data)])) {
            $c = count($data);
            if ($withCount) {
                $str .= $c . ' ';
            }

            $keys = array_keys($data);
            for ($i = 0; $i < $c; $i++) {
                $str .= "{$data[$keys[$i]]} ";
                if ($i && $i % $limit === 0) {
                    $str .= "<br />";
                }
            }
        }

        if (count($data) && is_array($data[array_key_first($data)])) {
            foreach ($data as $key => $row) {
                $c = count($row);
                $keys = array_keys($row);
                for ($i = 0; $i < $c; $i++) {
                    $str .= "{$row[$keys[$i]]}, ";
                    if ($i && $i % $limit === 0) {
                        $str .= "<br />";
                    }
                }
                $str .= '---------------------------------------------------------<br />';
            }
        }

        return $str;
    }

    public function countUniques(array $array): int
    {
        return count($this->removeDuplicates($array));
    }

    public function removeDuplicates(array $array): array
    {
        $arr = [];
        foreach ($array as $item) {
            if (!in_array($item, $arr, true)) {
                $arr[] = $item;
            }
        }

        return $arr;
    }

    /**
     * @param int $nrOfGhosts
     * @param mixed $data
     * @return array
     */
    public function readGhostMovements(int $nrOfGhosts, mixed $data): array
    {
        $ghostsMovements = [];

        for ($i = 0; $i < $nrOfGhosts; $i++) {
            $data = array_values($data);
            [$ghostStartX, $ghostStartY, $_, $currentGhostMovements] = $data;
            $this->ghostsPositions[$i] = [(int)$ghostStartX - 1, (int)$ghostStartY - 1];
            $ghostsMovements[$i] = str_split($currentGhostMovements);

            unset($data[0], $data[1], $data[2], $data[3]);
        }

        return $ghostsMovements;
    }
}
