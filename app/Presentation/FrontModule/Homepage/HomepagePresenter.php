<?php declare(strict_types=1);

namespace App\Presentation\FrontModule\Homepage;

use App\Presentation\FrontModule;
use App\Model;

final class HomepagePresenter extends FrontModule\BasePresenter
{
    public function __construct(
        private Model\Page\PageFacade $pagesFacade,
        private Model\Gallery\GalleryFacade $galleryFacade
    ) {}

    public function renderDefault(): void
    {
        $this->template->homepage = $this->pagesFacade->getHomepage();
        $this->template->gallery = $this->galleryFacade->getGalleryItems(null, true, 'RAND()', 3);

        $this->template->breadcrumbItems = [];  // no breadcrumb for homepage
        $this->template->pageHeading = $this->template->homepage->heading ?? 'Vítejte na example.com';
    }
}
