<?php declare(strict_types=1);

namespace Tests\Core;

use App\Core\RouterFactory;
use Nette\Http\Request;
use Nette\Http\UrlScript;
use Nette\Routing\Router;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(RouterFactory::class)]
final class RouterFactoryTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $this->router = RouterFactory::createRouter();
    }

    /**
     * @param array<string, mixed> $expected
     */
    #[DataProvider('provideMatchingUrls')]
    public function testMatch(string $path, array $expected): void
    {
        $params = $this->router->match(new Request(new UrlScript("http://localhost$path", '/')));

        $this->assertNotNull($params, "No route matched '$path'");
        foreach ($expected as $key => $value) {
            $this->assertSame($value, $params[$key] ?? null, "Parameter '$key' of '$path'");
        }
    }

    /**
     * @return iterable<string, array{string, array<string, mixed>}>
     */
    public static function provideMatchingUrls(): iterable
    {
        yield 'home' => ['/', ['presenter' => 'FrontModule:Homepage', 'action' => 'default']];
        yield 'contact' => ['/kontakt', ['presenter' => 'FrontModule:Contact', 'action' => 'default']];
        yield 'gallery detail' => ['/galerie/detail/3', ['presenter' => 'FrontModule:Gallery', 'action' => 'detail', 'id' => '3']];
        yield 'cms page' => ['/historie.html', ['presenter' => 'FrontModule:Cms', 'uri' => 'historie']];
        yield 'cms nested page' => ['/sortiment/nahradni-dily.html', ['presenter' => 'FrontModule:Cms', 'uri' => 'sortiment/nahradni-dily']];
        yield 'admin home' => ['/admin/', ['presenter' => 'AdminModule:Default', 'action' => 'default']];
        yield 'admin sign in' => ['/admin/prihlaseni', ['presenter' => 'AdminModule:SignIn', 'action' => 'default']];
        yield 'admin item edit' => ['/admin/vlastni-stranky/upravit-polozku-menu/12', ['presenter' => 'AdminModule:Cms', 'action' => 'itemEdit', 'id' => '12']];
    }

    public function testUrlGeneration(): void
    {
        $refUrl = new UrlScript('http://localhost/', '/');

        $this->assertSame(
            'http://localhost/kontakt',
            $this->router->constructUrl(['presenter' => 'FrontModule:Contact', 'action' => 'default'], $refUrl),
        );
    }

    public function testUnknownIdFormatInAdminDoesNotMatchTheIdPart(): void
    {
        // admin id must be digits with an optional slug, e.g. 12 or 12-some-slug
        $refUrl = new UrlScript('http://localhost/admin/vlastni-stranky/upravit-polozku-menu/abc', '/');
        $params = $this->router->match(new Request($refUrl));

        $this->assertNull($params);
    }
}
