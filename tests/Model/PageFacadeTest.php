<?php declare(strict_types=1);

namespace Tests\Model;

use App\Model\MenuItem\MenuItemFacade;
use App\Model\Page\PageEntity;
use App\Model\Page\PageFacade;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\DatabaseTestCase;

#[CoversClass(PageFacade::class)]
final class PageFacadeTest extends DatabaseTestCase
{
    private PageFacade $facade;

    protected function setUp(): void
    {
        parent::setUp();
        $this->facade = self::container()->getByType(PageFacade::class);
    }

    public function testGetPageById(): void
    {
        $page = $this->facade->getPageById(2);

        $this->assertNotNull($page);
        $this->assertSame('Historie', $page->heading);
        $this->assertFalse($page->isHomepage);
        $this->assertInstanceOf(\DateTime::class, $page->created);
    }

    public function testUnknownPageReturnsNull(): void
    {
        $this->assertNull($this->facade->getPageById(9999));
    }

    public function testGetHomepage(): void
    {
        $homepage = $this->facade->getHomepage();

        $this->assertNotNull($homepage);
        $this->assertSame(1, $homepage->id);
        $this->assertTrue($homepage->isHomepage);
    }

    public function testPersistInsertsNewPageAndAssignsId(): void
    {
        $page = new PageEntity(heading: 'Nová stránka', seoTitle: 'Nová', content: '<p>Obsah</p>');

        $this->facade->persist($page);

        $this->assertNotNull($page->id);
        $loaded = $this->facade->getPageById($page->id);
        $this->assertNotNull($loaded);
        $this->assertSame('Nová stránka', $loaded->heading);
        $this->assertSame('<p>Obsah</p>', $loaded->content);
    }

    public function testPersistUpdatesExistingPage(): void
    {
        $page = $this->facade->getPageById(2);
        $this->assertNotNull($page);
        $page->heading = 'Změněná historie';

        $this->facade->persist($page);

        $reloaded = $this->facade->getPageById(2);
        $this->assertNotNull($reloaded);
        $this->assertSame('Změněná historie', $reloaded->heading);
    }

    public function testDeletingPageCascadesToItsMenuItem(): void
    {
        $menuItems = self::container()->getByType(MenuItemFacade::class);
        $this->assertNotNull($menuItems->getMenuItemByPageId(4), 'fixture: page 4 has a menu item');

        $page = $this->facade->getPageById(4);
        $this->assertNotNull($page);
        $this->facade->delete($page);

        $this->assertNull($this->facade->getPageById(4));
        $this->assertNull($menuItems->getMenuItemByPageId(4, activeOnly: false));
    }

    public function testCreateHomepageWhenNoneExists(): void
    {
        $existing = $this->facade->getHomepage();
        $this->assertNotNull($existing);
        $this->facade->delete($existing);
        $this->assertNull($this->facade->getHomepage());

        $created = $this->facade->createHomepage();

        $this->assertNotNull($created);
        $this->assertTrue($created->isHomepage);
        $this->assertSame('Homepage', $created->heading);
    }
}
