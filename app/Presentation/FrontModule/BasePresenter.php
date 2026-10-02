<?php declare(strict_types=1);

namespace App\Presentation\FrontModule;

use App;
use Nette\Application\Attributes\Persistent;
use Nette\DI\Attributes\Inject;

abstract class BasePresenter extends App\Presentation\BasePresenter
{

    #[Persistent]
	public int | null $id = null;

    #[Inject]
    public \Model\MenuItemsFacade $menuItemsFacade;

	public function beforeRender()
	{
		parent::beforeRender();

		$this->template->activeItemId = null;
        $this->template->presenterName = $this->getName();
		$this->template->menuItems = $this->menuItemsFacade->getMenuItems(null, true, "ASC");
	}

}
