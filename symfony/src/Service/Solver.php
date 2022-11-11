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
        $this->fileReader = $fileReader->setLevel(2)->setSubLevel('2');
    }

    public function solveFirstLevel()
    {
        $data = $this->fileReader->read();
        $data = $this->array_flatten($data);
        $gridSize = (int) $data[0];
        unset($data[0]);

        $count = 0;

        $index = 0;
        $map = [];
        foreach ($data as $rowNum => $row) {
            if ($index++ >= $gridSize) break;

            $items = str_split($row);
            $map[] = $items;
            unset($data[$rowNum]);
        }

        [$startX, $startY, $nrMovements, $movements] = array_values($data);
        $movements = str_split($movements);

        $currentX = (int) $startX - 1;
        $currentY = (int) $startY - 1;

        $count += $this->checkPosition($map, $currentX, $currentY) ? 1 : 0;
        $countedPositions = [[$currentX, $currentY]];

        dump($movements);
        dump($map);
        dump($map[$currentX][$currentY]);
        dump(['star' => [$currentX, $currentY, $count]]);
        foreach ($movements as $movement) {
            switch ($movement) {
                case 'U': $currentX -= 1;  break;
                case 'D': $currentX += 1; break;
                case 'L': $currentY -= 1; break;
                case 'R': $currentY += 1; break;
                default:
                    break;
            }

            dump([
                'x y' => [$currentX, $currentY],
                'movement' => $movement,
                'currentScore' => $count,
                'currentLeter' => $map[$currentX][$currentY],
            ]);
            foreach ($countedPositions as $countedPosition) {
                [$x, $y] = $countedPosition;
                if ($currentX === $x && $currentY === $y) continue 2;
            }

            $countedPositions[] = [$currentX, $currentY];
            $count += $this->checkPosition($map, $currentX, $currentY) ? 1 : 0;
            dump(['newScore' => $count]);
        }

        $this->fileReader->write($count);

        return $this->jsonify($count, 10);
    }

    public function checkPosition(array $map, $posX, $posY): bool
    {
        return $map[$posX][$posY] === 'C';
    }

    public function followTokens($data)
    {
        $str = '';

        $canAdd = false;
        $skipPoins = 0;
        $die = false;
        $ifLevel = 0;

        foreach ($data as $key => $item) {
            if ($skipPoins-- > 0) {
                continue;
            };


            if ($canAdd) {
                $var = $this->findVariableByName($this->variables, ['name' => $item]);
                if ($var !== null) {
                    $str .= $var['value'];
                } else {
                    $str .= $item;
                }

                $canAdd = false;
                continue;
            }

            if ($item === 'var') {
                $var = $this->findVariableByName($this->variables, ['name' => $data[$key + 1]]);
                if ($var !== null) {
                    return ['ERROR', true];
                }

                $anotherVar = $this->findVariableByName($this->variables, ['name' => $data[$key + 2]]);
                if ($anotherVar !== null) {
                    $this->variables[] = ['name' => $data[$key + 1], 'type' => $anotherVar['type'], 'value' => $anotherVar['value']];
                } else {
                    $this->variables[] = ['name' => $data[$key + 1], 'type' => $this->getType($data[$key + 2]), 'value' => $data[$key + 2]];
                }

                $skipPoins = 2;
                continue;
            }

            if ($item === 'set') {
                $var = $this->findVariableByName($this->variables, ['name' => $data[$key + 1]]);
                $varIndex = $this->findVariableIndexByName($this->variables, ['name' => $data[$key + 1]]);
                if ($var !== null) {
                    $anotherVar = $this->findVariableByName($this->variables, ['name' => $data[$key + 2]]);
                    if ($anotherVar !== null) {
                        $this->variables[$varIndex]['value'] = $anotherVar['value'];
                        $this->variables[$varIndex]['type'] = $this->getType($anotherVar['value']);
                    } else {
                        $this->variables[$varIndex]['value'] = $data[$key + 2];
                        $this->variables[$varIndex]['type'] = $this->getType($data[$key + 2]);
                    }
                } else {
                    return ['ERROR', true];
                }

                $skipPoins = 2;
                continue;
            }

            if ($item === 'if') {
                $response = $this->handleConditional($key, $data);
                $skipPoins = $response[0];
                $str .= $response[1];
                $isReturn = $response[2];

                if ($isReturn) {
                    return [$str];
                }

                return [$str . $this->followTokens(array_slice($data, $key + $skipPoins + 1))[0], false];
            }

            if (in_array($item, $this->preservedTokens)) {
                $canAdd = true;
            }

            if ($item === 'return') {
                return [$str, true];
            }
        }

        return [$str, false];
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
