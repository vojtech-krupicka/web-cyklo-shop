<?php declare(strict_types=1);

namespace App\Presentation\AdminModule\Cms;

class MenuItemEditFormData
{
    public string $name;
    public string $title;
    public string $fullUrl;
    public string $url;
    public ?int $parentId;
}
