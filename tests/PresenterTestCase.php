<?php declare(strict_types=1);

namespace Tests;

use App\Core\AdminIdentity;
use App\Model\Member\MemberEntity;
use Nette\Application\IPresenterFactory;
use Nette\Application\Request;
use Nette\Application\Response;
use Nette\Application\Responses\TextResponse;
use Nette\Bridges\ApplicationLatte\Template;
use App\Model\Settings\AppSettings;
use Nette\Application\UI\Presenter;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;
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
     * @param array<string, mixed> $files
     */
    protected function runPresenter(string $name, array $params = [], string $method = 'GET', array $post = [], array $files = []): Response
    {
        $presenter = self::container()->getByType(IPresenterFactory::class)->createPresenter($name);
        $this->assertInstanceOf(Presenter::class, $presenter);
        $presenter->autoCanonicalize = false;

        return $presenter->run(new Request($name, $method, $params, $post, $files));
    }

    /**
     * Submits a form component the way a browser does: POST with the "<form>-submit" signal.
     *
     * @param array<string, mixed> $post
     * @param array<string, mixed> $params
     * @param array<string, mixed> $files
     */
    protected function submitForm(string $presenter, string $form, array $post, array $params = [], array $files = []): Response
    {
        return $this->runPresenter($presenter, ['do' => "$form-submit"] + $params, 'POST', $post, $files);
    }

    /** The sandbox directory for uploads (see config/local.test.neon). */
    protected function resourcesDir(): string
    {
        $dir = self::container()->getByType(AppSettings::class)->resourcesDir;
        self::assertStringContainsString('test-resources', $dir, 'tests must never touch the real resources directory');

        return $dir;
    }

    /** Creates a temporary file that Nette's FileUpload can move, like a browser upload. */
    protected function upload(string $name, string $contents): FileUpload
    {
        $tmp = tempnam(sys_get_temp_dir(), 'upload');
        file_put_contents($tmp, $contents);

        return new FileUpload([
            'name' => $name,
            'full_path' => $name,
            'type' => '',
            'size' => strlen($contents),
            'tmp_name' => $tmp,
            'error' => UPLOAD_ERR_OK,
        ]);
    }

    /** A real 1x1 PNG. */
    protected function uploadImage(string $name = 'obrazek.png'): FileUpload
    {
        return $this->upload($name, (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='));
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

    protected function setUp(): void
    {
        parent::setUp();
        FileSystem::delete($this->resourcesDir());
    }

    protected function tearDown(): void
    {
        FileSystem::delete($this->resourcesDir());

        $user = self::container()->getByType(User::class);
        if ($user->isLoggedIn()) {
            $user->logout(true);
        }
        parent::tearDown();
    }
}
