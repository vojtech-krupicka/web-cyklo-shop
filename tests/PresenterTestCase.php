<?php declare(strict_types=1);

namespace Tests;

use App\Core\AdminIdentity;
use App\Model\Member\MemberEntity;
use Nette\Application\IPresenterFactory;
use Nette\Application\Request;
use Nette\Application\Response;
use Nette\Application\Responses\TextResponse;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Application\UI\Presenter;
use Nette\Security\User;

/**
 * Base class for presenter tests: runs a presenter from the real DI container
 * (against the test database) and returns its response.
 */
abstract class PresenterTestCase extends DatabaseTestCase
{
    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $post
     */
    protected function runPresenter(string $name, array $params = [], string $method = 'GET', array $post = []): Response
    {
        $presenter = self::container()->getByType(IPresenterFactory::class)->createPresenter($name);
        $this->assertInstanceOf(Presenter::class, $presenter);
        $presenter->autoCanonicalize = false;

        return $presenter->run(new Request($name, $method, $params, $post));
    }

    /** Renders the whole page (layout, menus, footer) and returns the HTML. */
    protected function renderPage(Response $response): string
    {
        $this->assertInstanceOf(TextResponse::class, $response);
        $source = $response->getSource();
        // renderToString() lives on the Latte implementation, not on the UI\Template interface
        $this->assertInstanceOf(Template::class, $source);

        return $source->renderToString();
    }

    protected function loginAsAdmin(): void
    {
        $member = new MemberEntity(
            id: 1,
            password: '',
            username: 'test-admin',
            firstname: 'Test',
            surname: 'Admin',
            email: 'test-admin@example.com',
            role: 'admin',
            active: true,
            lastLogin: null,
        );

        self::container()->getByType(User::class)->login(AdminIdentity::fromMember(['admin'], $member));
    }

    protected function tearDown(): void
    {
        $user = self::container()->getByType(User::class);
        if ($user->isLoggedIn()) {
            $user->logout(true);
        }
        parent::tearDown();
    }
}
