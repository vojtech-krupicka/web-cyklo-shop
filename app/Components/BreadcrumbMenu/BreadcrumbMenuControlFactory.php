<?php declare(strict_types=1);

namespace App\Components\BreadcrumbMenu;

interface BreadcrumbMenuControlFactory
{
    public function create(): BreadcrumbMenuControl;
}
