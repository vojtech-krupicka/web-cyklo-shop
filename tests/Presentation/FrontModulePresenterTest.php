<?php declare(strict_types=1);

namespace Tests\Presentation;

use Nette\Application\BadRequestException;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\PresenterTestCase;

final class FrontModulePresenterTest extends PresenterTestCase
{
    public function testHomepageShowsTheHomepageHeading(): void
    {
        $response = $this->runPresenter('FrontModule:Homepage', ['action' => 'default']);

        $this->assertInstanceOf(TextResponse::class, $response);
        $this->assertSame('Vítejte v testovacím cyklo-shopu', $response->getSource()->pageHeading);
    }

    public function testKnownCmsPageRendersItsHeading(): void
    {
        $response = $this->runPresenter('FrontModule:Cms', ['action' => 'default', 'uri' => 'sortiment/nahradni-dily']);

        $this->assertInstanceOf(TextResponse::class, $response);
        $this->assertSame('Náhradní díly', $response->getSource()->pageHeading);
    }

    public function testUnknownCmsPageIsNotFound(): void
    {
        $this->expectException(BadRequestException::class);

        $this->runPresenter('FrontModule:Cms', ['action' => 'default', 'uri' => 'neexistuje']);
    }

    public function testUnknownGalleryRedirectsToTheGalleryList(): void
    {
        $response = $this->runPresenter('FrontModule:Gallery', ['action' => 'detail', 'id' => 9999]);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('galerie', $response->getUrl());
    }

    public function testGalleryDetailListsOnlyItsOwnActiveItems(): void
    {
        $response = $this->runPresenter('FrontModule:Gallery', ['action' => 'detail', 'id' => 1]);

        $this->assertInstanceOf(TextResponse::class, $response);
        $this->assertSame('Obchod', $response->getSource()->pageHeading);
        $this->assertCount(4, $response->getSource()->galleryItems);
    }

    public function testLayoutUsesNajaAsTheOnlyAjaxLibrary(): void
    {
        $html = $this->renderPage($this->runPresenter('FrontModule:Homepage', ['action' => 'default']));

        $this->assertStringContainsString('Naja.min.js', $html);
        $this->assertStringContainsString('javascript/app.js', $html);
        foreach (['jquery.nette.js', 'jquery.ajaxform.js', 'jquery.livequery.js'] as $legacy) {
            $this->assertStringNotContainsString($legacy, $html);
        }
    }

    /**
     * Renders the complete page, which catches template and component errors.
     *
     * @param array<string, mixed> $params
     */
    #[DataProvider('providePages')]
    public function testPageRendersCompletely(string $presenter, array $params, string $expectedText): void
    {
        $html = $this->renderPage($this->runPresenter($presenter, $params));

        $this->assertStringContainsString($expectedText, $html);
        $this->assertStringContainsString('Historie', $html, 'main menu is rendered');
    }

    /** @return iterable<string, array{string, array<string, mixed>, string}> */
    public static function providePages(): iterable
    {
        yield 'homepage'     => ['FrontModule:Homepage', ['action' => 'default'], 'Vítejte'];
        yield 'cms page'     => ['FrontModule:Cms', ['action' => 'default', 'uri' => 'historie'], 'Lorem ipsum'];
        yield 'contact'      => ['FrontModule:Contact', ['action' => 'default'], 'Otevírací doba'];
        yield 'gallery list' => ['FrontModule:Gallery', ['action' => 'default'], 'Obchod'];
        yield 'sitemap'      => ['FrontModule:Sitemap', ['action' => 'default'], 'Mapa stránek'];
    }
}
