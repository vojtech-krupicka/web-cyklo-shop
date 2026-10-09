<?php declare(strict_types=1);

namespace App\Presentation\AdminModule\Gallery;

use Nette\Http\FileUpload;

class GalleryMediaAddFormData
{
    public string $title;
    public string $description;
    public FileUpload $media;
}
