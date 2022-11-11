<?php

namespace App\Service;

use App\Entity\Player;

class Solver
{
    /** @var string $projectDir */
    protected string $projectDir;

    /** @var FileReader $fileReader */
    protected FileReader $fileReader;

    public function __construct(
        string     $projectDir,
        FileReader $fileReader
    )
    {
        $this->projectDir = $projectDir;
        $this->fileReader = $fileReader->setLevel(3)->setSubLevel('7');
    }

    private array $countedPositions = [];
    private array $ghostsPositions = [];

    public function solveFirstLevel()
    {
        $data = $this->fileReader->read();
        $data = $this->array_flatten($data);
        $gridSize = (int) $data[0];
        unset($data[0]);

        $count = 0;
        $isAlive = true;

        $index = 0;
        $map = [];
        foreach ($data as $rowNum => $row) {
            if ($index++ >= $gridSize) break;

            $items = str_split($row);
            $map[] = $items;
            unset($data[$rowNum]);
        }

        $data = array_values($data);
        [$startX, $startY, $_, $movements] = $data;
        unset($data[0],$data[1],$data[2],$data[3]);
        $data = array_values($data);
        $nrOfGhosts = (int) $data[0];
        $ghostsMovements = [];
        unset($data[0]);

        for ($i = 0; $i < $nrOfGhosts; $i++) {
            $data = array_values($data);
            [$ghostStartX, $ghostStartY, $_, $ghostMovements] = $data;
            $this->ghostsPositions[$i] = [(int) $ghostStartX - 1, (int) $ghostStartY - 1];
            $ghostsMovements[$i] = str_split($ghostMovements);

            unset($data[0], $data[1], $data[2], $data[3]);
        }

        $movements = str_split($movements);

        $currentX = (int) $startX - 1;
        $currentY = (int) $startY - 1;

        $countedPositions = [];

        dump($movements);
        dump($map);
        dump($map[$currentX][$currentY]);
        dump(['star' => [$currentX, $currentY, $count]]);

        foreach ($movements as $index => $movement) {
            $this->moveGhosts($index, $ghostsMovements);

            switch ($movement) {
                case 'U':
                    $currentX -= 1;  break;
                case 'D':
                    $currentX += 1; break;
                case 'L':
                    $currentY -= 1; break;
                case 'R':
                    $currentY += 1; break;
                default:
                    break;
            }

            dump([
                'x y' => [$currentY, $currentX],
                'movement' => $movement,
                'movementIndex' => $index,
                'currentScore' => $count,
                'currentLeter' => $map[$currentX][$currentY],
                'ghostCurrentPositions' => $this->ghostsPositions,
            ]);

            if ($this->dies($map, $currentX, $currentY)) {
                $isAlive = false;
                break;
            }

            foreach ($countedPositions as $countedPosition) {
                [$x, $y] = $countedPosition;
                if ($currentX === $x && $currentY === $y) continue 2;
            }

            $countedPositions[] = [$currentX, $currentY];
            $count += $this->checkPosition($map, $currentX, $currentY) ? 1 : 0;
            dump(['newScore' => $count]);
        }
//die;
        $res = [$count, $isAlive ? 'YES' : 'NO'];
        $this->fileReader->write($res);

        return $this->jsonify($res, 10);
    }

    public function moveGhosts($moveIndex, array $ghostsMovements): void
    {
        foreach ($ghostsMovements as $ghostIndex => $movements) {
            $movement = $movements[$moveIndex];
            [$currentX, $currentY] = $this->ghostsPositions[$ghostIndex];
            switch ($movement) {
                case 'U':
                    $currentX -= 1;  break;
                case 'D':
                    $currentX += 1; break;
                case 'L':
                    $currentY -= 1; break;
                case 'R':
                    $currentY += 1; break;
                default:
                    break;
            }

            $this->ghostsPositions[$ghostIndex] = [(int) $currentX, (int) $currentY];
        }
    }

    public function dies(array $map, $posX, $posY): bool
    {
        if ($map[$posX][$posY] === 'W') {
            return true;
        }

        foreach ($this->ghostsPositions as $ghostsPosition) {
            [$ghostX, $ghostY] = $ghostsPosition;
            if ($posX === $ghostX && $posY === $ghostY) {
                return true;
            }
        }

        return false;
    }

    public function checkPosition(array $map, $posX, $posY): bool
    {
        return $map[$posX][$posY] === 'C';
    }

    function array_flatten($array): array
    {
        if (!is_array($array)) {
            return [];
        }

        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->array_flatten($value));
            } else {
                $result = array_merge($result, array($key => $value));
            }
        }

        return $result;
    }

    public function countUniques($array)
    {
        $c = count($array);
        $count = 0;
        $items = [];
        $found = false;
        foreach ($array as $item) {
            if (!in_array($item, $items, true)) {
                $items[] = $item;
            }
        }

        return count($items);
    }

    public function removeDuplicates($array)
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
     * @param $id
     * @param PLayer[] $players
     * @return int|null
     */
    public function findPlayer($id, array $players): ?int
    {
        foreach ($players as $key => $player) {
            if ($player->id === $id) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @param Player[] $players
     */
    public function collapseSamePLayers(array $players): array
    {
        $newPlayers = [];
        $len = count($players);
        $stats = [];
        for ($i = 0; $i < $len; $i++) {
            $id = $players[$i]->id;
            $win = $players[$i]->wins;

            if (array_key_exists($id, $stats)) {
                if ($win) {
                    $stats[$id] += $win;
                }
            } else {
                $stats[$id] = $win;
            }
        }

        foreach ($stats as $key => $stat) {
            $newPlayers[] = new Player((int)substr($key, 3), null, $stat);
        }

        return $newPlayers;
    }

    public function jsonify($data, $limit = 100, $withCount = false)
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
}
