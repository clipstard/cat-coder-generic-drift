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

        $data = '125 133 134 135 136 -52 -51 -50 -49 -48 -47 -46 -45 66 67 68 69 70 71 -38 -37 -36 -35 -34 -33 -32 -31 -30 -29 -132 -131 -130 -193 -192 -191 -190 -189 -188 -187 -186 -185 -184 -183 -182 -181 -180 -179 -178 -177 -176 -175 -174 -173 -172 -171 -170 -169 -77 -76 -75 -74 -73 -72 18 19 20 21 22 23 24 25 26 27 28 -164 -163 -65 -64 -63 -62 -61 -60 -59 -58 -57 -56 -55 -54 -53 39 40 41 42 43 44 159 160 161 162 -17 -16 -15 -14 -13 -12 -11 -10 -9 -8 -7 -6 -5 -4 -3 -2 -1 -168 -167 -166 -165 126 127 128 129 86 87 88 89 90 91 92 93 94 95 96 -124 -123 -122 -121 -120 -119 -118 -117 -116 -115 -114 -113 -112 -111 -110 -109 -108 -107 -106 -105 -104 -103 -102 -101 -100 -99 -98 -97 153 154 155 156 157 158 -148 -147 -146 -145 -144 -143 -142 -141 -140 -139 -138 -137 -85 -84 -83 -82 -81 -80 -79 -78 -152 -151 -150 -149';
//        $data = '8 0 3 1 6 5 -2 4 7 -1';
        $arr = explode(' ', $data);
        $intValue = [];
        foreach ($arr as $item) {
            $intValue[] = (int)$item;
        }

        $c = count($intValue);


        $pairs = $this->findPairs($intValue);
        return $this->jsonify($pairs);

//        $data = $this->fileReader->read(' ');
//        return $this->fileReader->write($data, ' ') ? $data : false;
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
                    $pairs[] = new Pair($array[$i], $array[$j]);
                }
            }
        }

        Pair::sortByX($pairs);
        return $pairs;
    }

    public function solveExample()
    {
//        $data = "8 0 3 1 6 5 -2 4 7 1 2 -2 5";
        $data = "193 125 133 134 135 136 -52 -51 -50 -49 -48 -47 -46 -45 66 67 68 69 70 71 -38 -37 -36 -35 -34 -33 -32 -31 -30 -29 -132 -131 -130 -193 -192 -191 -190 -189 -188 -187 -186 -185 -184 -183 -182 -181 -180 -179 -178 -177 -176 -175 -174 -173 -172 -171 -170 -169 -77 -76 -75 -74 -73 -72 18 19 20 21 22 23 24 25 26 27 28 -164 -163 -65 -64 -63 -62 -61 -60 -59 -58 -57 -56 -55 -54 -53 39 40 41 42 43 44 159 160 161 162 -17 -16 -15 -14 -13 -12 -11 -10 -9 -8 -7 -6 -5 -4 -3 -2 -1 -168 -167 -166 -165 126 127 128 129 86 87 88 89 90 91 92 93 94 95 96 -124 -123 -122 -121 -120 -119 -118 -117 -116 -115 -114 -113 -112 -111 -110 -109 -108 -107 -106 -105 -104 -103 -102 -101 -100 -99 -98 -97 153 154 155 156 157 158 -148 -147 -146 -145 -144 -143 -142 -141 -140 -139 -138 -137 -85 -84 -83 -82 -81 -80 -79 -78 -152 -151 -150 -149 -45 12 44 94";
        $arr = explode(' ', $data);
        $intValue = [];
        $intPermutation = [];
        $first = true;
        $length = null;
        $c = count($arr);
        for ($i = 0; $i < $c; $i++) {
            if (!$i) {
                $length = (int)$arr[$i];
                continue;
            }

            if ($i <= $length) {
                $intValue[] = (int)$arr[$i];
            } else {
                $intPermutation[] = $arr[$i];
            }
        }

        $this->makePermutations($intValue, $intPermutation);

        return $intValue;
//        return $this->jsonify($intValue, 100, false);
    }

    public function solveThird()
    {
        $values = $this->solveExample();
        $pairs = $this->findPairs($values);
        $c = count($pairs) . "";

        return $this->jsonify($c);
    }

    private function makePermutations(&$array, &$permutation)
    {
        if (count($permutation) % 4) {
            throw new \Exception('invalid permutation');
        }

        $indexes = [$permutation[1], $permutation[3]];
        $values = [$permutation[0], $permutation[2]];

        $c = count($array);

        $branch = [];
        for ($i = 0; $i < $c; $i++) {
            if ($i >= $indexes[0] && $i <= $indexes[1]) {
                $branch[] = $array[$i];
            }
        }

        $direction = $values[0] + $values[1] === 1;
        $branch = $this->swapBranch($branch, $direction);

        $p = 0;
        for ($i = 0; $i < $c; $i++) {
            if ($i >= $indexes[0] && $i <= $indexes[1]) {
                $array[$i] = $branch[$p++];
            }
        }
    }

    private function swapBranch($branch, $direction)
    {
        $newBranch = $direction ?
            array_slice($branch, 0, count($branch) - 1) :
            array_slice($branch, 1, count($branch));

        $newBranch = array_reverse($newBranch);
        $newBranch = array_map(static function ($item) { return $item * -1;}, $newBranch);

        $newBranch = $direction ?
            array_merge($newBranch, [$branch[count($branch) - 1]]) :
            array_merge([$branch[0]], $newBranch);
        return $newBranch;
    }

    public function jsonify($data, $limit = 100, $withCount = true)
    {
        if (is_string($data)) {
            return $data;
        }

        $str = '';

        if (is_array($data) && !is_array($data[0])) {
            $c = count($data);
            if ($withCount) {
                $str .= $c . ' ';
            }

            for ($i = 0; $i < $c; $i++) {
                $str .= "{$data[$i]} ";
                if ($i && $i % $limit === 0) {
                    $str .= "<br />";
                }
            }
        }

        if (is_array($data) && is_array($data[0])) {
            foreach ($data as $row) {
                $c = count($row);
                for ($i = 0; $i < $c; $i++) {
                    $str .= "{$row[$i]}, ";
                    if ($i && $i % $limit === 0) {
                        $str .= "<br />";
                    }
                }
                $str .= '---------------------------------------------------------<br />';
            }
        }

        return $str;
    }

    public function sortByX(&$array)
    {
        $c = count($array);
        for ($i = 0; $i < ($c - 1); $i++) {
            for ($j = 1; $j < $c; $j++) {
                if ($array[$i]['x'] > $array[$j]['x']) {
                    [$array[$i], $array[$j]] = [$array[$j], $array[$i]];
                }
            }
        }
    }
}
