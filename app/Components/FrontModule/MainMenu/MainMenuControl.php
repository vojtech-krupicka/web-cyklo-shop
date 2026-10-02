<?php declare(strict_types=1);

namespace App\Components\FrontModule\MainMenu;

use Nette\Application\UI\Control;


final class MainMenuItem
{
    public bool $isActive = false;

    public function __construct(
        public readonly string $presenter,
        public readonly string $title,
        string $presenterName,
    ) {
        $this->isActive = $presenterName === 'FrontModule:' . $presenter;
    }
}

final class MainMenuControl extends Control
{

	public function render(): void
	{

        $presenterName = $this->getPresenter()->getName();
		$this->template->menuItems = [
            new MainMenuItem('Homepage', 'Úvod', $presenterName),
            new MainMenuItem('Gallery', 'Galerie', $presenterName),
            new MainMenuItem('Guestbook', 'Diskuze', $presenterName),
            new MainMenuItem('Contact', 'Kontakt', $presenterName),
            new MainMenuItem('Sitemap', 'Mapa stránek', $presenterName),
        ];
		$this->template->render(__DIR__ . '/menu.latte');
	}

}
