<?php

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
        $this->template->gallery = $this->galleryFacade->getGalleryItems('RAND()', 3);
	}

}
