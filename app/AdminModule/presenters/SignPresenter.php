<?php
/**
 * @filesource  \AdminModule\SignPresenter.php
 *
 * @author      Ing. Vojtech Krupicka
 * @copyright   HassaSimple Framework
 * @package     \AdminModule
 * @version     1.0.0 
 */



// Namespace definition
namespace AdminModule;



// Used namespace
use Nette\Application\UI,
	Nette\Security as NS;

	

/**
 * @abstract \AdminModule\SignPresenter 
 * Sign presenter for sign administrator into admin module.
 */
class SignPresenter extends \AdminModule\BasePresenter
{

	/**
	 * Default action.
	 */
	public function actionDefault()
	{
		// Check if user is already logged, and if its true, redirect to default presenter.
		if($this->user->isLoggedIn()) {
			$this->redirect(':Admin:Default:');
			exit();
		}
		
		return;
	} // actionDefault()
	

	/**
	 * Sign in form component factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		$form = new UI\Form;
		$renderer = $form->getRenderer();
		$renderer->wrappers['controls']['container'] = 'table class="form"';
 		
 		$form->addText('username', '*Přihlašovací jméno:')
			->setRequired('Musíte vyplnit Vaše přihlašovací jméno do administračního systému.');

		$form->addPassword('password', '*Heslo:')
			->setRequired('Vyplňte prosím Vaše heslo.');

		$form->addCheckbox('remember', 'Zůstat přihlášený?');

		$form->addSubmit('send', 'Přihlásit');

		$form->onSuccess[] = $this->signInFormSubmitted;
		return $form;
	} // createComponentSignInForm()



	/**
	 * put your comment there...
	 * @param mixed $form
	 */
	public function signInFormSubmitted($form)
	{
		try {
			$values = $form->getValues();
			if ($values->remember) {
				$this->user->setExpiration('+ 14 days', false);
			} else {
				$this->user->setExpiration('+ 90 minutes', true, true);
			}
			$this->user->login($values->username, $values->password);
			$this->flashMessage('Byl jste úspěšně přihlášen do administračního systému.', 'info');
			$this->redirect('Default:');

		} catch (NS\AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	} // signInFormSubmitted()



	/**
	 * Logout action.
	 */
	public function actionOut()
	{
		$this->user->logout(true);
		$this->flashMessage('Byl jste úspěšně odhlášen.', 'info');
		$this->redirect('default'); 
		exit();
	} // actionOut()

	
} // class \AdminModule\SignPresenter 
