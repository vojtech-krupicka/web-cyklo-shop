<?php
/**
 * @filesource  BasePresenter.php
 * 
 * @author      Ing. Vojtech Krupicka
 * @copyright   HassaSimple Framework
 * @version     1.0.0 
 */



/**
 * @abstract  \BasePresenter 
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	
	/**
	 * 
	 */
	public function startup()
	{
		// Zavola rodice !!!
		parent::startup(); 
		
		// Nastavi generovani absolutnich URL
		$this->absoluteUrls = true;
		
		// Nastaveni ticheho modu pro generovani odkazu
		$this->invalidLinkMode = self::INVALID_LINK_WARNING;
		
		return;
	} // startup()
	
	
	
	/**
	 * Overloading flash message method for authomatic invalidate snippet.
	 * @param mixed $message
	 * @param mixed $type
	 * @return stdClass
	 */
	public function flashMessage($message, $type = 'info')
	{
		parent::flashMessage($message, $type);
		
		if($this->isAjax()) {
			$this->invalidateControl("flashes");
		}
	} // flashMessage()
	

} // class \BasePresenter
