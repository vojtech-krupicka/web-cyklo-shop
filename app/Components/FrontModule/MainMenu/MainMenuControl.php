<?php declare(strict_types=1);

namespace App\Components\FrontModule\MainMenu;

use App\Model;
use Nette\Application\UI\Control;

final class MainMenuControl extends Control
{
    public function __construct(
        private Model\Gallery\GalleryFacade $galleryFacade,
    ) {}

    public function renderFlat(): void
    {
        $this->template->menuItems = $this->createMenuItems();
        $this->template->render(__DIR__ . '/menu.latte');
    }

    public function renderWithGalleries(): void
    {
        $this->template->menuItems = $this->createMenuItems();
        $this->template->galleries = $this->galleryFacade->getGalleries(true, 'added ASC');
        $this->template->render(__DIR__ . '/menu-with-galleries.latte');
    }

    /**
     * @return list<MainMenuItem>
     */
    private function createMenuItems(): array
    {
        $presenterName = $this->getPresenter()->getName() ?? '';
        return [
            new MainMenuItem('Homepage', 'Úvod', $presenterName),
            new MainMenuItem('Gallery', 'Galerie', $presenterName),
            // new MainMenuItem('Guestbook', 'Diskuze', $presenterName),
            new MainMenuItem('Contact', 'Kontakt', $presenterName),
            new MainMenuItem('Sitemap', 'Mapa stránek', $presenterName),
        ];
    }
}
