<?php
/**
 * @filesource  \FrontModule\GuestbookPresenter.php
 * 
 * @author      Ing. Vojtech Krupicka
 * @copyright   HassaSimple Framework
 * @package     \FrontModule
 * @version     1.0.0 
 */
 
 
 
// Namespace definition
namespace FrontModule;



/**
 * @abstract  \FrontModule\GuestbookPresenter 
 * Presenter in Front module for guest book.
 */
class GuestbookPresenter extends \FrontModule\BasePresenter
{

	protected function createComponentGuestbookComments()
	{
		$comments = $this->context->createComments()->getGuestbookComments()->order('added ASC');
		$control = new \Components\CommentsControl\CommentsControl($comments);
		return $control;
	}
	

} // class \FrontModule\GuestbookPresenter
