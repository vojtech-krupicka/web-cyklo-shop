<?php declare(strict_types=1);

namespace Tests\Model;

use App\Model\MenuItem\MenuItemEntity;
use App\Model\MenuItem\MenuItemFacade;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\DatabaseTestCase;

#[CoversClass(MenuItemFacade::class)]
final class MenuItemFacadeTest extends DatabaseTestCase
{
    private MenuItemFacade $facade;

    protected function setUp(): void
    {
        parent::setUp();
        $this->facade = self::container()->getByType(MenuItemFacade::class);
    }

    public function testFindsActiveItemByUrl(): void
    {
        $item = $this->facade->getMenuItemByUri('sortiment/nahradni-dily');

        $this->assertNotNull($item);
        $this->assertSame('Náhradní díly', $item->name);
        $this->assertSame(2, $item->parentId);
    }

    public function testUnknownUrlReturnsNull(): void
    {
        $this->assertNull($this->facade->getMenuItemByUri('neexistuje'));
    }

    public function testRootItemsAreSortedAndOnlyTopLevel(): void
    {
        $items = $this->facade->getMenuItems();

        $this->assertNotEmpty($items);
        $this->assertSame(['Novinky', 'Historie', 'Sortiment'], array_map(fn(MenuItemEntity $i) => $i->name, array_slice($items, 0, 3)));
        foreach ($items as $item) {
            $this->assertNull($item->parentId);
        }
    }

    public function testChildrenOfSortiment(): void
    {
        $names = array_map(fn(MenuItemEntity $i) => $i->name, $this->facade->getMenuItems(2));

        $this->assertSame(['Náhradní díly', 'Oblečení', 'Příslušenství', 'Minidrogerie'], $names);
    }

    public function testInactiveItemIsHiddenUnlessRequested(): void
    {
        $item = $this->facade->getMenuItemById(1);
        $this->assertNotNull($item);
        $item->active = false;
        $this->facade->persist($item);

        $this->assertNull($this->facade->getMenuItemById(1));
        $this->assertNotNull($this->facade->getMenuItemById(1, activeOnly: false));
    }

    public function testChangesAreRolledBackBetweenTests(): void
    {
        // testInactiveItemIsHiddenUnlessRequested deactivated item 1 inside its own transaction
        $item = $this->facade->getMenuItemById(1);

        $this->assertNotNull($item);
        $this->assertTrue($item->active);
    }
}
