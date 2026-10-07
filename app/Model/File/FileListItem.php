<?php declare(strict_types=1);

namespace App\Model\File;

class FileListItem
{
    public function __construct(
        public string $fileName,
        public string $extension,
        public string $name,
        public string $fullPath,
        public string $webPath,
        public int $fileSize,
        public \DateTime $modified
    ) {}
}
