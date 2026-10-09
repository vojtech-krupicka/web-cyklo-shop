<?php declare(strict_types=1);

namespace App\Presentation\AdminModule\File;

use Nette\Http\FileUpload;

class FileAddFormData
{
    public string $name;
    public bool $overwrite;
    public FileUpload $file;
}
