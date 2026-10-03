<?php declare(strict_types=1);

namespace App\Components\FrontModule\BreadcrumbMenu;

interface BreadcrumbMenuControlFactory
{
	public function create(): BreadcrumbMenuControl;
}
