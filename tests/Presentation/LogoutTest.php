<?php declare(strict_types=1);

namespace Tests\Presentation;

use Nette\Application\Responses\RedirectResponse;
use Nette\Security\User;
use Tests\PresenterTestCase;

final class LogoutTest extends PresenterTestCase
{
    private function isLoggedIn(): bool
    {
        return self::container()->getByType(User::class)->isLoggedIn();
    }

    public function testTheLogoutLinkSignsTheAdminOutAndShowsTheSignInPage(): void
    {
        $this->loginAsAdmin();
        $this->assertTrue($this->isLoggedIn());

        $response = $this->signal('AdminModule:Cms', 'logout');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('prihlaseni', $response->getUrl());
        $this->assertFalse($this->isLoggedIn());
    }

    public function testAfterLoggingOutAdminPagesAreClosedAgain(): void
    {
        $this->loginAsAdmin();
        $this->signal('AdminModule:Cms', 'logout');

        $response = $this->runPresenter('AdminModule:Cms', ['action' => 'default']);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('prihlaseni', $response->getUrl());
    }

    public function testTheLogoutLinkWorksOnEverySecuredPage(): void
    {
        foreach (['AdminModule:Default', 'AdminModule:Gallery', 'AdminModule:File'] as $presenter) {
            $this->loginAsAdmin();

            $this->signal($presenter, 'logout');

            $this->assertFalse($this->isLoggedIn(), $presenter);
        }
    }

    public function testTheSignInPageHasItsOwnLogoutAction(): void
    {
        $this->loginAsAdmin();

        $response = $this->runPresenter('AdminModule:SignIn', ['action' => 'out']);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertFalse($this->isLoggedIn());
    }

    public function testLoggingOutWhileAnonymousJustSendsYouToSignIn(): void
    {
        $response = $this->signal('AdminModule:Cms', 'logout');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertFalse($this->isLoggedIn());
    }
}
