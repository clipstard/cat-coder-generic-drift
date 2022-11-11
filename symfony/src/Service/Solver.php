<?php

namespace App\Service;

use App\Entity\Player;
use App\Entity\Point;

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
        $this->fileReader = $fileReader->setLevel(4)->setSubLevel('example');
    }

    private array $coinMap = [[]];
    private array $map = [];
    private array $movementsMap = [];
    private int $maxMovements = 0;
    private ?Point $root = null;

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
        [$startX, $startY, $maxMovements] = $data;

        $this->maxMovements = (int) $maxMovements;
        $currentX = (int) $startX - 1;
        $currentY = (int) $startY - 1;
        $this->root = new Point($currentX, $currentY);
        $this->root->isRoot = true;
        $countCoins = 0;
        $this->map = $map;

        foreach ($map as $x => $row) {
            foreach ($row as $y => $item) {
                if ($item === 'C') {
                    $this->coinMap[$x][$y] = 'C';
                    $countCoins++;
                }
            }
        }

        /*
         *                 if (isset($this->coinMap[$x][$y])) {
                    dump([
                        'position' => 'this',
                        'x' => $x,
                        'y' => $y,
                    ]);
                } elseif (isset($this->coinMap[$x][$y + 1])) { // check right
                    dump([
                        'position' => 'right',
                        'x' => $x, 'y' => $y + 1,
                    ]);
                } elseif (isset($this->coinMap[$x][$y - 1])) { // check left
                    dump([
                        'position' => 'left',
                        'x' => $x, 'y' => $y - 1,
                    ]);
                } elseif (isset($this->coinMap[$x + 1][$y])) { // check bottom
                    dump([
                        'position' => 'top',
                        'x' => $x + 1, 'y' => $y,
                    ]);
                } elseif (isset($this->coinMap[$x - 1][$y])) { // check top
                    dump([
                        'position' => 'bottom',
                        'x' => $x - 1, 'y' => $y,
                    ]);
                }
         */
        $nbPoints = 0;
        $this->addNextPoint($currentY, $currentX, $this->root);

        $point = $this->root;
        $path = '';
        $pointsCounted = 0;
        while ($point !== null) {
            $path .= $point->getPreviousMovement();
            if (!$point->collected) {
                $pointsCounted++;
            }

            $point->collected = true;
            $point = $point->getPrevious();
        }

        $str = '';
        foreach ($this->map as $row) {
            $str .= implode('', $row) . "\n";
        }

        $res = [$count];
        $this->fileReader->write($res);

        return $this->jsonify($res, 10);
    }

    public function addNextPoint($x, $y, Point $point)
    {
        $leftX = $x - 1;
        $rightX = $x + 1;
        $topX = $x;
        $bottomX = $x;
        $leftY = $y;
        $rightY = $y;
        $topY = $y - 1;
        $bottomY = $y + 1;

        $left = @$this->coinMap[$leftY][$leftX];
        $right = @$this->coinMap[$rightY][$rightX];
        $top = @$this->coinMap[$topY][$topX];
        $bottom = @$this->coinMap[$bottomY][$bottomX];
        unset($this->map[$y][$x]);
        $str = '';
        foreach ($this->map as $row) {
            $str .= implode('', $row) . "\n";
        }

        if ($left && !$point->isInTheTree($leftX, $leftY)) {
            $point->setLeft(new Point($leftX, $leftY));
            $this->addNextPoint($leftX, $leftY, $point->left);
        }

        if ($right && !$point->isInTheTree($rightX, $rightY)) {
            $point->setRight(new Point($rightX, $rightY));
            $this->addNextPoint($rightX, $rightY, $point->right);
        }

        if ($top && !$point->isInTheTree($topX, $topY)) {
            $point->setTop(new Point($topX, $topY));
            $this->addNextPoint($topX, $topY, $point->top);
        }

        if ($bottom && !$point->isInTheTree($bottomX, $bottomY)) {
            $point->setBottom(new Point($bottomX, $bottomY));
            $this->addNextPoint($bottomX, $bottomY, $point->bottom);
        }
    }

    function branchCompleted(Point $point): bool
    {
        $current = $this->root;

        while ($current !== null) {
            if ($current->eq($point)) {
                return true;
            }
        }

        return false;
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
