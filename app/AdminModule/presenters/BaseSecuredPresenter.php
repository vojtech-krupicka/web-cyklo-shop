<?php 
/**
 * @filesource  \AdminModule\BaseSecuredPresenter.php
 *
 * @author      Ing. Vojtech Krupicka
 * @copyright   HassaSimple Framework
 * @package     \AdminModule
 * @version     1.0.0 
 */
 
 
// Namespace definition
namespace AdminModule;


/**
 * @abstract  \AdminModule\BaseSecuredPresenter 
 * Base presenter in Admin module for all admin presenters which may be protected.
 */
abstract class BaseSecuredPresenter extends \AdminModule\BasePresenter
{
	
	/** @persistent int */
	public $id = NULL;


	
	/**
	 * Start up method, check if user is already logged in.	
	 */
	public function startup()
	{
		// Zavola rodice !!!
		parent::startup(); 
				
		// Pokud neni uzivatel prihlasen, presmeruje ho na stranku prihlaseni
		if(!$this->user->isLoggedIn()) {
			// Pokud byl uzivatel odhlasen automaticky
			if($this->user->getLogoutReason() === \Nette\Security\User::INACTIVITY) {
				$this->flashMessage('Byl jste automaticky odhlášen z administračního systému z důvodu neaktivity.', 'warning');
			}
		
			// A pokud neni, presmeruje na stranku prihlaseni
			$this->redirect(':Admin:Sign:');
			die;			
		}
	} // startup()
	
	
	
	/**
	 * put your comment there...
	 */
	public function beforeRender() 
	{
		$this->template->id = $this->id;
	}
	
	
	
	/**
	 * Logout signal handle.
	 */
	public function handleLogout()
	{
		$this->user->logout(true);
		$this->flashMessage('Byl jste úspěšně odhlášen.', 'info');
		$this->redirect(':Admin:Sign:'); 
		exit();
	} // handleLogout()
	
		
} // class \AdminModule\BaseSecuredPresenter  
