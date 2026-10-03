<?php declare(strict_types=1);

namespace App\Components\FrontModule\BreadcrumbMenu;

use Nette\Application\UI\Control;


final class BreadcrumbMenuItem
{
    public function __construct(
        public readonly string $presenter,
        public readonly string $title,
        public readonly string $action = "",
        public readonly ?string $argName = null,
        public readonly mixed $argValue = null,
    ) { }
}

final class BreadcrumbMenuControl extends Control
{

	public function render(?array $items = null): void
	{
		$this->template->menuItems = $items;
		$this->template->render(__DIR__ . '/breadcrumb.latte');
	}

}
