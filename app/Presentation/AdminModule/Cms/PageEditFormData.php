<?php declare(strict_types=1);

namespace App\Presentation\AdminModule\Cms;

class PageEditFormData
{
    public string $heading;
    public string $seoTitle;
    public string $seoKeywords;
    public string $seoDescription;
    public string $content;
    public int $allowComments;
}
