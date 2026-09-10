<?php
/**
 * @filesource  \AdminModule\GuestbookPresenter.php
 *
 * @author      Ing. Vojtech Krupicka
 * @copyright   HassaSimple Framework
 * @package     \AdminModule
 * @version     1.0.0 
 */



// Namespace definition
namespace AdminModule;


/**
 * @abstract  \AdminModule\GuestbookPresenter 
 * Presenter for discussion.
 */
class GuestbookPresenter extends \AdminModule\BaseSecuredPresenter
{

	/**
	 * Render default action.
	 */
	public function renderDefault()
	{
		
	} // renderDefault()
	
	
	
	protected function createComponentGuestbookComments()
	{
		$comments = $this->context->createComments()->getGuestbookComments()->order('added ASC');
		$control = new \Components\CommentsControl\CommentsControl($comments);
		return $control;
	}
	

} // class \AdminModule\GuestbookPresenter 
