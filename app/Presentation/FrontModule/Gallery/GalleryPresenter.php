<?php declare(strict_types=1);

namespace App\Presentation\FrontModule\Gallery;

use App\Presentation\FrontModule;


final class GalleryPresenter extends FrontModule\BasePresenter
{

    public function __construct(
        private \Model\GalleryFacade $galleryFacade
    )
    { }

    public function renderDefault()
	{
		$this->template->galleries = $this->galleryFacade->getGalleries(true, "added ASC");
	}

	public function renderDetail(int $id)
	{
		$gallery  = $this->galleryFacade->getGallery($id);
		if($gallery) {
			$this->template->gallery = $gallery;
			$this->template->galleryItems = $this->galleryFacade->getGalleryItems($id, true, 'sort_order ASC');
		}
		else {
			$this->flashMessage("Galerie s id '#".$id."' neexistuje!", 'error');
			$this->redirect(':FrontModule:Gallery:default', array('id' => NULL));
		}
	}

    public function getRandomGalleryItem(int $galleryId)
    {
        return $this->galleryFacade->getGalleryItems($galleryId, true, 'RAND()', 1)->fetch();
    }

}
