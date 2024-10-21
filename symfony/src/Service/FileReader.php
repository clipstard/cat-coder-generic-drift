<?php

namespace App\Service;

class FileReader
{

    const BASE_BATH = '/public/level';

    /** @var string $projectDir */
    protected $projectDir;


    public function __construct(
        string $projectDir
    )
    {
        $this->projectDir = $projectDir;
    }

    public function read($filename, $delimiter = ' ')
    {
        $fullPath = $this->projectDir . self::BASE_BATH . '/' . $filename;
        if (!file_exists($fullPath)) {
            throw new \Exception('file not exists');
        }

        $fp = fopen($fullPath, 'rb');
        $arr = [];

        while ($row = fgets($fp, 4096)) {
            $explodedRow = explode($delimiter, $row);
            $replacedRow = [];
            foreach ($explodedRow as $item) {
                $matched = null;
                preg_match_all("/[^\r\n]+/", $item, $matched);
                if (count($matched) && count($matched[0])) {
                    $replacedRow[] = $matched[0][0];
                }
            }

            $arr[] = $replacedRow;
        }

        return $arr;
    }
}