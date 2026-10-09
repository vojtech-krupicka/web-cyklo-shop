<?php declare(strict_types=1);

namespace Tests\Presentation;

use Nette\Application\Response;
use Nette\Application\Responses\RedirectResponse;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Tests\PresenterTestCase;

final class CmsSignalsTest extends PresenterTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAsAdmin();
    }

    private function db(): Explorer
    {
        return self::container()->getByType(Explorer::class);
    }

    private function item(int $id): ?ActiveRow
    {
        return $this->db()->table('menu_items')->get($id);
    }

    /** @return array<int, int> menu item id => sort order, for the children of a parent, in display order */
    private function order(?int $parentId): array
    {
        $query = $this->db()->table('menu_items')->order('sort_order ASC');
        $parentId === null ? $query->where('parent_id IS NULL') : $query->where('parent_id', $parentId);

        $result = [];
        foreach ($query as $row) {
            $result[(int) $row['id']] = (int) $row['sort_order'];
        }

        return $result;
    }

    /** @param array<string, mixed> $params */
    private function cms(string $signal, array $params = []): Response
    {
        return $this->signal('AdminModule:Cms', $signal, $params);
    }

    // region activate

    public function testItemCanBeDeactivatedAndActivatedAgain(): void
    {
        $response = $this->cms('activate', ['itemId' => 1, 'flag' => 0]);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(0, ($this->item(1)['active'] ?? null));

        $this->cms('activate', ['itemId' => 1, 'flag' => 1]);
        $this->assertSame(1, ($this->item(1)['active'] ?? null));
    }

    public function testActivatingAnUnknownItemChangesNothing(): void
    {
        $before = $this->order(null);

        $response = $this->cms('activate', ['itemId' => 9999, 'flag' => 0]);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($before, $this->order(null));
    }

    // region delete

    public function testDeletingAnItemRemovesItsPageAndClosesTheGapInTheOrder(): void
    {
        // children of "Sortiment": 3 (0), 4 (1), 6 (2), 5 (3)
        $this->assertSame([3 => 0, 4 => 1, 6 => 2, 5 => 3], $this->order(2));

        $response = $this->cms('delete', ['itemId' => 4]);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertNull($this->item(4));
        $this->assertNull($this->db()->table('pages')->get(5), 'the page of the item is deleted too');
        $this->assertSame([3 => 0, 6 => 1, 5 => 2], $this->order(2));
    }

    public function testDeletingAParentRemovesItsChildren(): void
    {
        $this->cms('delete', ['itemId' => 2]);

        $this->assertNull($this->item(2));
        foreach ([3, 4, 5, 6] as $childId) {
            $this->assertNull($this->item($childId), "child #$childId is gone");
        }
    }

    public function testAnInactiveItemCanBeDeleted(): void
    {
        $this->cms('activate', ['itemId' => 4, 'flag' => 0]);

        $this->cms('delete', ['itemId' => 4]);

        $this->assertNull($this->item(4), 'inactive items are listed in the admin, so they must be deletable');
    }

    public function testDeletingAnUnknownItemChangesNothing(): void
    {
        $before = $this->order(2);

        $this->cms('delete', ['itemId' => 9999]);

        $this->assertSame($before, $this->order(2));
    }

    // region move

    public function testMovingAnItemUpSwapsItWithThePreviousSibling(): void
    {
        $this->cms('move', ['itemId' => 6, 'up' => 1]);

        $this->assertSame([3 => 0, 6 => 1, 4 => 2, 5 => 3], $this->order(2));
    }

    public function testMovingAnItemDownSwapsItWithTheNextSibling(): void
    {
        $this->cms('move', ['itemId' => 4, 'up' => 0]);

        $this->assertSame([3 => 0, 6 => 1, 4 => 2, 5 => 3], $this->order(2));
    }

    public function testAnInactiveItemCanBeMoved(): void
    {
        $this->cms('activate', ['itemId' => 6, 'flag' => 0]);

        $this->cms('move', ['itemId' => 6, 'up' => 1]);

        $this->assertSame([3 => 0, 6 => 1, 4 => 2, 5 => 3], $this->order(2));
    }

    public function testAnItemCanMovePastAnInactiveNeighbour(): void
    {
        $this->cms('activate', ['itemId' => 4, 'flag' => 0]);

        $this->cms('move', ['itemId' => 6, 'up' => 1]);

        $this->assertSame([3 => 0, 6 => 1, 4 => 2, 5 => 3], $this->order(2));
    }

    public function testTheFirstItemCannotMoveUpAndTheLastCannotMoveDown(): void
    {
        $this->cms('move', ['itemId' => 3, 'up' => 1]);
        $this->cms('move', ['itemId' => 5, 'up' => 0]);

        $this->assertSame([3 => 0, 4 => 1, 6 => 2, 5 => 3], $this->order(2));
    }

    public function testMovingWorksForTopLevelItemsWhoseIdsDifferFromTheirPosition(): void
    {
        // top level in display order: 11 (0), 1 (1), 2 (2), 7 (3), 8 (4), 9 (5), 10 (6)
        $this->assertSame([11 => 0, 1 => 1, 2 => 2, 7 => 3, 8 => 4, 9 => 5, 10 => 6], $this->order(null));

        $this->cms('move', ['itemId' => 7, 'up' => 1]);

        $this->assertSame([11 => 0, 1 => 1, 7 => 2, 2 => 3, 8 => 4, 9 => 5, 10 => 6], $this->order(null));
    }

    // region homepage

    public function testCreatingTheHomepageAddsOne(): void
    {
        $this->db()->table('pages')->where('is_homepage', 1)->delete();
        $this->assertSame(0, $this->db()->table('pages')->where('is_homepage', 1)->count('*'));

        $response = $this->cms('createHomepage');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(1, $this->db()->table('pages')->where('is_homepage', 1)->count('*'));
    }
}
