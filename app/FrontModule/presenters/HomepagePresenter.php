<?php
/**
 * @filesource  BasePresenter.php
 * 
 * @author      Ing. Vojtech Krupicka
 * @copyright   HassaSimple Framework
 * @package     \FrontModule
 * @version     1.0.0 
 */
 
 
 
// Namespace definition
namespace FrontModule;



/**
 * @abstract  \FrontModule\BasePresenter 
 * Base presenter in Front module for all front presenters.
 */
class HomepagePresenter extends \FrontModule\BasePresenter
{

	public function renderDefault()
	{
		$this->template->gallery = $this->context->createGalleryItems()->order('RAND()')->limit(3);
		$this->template->homepage = $this->context->createPages()->where('is_homepage', true)->fetch();
	}

}
