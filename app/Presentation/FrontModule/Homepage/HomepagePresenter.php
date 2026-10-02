<?php declare(strict_types=1);

namespace App\Presentation\FrontModule\Homepage;

use App\Presentation\FrontModule;


final class HomepagePresenter extends FrontModule\BasePresenter
{

    public function __construct(
        private \Model\PagesFacade $pagesFacade,
        private \Model\GalleryFacade $galleryFacade
    )
    { }

    public function renderDefault()
	{
        $this->template->homepage = $this->pagesFacade->getHomepage();
        $this->template->gallery = $this->galleryFacade->getGalleryItems(null, true, "RAND()", 3);
	}

}
