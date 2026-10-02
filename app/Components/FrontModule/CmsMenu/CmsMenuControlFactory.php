<?php declare(strict_types=1);

namespace App\Components\FrontModule\CmsMenu;

interface CmsMenuControlFactory
{
	public function create(): CmsMenuControl;
}
