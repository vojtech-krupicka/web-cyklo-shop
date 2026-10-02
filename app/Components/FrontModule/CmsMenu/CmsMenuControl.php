<?php declare(strict_types=1);

namespace App\Components\FrontModule\CmsMenu;

use Nette\Application\UI\Control;

final class CmsMenuControl extends Control
{
	public function __construct(
		private \Model\MenuItemsFacade $menuItemsFacade,
	) {}


	public function renderSidebar(?int $activeItemId = null, ?string $heading = null): void
	{
		$this->renderTemplate(__DIR__ . '/sidebar.latte', $activeItemId, $heading);
	}

	public function renderFooter(?string $heading = null): void
	{
		$this->renderTemplate(__DIR__ . '/footer.latte', null, $heading);
	}

	public function renderTemplate(string $file, ?int $activeItemId = null, ?string $heading = null): void
	{
		$this->template->menuItems = $this->getMenuItems(null, true, "ASC");
		$this->template->activeItemId = $activeItemId;
		$this->template->heading = $heading;
		$this->template->render($file);
	}

    public function getMenuItems(?int $parentId = null, bool $activeOnly = true, string $order = "ASC"): \Nette\Database\Table\Selection
    {
        return $this->menuItemsFacade->getMenuItems($parentId, $activeOnly, $order);
    }
}
