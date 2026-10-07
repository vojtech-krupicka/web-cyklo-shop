<?php declare(strict_types=1);

namespace App\Components\BreadcrumbMenu;

use Nette\Application\UI\Control;

final class BreadcrumbMenuControl extends Control
{
    /**
     * @param list<array{name: string, label: string}>|null $items
     */
    public function render(?array $items = null): void
    {
        $this->template->menuItems = $items;
        $this->template->render(__DIR__ . '/breadcrumb.latte');
    }
}
