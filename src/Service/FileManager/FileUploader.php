<?php

namespace App\Service\FileManager;

use League\Flysystem\FilesystemOperator;

class FileUploader
{

    private FilesystemOperator $defaultStorage;

    public function __construct(FilesystemOperator $defaultStorage)
    {
        $this->defaultStorage = $defaultStorage;
    }

    public function getFileNameFromBase64File(string $base64File, string $filePrefix): string
    {
        $extension = explode('/', mime_content_type($base64File))[1];
        $data = explode(',', $base64File);
        $filename = sprintf('%s.%s', uniqid($filePrefix, true), $extension);
        return $filename;
    }

    public function uploadBase64File(string $filename, string $base64File, string $filePrefix)
    {
        //TODO: Try Catch Statment
        $extension = explode('/', mime_content_type($base64File))[1];
        $data = explode(',', $base64File);
        $this->defaultStorage->write($filename, base64_decode($data[1]));
        return;
    }

    public function deleteFile(string $fileName)
    {
        if ($this->defaultStorage->fileExists($fileName)) {

            $this->defaultStorage->delete($fileName);
        }
        // $data = explode(',', $base64File);
        // $filename = sprintf('%s.%s', uniqid('user_', true), $extension);

        return;
    }
}
