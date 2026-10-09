<?php declare(strict_types=1);

namespace Tests\Presentation;

use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\PresenterTestCase;

final class AdminModulePresenterTest extends PresenterTestCase
{
    /** @return iterable<string, array{string, array<string, mixed>}> */
    public static function provideSecuredPages(): iterable
    {
        yield 'dashboard'    => ['AdminModule:Default', ['action' => 'default']];
        yield 'cms list'     => ['AdminModule:Cms', ['action' => 'default']];
        yield 'item edit'    => ['AdminModule:Cms', ['action' => 'itemEdit', 'id' => 1]];
        yield 'page edit'    => ['AdminModule:Cms', ['action' => 'pageEdit', 'id' => 2]];
        yield 'gallery list' => ['AdminModule:Gallery', ['action' => 'default']];
        yield 'gallery edit' => ['AdminModule:Gallery', ['action' => 'edit', 'id' => 1]];
    }

    /** @param array<string, mixed> $params */
    #[DataProvider('provideSecuredPages')]
    public function testAnonymousUserIsSentToSignIn(string $presenter, array $params): void
    {
        $response = $this->runPresenter($presenter, $params);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('prihlaseni', $response->getUrl());
    }

    /** @param array<string, mixed> $params */
    #[DataProvider('provideSecuredPages')]
    public function testLoggedInAdminSeesTheCompletePage(string $presenter, array $params): void
    {
        $this->loginAsAdmin();

        $html = $this->renderPage($this->runPresenter($presenter, $params));

        $this->assertStringContainsString('Administrační systém', $html);
    }

    public function testSignInPageIsAvailableToAnonymousUsers(): void
    {
        $response = $this->runPresenter('AdminModule:SignIn', ['action' => 'default']);

        $this->assertInstanceOf(TextResponse::class, $response);
        $this->assertSame('Přihlášení', $response->getSource()->pageHeading);
    }

    public function testSignInRedirectsAnAlreadyLoggedInAdmin(): void
    {
        $this->loginAsAdmin();

        $response = $this->runPresenter('AdminModule:SignIn', ['action' => 'default']);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testEditingUnknownMenuItemRedirectsToTheList(): void
    {
        $this->loginAsAdmin();

        $response = $this->runPresenter('AdminModule:Cms', ['action' => 'itemEdit', 'id' => 9999]);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testEditingUnknownPageRedirectsToTheList(): void
    {
        $this->loginAsAdmin();

        $response = $this->runPresenter('AdminModule:Cms', ['action' => 'pageEdit', 'id' => 9999]);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testHomepageCanBeEditedEvenThoughItHasNoMenuItem(): void
    {
        $this->loginAsAdmin();

        $response = $this->runPresenter('AdminModule:Cms', ['action' => 'pageEdit', 'id' => 1]);

        $this->assertInstanceOf(TextResponse::class, $response);
        $this->assertNull($response->getSource()->menuItem);
        $this->assertSame(1, $response->getSource()->page->id);
    }

    public function testEditingUnknownGalleryRedirectsToTheList(): void
    {
        $this->loginAsAdmin();

        $response = $this->runPresenter('AdminModule:Gallery', ['action' => 'edit', 'id' => 9999]);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }
}
