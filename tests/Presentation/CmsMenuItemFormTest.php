<?php declare(strict_types=1);

namespace Tests\Presentation;

use Nette\Application\Responses\RedirectResponse;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Tests\PresenterTestCase;

final class CmsMenuItemFormTest extends PresenterTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAsAdmin();
    }

    private function menuItemByName(string $name): ?ActiveRow
    {
        return self::container()->getByType(Explorer::class)->table('menu_items')->where('name', $name)->fetch();
    }

    private function rows(string $table): int
    {
        return self::container()->getByType(Explorer::class)->table($table)->count('*');
    }

    /** @param array<string, mixed> $post */
    private function addItem(array $post, ?int $parentId = null): \Nette\Application\Response
    {
        return $this->submitForm('AdminModule:Cms', 'menuItemAdd', $post + ['save' => 'Vytvořit'], [
            'action' => 'itemAdd',
            'id' => $parentId,
        ]);
    }

    public function testAddingAnInternalItemCreatesTheMenuItemAndItsPage(): void
    {
        $items = $this->rows('menu_items');
        $pages = $this->rows('pages');

        $response = $this->addItem(['name' => 'Nová položka', 'title' => '', 'url' => '']);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($items + 1, $this->rows('menu_items'));
        $this->assertSame($pages + 1, $this->rows('pages'));

        $row = $this->menuItemByName('Nová položka');
        $this->assertNotNull($row);
        $this->assertSame('nova-polozka', $row['url']);
        $this->assertSame('Nová položka', $row['title'], 'empty title falls back to the name');
        $this->assertNotNull($row['page_id']);
    }

    public function testChildItemUrlIsPrefixedWithTheParentUrl(): void
    {
        $this->addItem(['name' => 'Podpoložka', 'title' => '', 'url' => ''], parentId: 2);

        $row = $this->menuItemByName('Podpoložka');
        $this->assertNotNull($row);
        $this->assertSame(2, $row['parent_id']);
        $this->assertSame('sortiment/podpolozka', $row['url']);
    }

    public function testAddingAnExternalItemKeepsTheUrlAndCreatesNoPage(): void
    {
        $pages = $this->rows('pages');

        $this->addItem(['name' => 'Externí odkaz', 'title' => '', 'url' => 'https://example.com', 'extern' => '1']);

        $row = $this->menuItemByName('Externí odkaz');
        $this->assertNotNull($row);
        $this->assertSame('https://example.com', $row['url']);
        $this->assertNull($row['page_id']);
        $this->assertSame($pages, $this->rows('pages'));
    }

    public function testMissingNameShowsAValidationErrorAndSavesNothing(): void
    {
        $items = $this->rows('menu_items');

        $html = $this->renderPage($this->addItem(['name' => '', 'title' => '', 'url' => '']));

        $this->assertStringContainsString('Vyplňte prosím popisek položky!', $html);
        $this->assertSame($items, $this->rows('menu_items'));
    }

    public function testInvalidExternalUrlIsRejected(): void
    {
        $items = $this->rows('menu_items');

        $html = $this->renderPage($this->addItem(['name' => 'Špatný odkaz', 'title' => '', 'url' => 'not a url', 'extern' => '1']));

        $this->assertStringContainsString('URL Adresa není ve správném formátu!', $html);
        $this->assertSame($items, $this->rows('menu_items'));
    }

    public function testCancelButtonRedirectsWithoutSaving(): void
    {
        $items = $this->rows('menu_items');

        $response = $this->submitForm('AdminModule:Cms', 'menuItemAdd', [
            'name' => 'Nevytvářet',
            'title' => '',
            'url' => '',
            'cancel' => 'Zrušit',
        ], ['action' => 'itemAdd']);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($items, $this->rows('menu_items'));
    }
}
