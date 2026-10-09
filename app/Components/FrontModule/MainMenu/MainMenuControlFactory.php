<?php declare(strict_types=1);

namespace App\Components\FrontModule\MainMenu;

interface MainMenuControlFactory
{
	public function create(): MainMenuControl;
}
