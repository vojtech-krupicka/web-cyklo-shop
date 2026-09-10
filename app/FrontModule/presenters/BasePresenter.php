<?php
/**
 * @filesource  \FrontModule\BasePresenter.php
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
abstract class BasePresenter extends \BasePresenter
{
	
	/** @persistent int */
	public $id = NULL;


	public function beforeRender()
	{
		parent::beforeRender();
		$this->template->menuItems = $this->context->createMenuItems()->where('parent_id', NULL)->where('active', true)->order('sort_order ASC');
		$this->template->activeItemId = NULL;
	}
	
} // class \FrontModule\BasePresenter
