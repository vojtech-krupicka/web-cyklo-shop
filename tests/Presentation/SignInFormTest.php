<?php declare(strict_types=1);

namespace Tests\Presentation;

use App\Model\Member\MemberFacade;
use Nette\Application\Responses\RedirectResponse;
use Nette\Security\User;
use Tests\PresenterTestCase;

final class SignInFormTest extends PresenterTestCase
{
    private function signIn(string $username, string $password): \Nette\Application\Response
    {
        return $this->submitForm('AdminModule:SignIn', 'signInForm', [
            'username' => $username,
            'password' => $password,
            'send' => 'Přihlásit',
        ], ['action' => 'default']);
    }

    private function isLoggedIn(): bool
    {
        return self::container()->getByType(User::class)->isLoggedIn();
    }

    public function testValidCredentialsLogTheAdminIn(): void
    {
        $response = $this->signIn('test-admin', 'aaa');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('/admin/', $response->getUrl());
        $this->assertTrue($this->isLoggedIn());
    }

    public function testSuccessfulLoginStoresTheLastLoginTime(): void
    {
        $facade = self::container()->getByType(MemberFacade::class);
        $this->assertNull($facade->getById(1)?->lastLogin, 'fixture: never logged in');

        $this->signIn('test-admin', 'aaa');

        $this->assertInstanceOf(\DateTime::class, $facade->getById(1)?->lastLogin);
    }

    public function testWrongPasswordShowsAnErrorAndStaysLoggedOut(): void
    {
        $html = $this->renderPage($this->signIn('test-admin', 'wrong'));

        $this->assertStringContainsString('Invalid password.', $html);
        $this->assertFalse($this->isLoggedIn());
    }

    public function testUnknownUserShowsAnError(): void
    {
        $html = $this->renderPage($this->signIn('nobody', 'aaa'));

        $this->assertStringContainsString('User not found.', $html);
        $this->assertFalse($this->isLoggedIn());
    }

    public function testEmptyFormShowsValidationMessages(): void
    {
        $html = $this->renderPage($this->signIn('', ''));

        $this->assertStringContainsString('Musíte vyplnit Vaše přihlašovací jméno', $html);
        $this->assertStringContainsString('Vyplňte prosím Vaše heslo.', $html);
        $this->assertFalse($this->isLoggedIn());
    }
}
