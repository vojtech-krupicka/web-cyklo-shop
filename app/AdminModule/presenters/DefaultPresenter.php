<?php
/**
 * @filesource  \AdminModule\DefaultPresenter.php
 *
 * @author      Ing. Vojtech Krupicka
 * @copyright   HassaSimple Framework
 * @package     \AdminModule
 * @version     1.0.0 
 */



// Namespace definition
namespace AdminModule;


/**
 * @abstract  \AdminModule\DefaultPresenter 
 * Default presenter for show admin homepage or admin help.
 */
class DefaultPresenter extends \AdminModule\BaseSecuredPresenter
{

	/**
	 * Render default action.
	 */
	public function renderDefault()
	{
		$this->template->members = $this->context->createMembers();
	} // renderDefault()
	

} // class \AdminModule\DefaultPresenter 
