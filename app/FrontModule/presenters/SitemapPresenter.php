<?php
/**
 * @filesource  \FrontModule\SitemapPresenter.php
 * 
 * @author      Ing. Vojtech Krupicka
 * @copyright   HassaSimple Framework
 * @package     \FrontModule
 * @version     1.0.0 
 */
 
 
 
// Namespace definition
namespace FrontModule;



/**
 * @abstract  \FrontModule\SitemapPresenter 
 * Presenter in Front module for site map pages.
 */
class SitemapPresenter extends \FrontModule\BasePresenter
{
	
	
	public function renderDefault()
	{
		$this->template->cmsItems = $this->context->createMenuItems()->where('parent_id', NULL)->where('active', true)->order('sort_order ASC');
		$this->template->galleries = $this->context->createGalleries()->where('active', true)->order('added ASC');
	}

} // class \FrontModule\SitemapPresenter 
