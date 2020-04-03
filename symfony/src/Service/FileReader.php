<?php

namespace App\Service;

class FileReader
{

    const BASE_BATH = '/public/level/';
    /** @var int */
    private $level = 0;
    /** @var int|string */
    private $subLevel = '0';

    /** @var string $projectDir */
    protected $projectDir;


    public function __construct(
        string $projectDir
    )
    {
        $this->projectDir = $projectDir;
    }

    /**
     * @return int|string
     */
    public function getSubLevel()
    {
        return $this->subLevel;
    }

    /**
     * @param int|string $subLevel
     *
     * @return FileReader
     */
    public function setSubLevel($subLevel): FileReader
    {
        $this->subLevel = $subLevel;

        return $this;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     *
     * @return FileReader
     */
    public function setLevel(int $level): FileReader
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return "level{$this->level}_{$this->subLevel}";
    }

    public function read($delimiter = ' ')
    {
        $dir = $this->projectDir . self::BASE_BATH . $this->getLevel() . '/in/';

        if (!file_exists($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new \Exception('file not exists');
        }

        $fullPath = $dir . $this->getFileName() . '.in';

        $fp = fopen($fullPath, 'rb');
        $arr = [];

        while ($row = fgets($fp)) {
            $explodedRow = explode($delimiter, $row);
            $replacedRow = [];
            foreach ($explodedRow as $item) {
                $matched = null;
                preg_match_all("/[^(\r|\n|\r\n)]+/", $item, $matched);
                if (count($matched) && count($matched[0])) {
                    $replacedRow[] = $matched[0][0];
                }
            }

            $arr[] = $replacedRow;
        }

        return $arr;
    }

    public function write($data, $delimiter = ' ')
    {
        $dir = $this->projectDir . self::BASE_BATH . $this->getLevel() . '/out/';

        if (!file_exists($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new \Exception('file not exists');
        }

        $fullPath = $dir . $this->getFileName() . '.out';
        $fp = fopen($fullPath, 'wb');
        $arr = [];

        $isArray = false;
        $isMatrix = false;
        if (is_array($data) && count($data)) {
            $isArray = true;
            if (isset($data[0]) && is_array($data[0]) && count($data[0])) {
                $isMatrix = true;
            }
        }

        try {
            if ($isMatrix) {
                foreach ($data as $row) {
                    if (is_float($row[0])){
                        $str = '';
                        foreach ($row as $item) {
                            $str .= sprintf('%.3f', $item) . $delimiter;
                        }

                        fwrite($fp, $str . "\n");
                    } else {
                        fwrite($fp, implode($delimiter, $row). "\n");
                    }
                }
            } elseif ($isArray) {
                $str = '';
                if (is_float($data[0])){
                    foreach ($data as $item) {
                        $str .= sprintf('%.3f', $item) . $delimiter;
                }
                    fwrite($fp, $str . "\n");
                } else {
                    fwrite($fp, implode($delimiter, $data). "\n");
                }


            } else {
                fwrite($fp, $data . "\n");
            }

            fclose($fp);
        } catch (\Exception $exception) {
            return false;
        }

        return true;
    }
}
