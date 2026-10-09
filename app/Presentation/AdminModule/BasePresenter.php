<?php declare(strict_types=1);

namespace App\Presentation\AdminModule;

use App\Components\AdminModule\MainMenu;
use Nette\DI\Attributes\Inject;
use App;

abstract class BasePresenter extends App\Presentation\BasePresenter
{
    #[Inject]
    public MainMenu\MainMenuControlFactory $mainMenuControlFactory;

    protected function createComponentMainMenu(): MainMenu\MainMenuControl
    {
        return $this->mainMenuControlFactory->create();
    }
}
