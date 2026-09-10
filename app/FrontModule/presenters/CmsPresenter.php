<?php
/**
 * @filesource  \FrontModule\CmsPresenter.php
 * 
 * @author      Ing. Vojtech Krupicka
 * @copyright   HassaSimple Framework
 * @package     \FrontModule
 * @version     1.0.0 
 */
 
 
 
// Namespace definition
namespace FrontModule;



/**
 * @abstract  \FrontModule\CmsPresenter 
 * Presenter in Front module for CMS pages.
 */
class CmsPresenter extends \FrontModule\BasePresenter
{

	/** @persistent */
	public $uri = NULL;
	
	private $menuItem = NULL;
	private $page = NULL;
	
	
	public function renderDefault()
	{
		// Get current menu item
		$this->menuItem = $this->context->createMenuItems()->where('url', $this->uri)->where('active', true)->order('sort_order ASC')->limit(1)->fetch();
		if(!$this->menuItem) {
			throw new \Nette\Application\BadRequestException("Stránka s URL '".$this->uri."' neexistuje!");
		}
		
		
		// Get page
		$this->page = $this->context->createPages()->get($this->menuItem['page_id']);
		if(!$this->page) {
			throw new \Nette\Application\BadRequestException("Stránka s URL '".$this->uri."' neexistuje!");
		}
		
		
		$parentItems = array();
		$parentItems[] = $this->menuItem;
		
		$parentObj = $this->context->createMenuItems()->where('id', $this->menuItem['parent_id'])->where('active', true)->fetch();
		while($parentObj) {
			$parentItems[] = $parentObj;
			$parentId = $parentObj['parent_id'];
			$parentObj = $this->context->createMenuItems()->where('id', $parentId)->where('active', true)->fetch();
		}
		
		// Set template
		$this->template->parentItems = array_reverse($parentItems); 
		$this->template->menuItem = $this->menuItem;
		$this->template->activeItemId = $this->menuItem->id;
		
		if(empty($this->page['seo_title'])) {
			$this->page['seo_title'] = $this->context->parameters['defaults']['seoTitle'];
		}
		if(empty($this->page['seo_keywords'])) {
			$this->page['seo_keywords'] = $this->context->parameters['defaults']['seoKeywords'];
		}
		if(empty($this->page['seo_description'])) {
			$this->page['seo_description'] = $this->context->parameters['defaults']['seoDescription'];
		}
		$this->template->page = $this->page;
		
		
	}
	
	
	
	protected function createComponentPageComments()
	{
		$comments = $this->context->createComments()->getPageComments($this->page->id)->order('added ASC');
		$control = new \Components\CommentsControl\CommentsControl($comments, $this->page);
		return $control;
	}
	

} // class \FrontModule\CmsPresenter
