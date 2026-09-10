<?php
/**
 * @filesource  \FrontModule\GalleryPresenter.php
 * 
 * @author      Ing. Vojtech Krupicka
 * @copyright   HassaSimple Framework
 * @package     \FrontModule
 * @version     1.0.0 
 */
 
 
 
// Namespace definition
namespace FrontModule;



/**
 * @abstract  \FrontModule\GalleryPresenter 
 * Presenter in Front module for gallery.
 */
class GalleryPresenter extends \FrontModule\BasePresenter
{

	
	public function renderDefault()
	{
		$this->template->galleries = $this->context->createGalleries()->where('active', true)->order('added ASC');
	}
	
	
	
	public function renderDetail($id)
	{
		$gallery  = $this->context->createGalleries()->where('active', true)->get($this->id);
		if($gallery) {
			$this->template->gallery = $gallery;
			$this->template->galleryItems = $this->context->createGalleryItems()->where('gallery_id', $this->id)->where('active', true)->order('sort_order ASC');
		}
		else {
			$this->flashMessage("Galerie s id '#".$id."' neexistuje!", 'error');
			$this->redirect(':Front:Gallery:default', array('id' => NULL));
		}
	}
	
	
	
	protected function createComponentGalleryComments()
	{
		$comments = $this->context->createComments()->getGalleryComments($this->id)->order('added ASC');
		$control = new \Components\CommentsControl\CommentsControl($comments);
		return $control;
	} // createComponentGalleryComments()
	

} // class \FrontModule\GalleryPresenter
