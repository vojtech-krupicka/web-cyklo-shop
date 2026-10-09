<?php declare(strict_types=1);

namespace Tests\Presentation;

use App\Model\MenuItem\MenuItemEntity;
use App\Model\MenuItem\MenuItemFacade;
use Nette\Application\Response;
use Nette\Application\Responses\RedirectResponse;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Tests\PresenterTestCase;

final class CmsEditFormsTest extends PresenterTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAsAdmin();
    }

    private function row(string $table, int $id): ActiveRow
    {
        $row = self::container()->getByType(Explorer::class)->table($table)->get($id);
        $this->assertNotNull($row, "$table #$id exists");

        return $row;
    }

    /** @param array<string, mixed> $post */
    private function editItem(int $id, array $post): Response
    {
        return $this->submitForm('AdminModule:Cms', 'menuItemEdit', $post + ['save' => 'Uložit'], ['action' => 'itemEdit', 'id' => $id]);
    }

    /** @param array<string, mixed> $post */
    private function editPage(int $id, array $post): Response
    {
        return $this->submitForm('AdminModule:Cms', 'pageEdit', $post + ['save' => 'Uložit'], ['action' => 'pageEdit', 'id' => $id]);
    }

    // region menu item edit

    public function testRenamingAnItemKeepsItUnderItsParent(): void
    {
        $response = $this->editItem(3, ['name' => 'Díly', 'title' => '', 'url' => 'dily', 'parentId' => '2']);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $row = $this->row('menu_items', 3);
        $this->assertSame('Díly', $row['name']);
        $this->assertSame('Díly', $row['title'], 'empty title falls back to the name');
        $this->assertSame('dily', $row['url_rewrite_name']);
        $this->assertSame('sortiment/dily', $row['url']);
        $this->assertSame(2, $row['parent_id']);
    }

    public function testMovingAnItemToTheTopLevelDropsTheParentPrefix(): void
    {
        $this->editItem(3, ['name' => 'Náhradní díly', 'title' => '', 'url' => 'nahradni-dily', 'parentId' => '']);

        $row = $this->row('menu_items', 3);
        $this->assertNull($row['parent_id']);
        $this->assertSame('nahradni-dily', $row['url']);
    }

    public function testChangingAParentUrlUpdatesTheUrlsOfItsChildren(): void
    {
        $this->editItem(2, ['name' => 'Sortiment', 'title' => '', 'url' => 'zbozi', 'parentId' => '']);

        $this->assertSame('zbozi', $this->row('menu_items', 2)['url']);
        $this->assertSame('zbozi/nahradni-dily', $this->row('menu_items', 3)['url']);
        $this->assertSame('zbozi/obleceni', $this->row('menu_items', 4)['url']);
    }

    public function testEditingAnExternalItemChangesItsUrl(): void
    {
        $facade = self::container()->getByType(MenuItemFacade::class);
        $external = new MenuItemEntity(name: 'Externí', title: 'Externí', url: 'http://example.com', active: true);
        $facade->persist($external);
        $this->assertNotNull($external->id);

        $this->editItem($external->id, ['name' => 'Externí', 'title' => '', 'url' => 'https://example.org', 'parentId' => '']);

        $this->assertSame('https://example.org', $this->row('menu_items', $external->id)['url']);
    }

    public function testAnExternalAddressWithoutASchemeGetsHttps(): void
    {
        $facade = self::container()->getByType(MenuItemFacade::class);
        $external = new MenuItemEntity(name: 'Externí', title: 'Externí', url: 'http://example.com', active: true);
        $facade->persist($external);
        $this->assertNotNull($external->id);

        $this->editItem($external->id, ['name' => 'Externí', 'title' => '', 'url' => 'example.org', 'parentId' => '']);

        // Nette's Form::URL rule completes a bare address with https:// before the handler runs
        $this->assertSame('https://example.org', $this->row('menu_items', $external->id)['url']);
    }

    public function testEmptyNameIsRejected(): void
    {
        $html = $this->renderPage($this->editItem(3, ['name' => '', 'title' => '', 'url' => 'dily', 'parentId' => '2']));

        $this->assertStringContainsString('Vyplňte prosím popisek položky!', $html);
        $this->assertSame('Náhradní díly', $this->row('menu_items', 3)['name']);
    }

    public function testSaveAndContinueGoesBackToTheListAndSaveStaysOnTheItem(): void
    {
        $post = ['name' => 'Díly', 'title' => '', 'url' => 'dily', 'parentId' => '2'];

        $stay = $this->editItem(3, $post);
        $back = $this->editItem(3, $post + ['save_and_back' => 'Uložit a pokračovat'] + ['save' => null]);

        $this->assertInstanceOf(RedirectResponse::class, $stay);
        $this->assertInstanceOf(RedirectResponse::class, $back);
        $this->assertStringContainsString('upravit-polozku-menu', $stay->getUrl());
        $this->assertStringNotContainsString('upravit-polozku-menu', $back->getUrl());
    }

    public function testCancelLeavesTheItemUntouched(): void
    {
        $response = $this->submitForm('AdminModule:Cms', 'menuItemEdit', [
            'name' => 'Změněno',
            'title' => '',
            'url' => 'zmeneno',
            'parentId' => '2',
            'cancel' => 'Zrušit',
        ], ['action' => 'itemEdit', 'id' => 3]);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('Náhradní díly', $this->row('menu_items', 3)['name']);
    }

    // region page edit

    public function testEditingAPageSavesAllFields(): void
    {
        $response = $this->editPage(2, [
            'heading' => 'Nová historie',
            'seoTitle' => 'SEO titulek',
            'seoKeywords' => 'a, b',
            'seoDescription' => 'Popis stránky',
            'allowComments' => '1',
            'content' => '<p>Nový obsah</p>',
        ]);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $row = $this->row('pages', 2);
        $this->assertSame('Nová historie', $row['heading']);
        $this->assertSame('SEO titulek', $row['seo_title']);
        $this->assertSame('a, b', $row['seo_keywords']);
        $this->assertSame('Popis stránky', $row['seo_description']);
        $this->assertSame('<p>Nový obsah</p>', $row['content']);
        $this->assertSame(1, $row['allow_comments']);
        $this->assertNotNull($row['modified']);
    }

    public function testTheHomepageCanBeEditedWithoutAMenuItem(): void
    {
        $this->editPage(1, [
            'heading' => 'Nový úvod',
            'seoTitle' => '',
            'seoKeywords' => '',
            'seoDescription' => '',
            'allowComments' => '0',
            'content' => '<p>Vítejte</p>',
        ]);

        $this->assertSame('Nový úvod', $this->row('pages', 1)['heading']);
    }

    public function testAPageNeedsSomeContent(): void
    {
        $html = $this->renderPage($this->editPage(2, [
            'heading' => 'Historie',
            'seoTitle' => '',
            'seoKeywords' => '',
            'seoDescription' => '',
            'allowComments' => '0',
            'content' => '',
        ]));

        $this->assertStringContainsString('Musíte napsat nějaký obsah', $html);
    }

    public function testCancelLeavesThePageUntouched(): void
    {
        $this->submitForm('AdminModule:Cms', 'pageEdit', [
            'heading' => 'Změněno',
            'content' => 'x',
            'cancel' => 'Zrušit',
        ], ['action' => 'pageEdit', 'id' => 2]);

        $this->assertSame('Historie', $this->row('pages', 2)['heading']);
    }
}
