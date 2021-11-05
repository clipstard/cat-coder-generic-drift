<?php

namespace App\Service;

use App\Entity\Player;

class Solver
{
    /** @var string $projectDir */
    protected $projectDir;

    /** @var FileReader $fileReader */
    protected $fileReader;
    protected $preservedTokens = ['print'];
    protected $conditional = ['if', 'else'];
    protected $variables = [];

    public function __construct(
        string     $projectDir,
        FileReader $fileReader
    )
    {
        $this->projectDir = $projectDir;
        $this->fileReader = $fileReader->setLevel(3)->setSubLevel('example');
    }

    public function solveFirstLevel()
    {
        $data = $this->fileReader->read();
        $data = $this->array_flatten($data);
        unset($data[0]);
        $functions = $this->splitFunctions($data);

        $result = [];
        foreach ($functions as $key => $function) {
            $this->variables = [];
            $result[] = [$this->followTokens($function)[0]];
        }

//        die;

        $filtered = [];
        foreach ($result as $item) {
//            if (str_contains($item[0], 'ERROR')) {
//                $filtered[] = ['ERROR'];
//                continue;
//            }

            if (trim($item[0]) === '') {
                continue;
            }

            $filtered[] = $item;
        }

        $this->fileReader->write($filtered);

        return $this->jsonify($filtered, 10);
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

    public function splitFunctions($data)
    {
        $arr = [];
        $tmp = [];
        $isIf = 0;
        $isElse = 0;

        foreach ($data as $item) {
            if ($item === 'if') {
                $isIf++;
            }

            if ($item === 'else') {
                $isElse++;
            }

            if ($item === 'start') continue;
            if ($item === 'end' && $isIf > 0) {
                $tmp[] = $item;
                $isIf--;
                continue;
            }

            if ($item === 'end' && $isElse > 0) {
                $tmp[] = $item;
                $isElse--;
                continue;
            }

            if ($item === 'end' && $isElse === 0 && $isIf === 0) {
                $arr[] = $tmp;
                $tmp = [];
            } else {
                $tmp[] = $item;
            }
        }

        return $arr;
    }

    /**
     * @param int $start
     * @param $data
     */
    function handleConditional(int $start, $data)
    {
        $isEndif = false;
        $isEndElse = false;
        $statement = false;
        $arr = [];
        $count = 1;

        $items = array_slice($data, $start + 1);

        dump($items);
        if ($items[0] !== 'false' && $items[0] !== 'true') {
            $var = $this->findVariableByNameAndType($this->variables, ['name' => $items[0], 'type' => 'boolean']);
            if ($var === null) {
                return [0, 'ERROR', true];
            } else {
                $statement = $var['value'] === 'true';
            }
        } else {
            $statement = $items[0] === 'true';
        }

        unset($items[0]);
        foreach ($items as $key => $item) {
            $debug[] = $item;
            $count++;

            if ($item === 'if') {
                return $this->handleConditional(0, array_slice($items, $key - 1));
            }

            if ($item === 'else') continue;
            if ($item === 'end' && !$isEndif) {
                $isEndif = true;
                continue;
            }

            if ($item === 'end' && !$isEndElse) {
                break;
            }

            if ($statement && !$isEndif) {
                $arr[] = $item;
            }

            if (!$statement && $isEndif && !$isEndElse) {
                $arr[] = $item;
            }
        }

        $return = $this->followTokens($arr);
        $withReturn = $return[1];
        $str = $return[0];

        return [$count - 1, $str, $withReturn];
    }

    public function findVariableByNameAndType($variables, $variable)
    {
        foreach ($variables as $key => $var) {
            if ($var['name'] === $variable['name'] && $var['type'] === $variable['type']) {
                return $var;
            }
        }

        return null;
    }

    public function findVariableByName($variables, $variable)
    {
        foreach ($variables as $key => $var) {
            if ($var['name'] === $variable['name']) {
                return $var;
            }
        }

        return null;
    }

    public function findVariableIndexByName($variables, $variable)
    {
        foreach ($variables as $key => $var) {
            if ($var['name'] === $variable['name']) {
                return $key;
            }
        }

        return null;
    }

    public function getType($item)
    {
        if ($item === 'true' || $item === 'false') {
            return 'boolean';
        }

        $int = intval($item);

        if ("$int" === $item) {
            return 'number';
        }

        return 'string';
    }


    function array_flatten($array)
    {
        if (!is_array($array)) {
            return false;
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
