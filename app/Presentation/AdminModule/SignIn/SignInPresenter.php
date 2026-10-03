<?php declare(strict_types=1);

namespace App\Presentation\AdminModule\SignIn;

use App\Presentation\AdminModule;
use Nette\Application\UI;
use Nette\Security;

class SignInFormData
{
    public string $username;
    public string $password;
    public bool $remember;
}

final class SignInPresenter extends AdminModule\BasePresenter
{
    public function actionDefault(): void
    {
        // Check if user is already logged, and if its true, redirect to default presenter.
        if ($this->user->isLoggedIn()) {
            $this->redirect(':AdminModule:Default:');
            exit();
        }
    }

    public function renderDefault(): void
    {
        $this->template->pageHeading = 'Přihlášení';
    }

    protected function createComponentSignInForm(): UI\Form
    {
        $form = new UI\Form;
        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = 'table class="form"';

        $form
            ->addText('username', '*Přihlašovací jméno:')
            ->setRequired('Musíte vyplnit Vaše přihlašovací jméno do administračního systému.');

        $form
            ->addPassword('password', '*Heslo:')
            ->setRequired('Vyplňte prosím Vaše heslo.');

        $form->addCheckbox('remember', 'Zůstat přihlášený?');

        $form->addSubmit('send', 'Přihlásit');

        $form->onSuccess[] = $this->signInFormSubmitted(...);
        return $form;
    }

    public function signInFormSubmitted(UI\Form $form, SignInFormData $values): void
    {
        try {
            if ($values->remember) {
                $this->user->setExpiration('+7days', false);
            } else {
                $this->user->setExpiration('+90minutes', true);
            }
            $this->user->login($values->username, $values->password);
            $this->flashMessage('Byl jste úspěšně přihlášen do administračního systému.', 'info');
            $this->redirect('Default:');
        } catch (Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }

    public function actionOut(): void
    {
        $this->user->logout(true);
        $this->flashMessage('Byl jste úspěšně odhlášen.', 'info');
        $this->redirect('default');
        exit();
    }
}
