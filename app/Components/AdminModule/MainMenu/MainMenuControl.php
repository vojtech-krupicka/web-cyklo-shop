<?php declare(strict_types=1);

namespace App\Components\AdminModule\MainMenu;

use Nette\Application\UI\Control;

final class MainMenuItem
{
    public bool $isActive = false;

    public function __construct(
        public readonly string $presenter,
        public readonly string $title,
        string $presenterName,
    ) {
        $this->isActive = $presenterName === 'AdminModule:' . $presenter;
    }
}

final class MainMenuControl extends Control
{
    public function render(): void
    {
        $this->template->menuItems = $this->createMenuItems();
        $this->template->render(__DIR__ . '/menu.latte');
    }

    private function createMenuItems(): array
    {
        $presenterName = $this->getPresenter()->getName();
        return [
            new MainMenuItem('Default', 'Úvod', $presenterName),
            new MainMenuItem('Cms', 'Vlastní stránky', $presenterName),
            // new MainMenuItem('File', 'Správce souborů', $presenterName),
            // new MainMenuItem('Guestbook', 'Diskuze', $presenterName),
            new MainMenuItem('Gallery', 'Galerie', $presenterName),
        ];
    }
}
