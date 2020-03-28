<?php

namespace App\Controller;

use App\Service\FileReader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ZipArchive;

class FileUploader extends AbstractController
{
    /** @var string */
    protected $projectDir;

    public function __construct(
        string $projectDir
    )
    {
        $this->projectDir = $projectDir;
    }

    public function __invoke(Request $request)
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('file');
        try {
            $path = $this->projectDir . '/public/uploads/';
            if (!file_exists($path) && !mkdir($path, 0777, true) && !is_dir($path)) {
                throw new \RuntimeException('directory no exists' . $path);
            }

            $fileName = $file->getClientOriginalName() ?? 'level.zip';
            $file->move($path, $fileName);

        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()]);
        }

        $zip = new ZipArchive();
        $match = null;

        preg_match_all('/[0-9]+/', $fileName, $match);
        $level = 0;

        if (count($match) && count($match[0])) {
            $level = $match[0][0];
        }

        $fullPath = $this->projectDir . FileReader::BASE_BATH . $level . '/in/';

        if (!file_exists($fullPath) && !mkdir($fullPath, 0777, true) && !is_dir($fullPath)) {
            throw new \Exception('file not exists');
        }

        if ($zip->open($path . $fileName) === TRUE) {
            $zip->extractTo($fullPath);
            $zip->close();
            $response = 'ok';
        } else {
            $response = 'failed';
        }

        return new JsonResponse(['message' => $response]);
    }
}