<?php declare(strict_types=1);

namespace App\Components\AdminModule\MainMenu;

interface MainMenuControlFactory
{
	public function create(): MainMenuControl;
}
