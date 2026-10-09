<?php declare(strict_types=1);

namespace App\Presentation\AdminModule\Cms;

class MenuItemAddFormData
{
    public string $name;
    public string $title;
    public string $fullUrl;
    public string $url;
    public bool $extern;
}
