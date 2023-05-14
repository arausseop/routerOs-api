<?php

namespace App\Service\FileManager;

use League\Flysystem\FilesystemOperator;

class FileDeleter
{

    private FilesystemOperator $defaultStorage;

    public function __construct(FilesystemOperator $defaultStorage)
    {
        $this->defaultStorage = $defaultStorage;
    }

    public function __invoke(string $path): void
    {
        $this->defaultStorage->delete($path);
    }
}
