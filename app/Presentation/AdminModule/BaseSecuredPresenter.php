<?php declare(strict_types=1);

namespace App\Presentation\AdminModule;

abstract class BaseSecuredPresenter extends BasePresenter
{
    public function startup(): void
    {
        // Zavola rodice !!!
        parent::startup();

        // Pokud neni uzivatel prihlasen, presmeruje ho na stranku prihlaseni
        if (!$this->user->isLoggedIn()) {
            // Pokud byl uzivatel odhlasen automaticky
            if ($this->user->getLogoutReason() === \Nette\Security\User::LogoutInactivity) {
                $this->flashMessage('Byl jste automaticky odhlášen z administračního systému z důvodu neaktivity.', 'warning');
            }

            // A pokud neni, presmeruje na stranku prihlaseni
            $this->redirect(':AdminModule:SignIn:');
        }
    }

    public function beforeRender()
    {
        parent::beforeRender();

        $this->addBreadcrumbItem('Default', 'Úvod');
    }

    public function handleLogout(): void
    {
        $this->user->logout(true);
        $this->flashMessage('Byl jste úspěšně odhlášen.', 'info');
        $this->redirect(':AdminModule:SignIn:');
    }
}
