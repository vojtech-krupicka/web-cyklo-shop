<?php declare(strict_types=1);

namespace Tests\Presentation;

use Nette\Application\Response;
use Nette\Application\Responses\RedirectResponse;
use Nette\Database\Explorer;
use Tests\PresenterTestCase;

final class GallerySignalsTest extends PresenterTestCase
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

    /** @return array<int, int> image id => sort order, in display order */
    private function order(int $galleryId): array
    {
        $result = [];
        foreach ($this->db()->table('gallery_items')->where('gallery_id', $galleryId)->order('sort_order ASC') as $row) {
            $result[(int) $row['id']] = (int) $row['sort_order'];
        }

        return $result;
    }

    /** @param array<string, mixed> $params */
    private function gallery(string $signal, array $params = [], int $id = 1): Response
    {
        return $this->signal('AdminModule:Gallery', $signal, $params + ['id' => $id], 'edit');
    }

    // region galleries

    public function testGalleryCanBeDeactivatedAndActivatedAgain(): void
    {
        $response = $this->signal('AdminModule:Gallery', 'activate', ['galleryId' => 2, 'flag' => 0]);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(0, $this->db()->table('galleries')->get(2)['active'] ?? null);

        $this->signal('AdminModule:Gallery', 'activate', ['galleryId' => 2, 'flag' => 1]);
        $this->assertSame(1, $this->db()->table('galleries')->get(2)['active'] ?? null);
    }

    public function testDeletingAGalleryRemovesItsImages(): void
    {
        $this->signal('AdminModule:Gallery', 'delete', ['galleryId' => 1]);

        $this->assertNull($this->db()->table('galleries')->get(1));
        $this->assertSame([], $this->order(1));
        $this->assertNotNull($this->db()->table('galleries')->get(2), 'other galleries are untouched');
    }

    public function testDeletingAnUnknownGalleryChangesNothing(): void
    {
        $this->signal('AdminModule:Gallery', 'delete', ['galleryId' => 9999]);

        $this->assertSame(2, $this->db()->table('galleries')->count('*'));
    }

    // region images

    public function testImageCanBeActivatedAndDeactivated(): void
    {
        $this->gallery('activateMedia', ['mediaId' => 1, 'flag' => 0]);
        $this->assertSame(0, $this->db()->table('gallery_items')->get(1)['active'] ?? null);

        $this->gallery('activateMedia', ['mediaId' => 1, 'flag' => 1]);
        $this->assertSame(1, $this->db()->table('gallery_items')->get(1)['active'] ?? null);
    }

    public function testMovingAnImageUpSwapsItWithThePreviousOne(): void
    {
        // gallery 1: 1 (0), 2 (1), 3 (2), 4 (3)
        $this->gallery('moveMedia', ['mediaId' => 3, 'up' => 1]);

        $this->assertSame([1 => 0, 3 => 1, 2 => 2, 4 => 3], $this->order(1));
    }

    public function testMovingAnImageDownSwapsItWithTheNextOne(): void
    {
        $this->gallery('moveMedia', ['mediaId' => 2, 'up' => 0]);

        $this->assertSame([1 => 0, 3 => 1, 2 => 2, 4 => 3], $this->order(1));
    }

    public function testTheFirstImageCannotMoveUpAndTheLastCannotMoveDown(): void
    {
        $this->gallery('moveMedia', ['mediaId' => 1, 'up' => 1]);
        $this->gallery('moveMedia', ['mediaId' => 4, 'up' => 0]);

        $this->assertSame([1 => 0, 2 => 1, 3 => 2, 4 => 3], $this->order(1));
    }

    public function testDeletingAnImageKeepsTheRemainingOrderContinuous(): void
    {
        $this->gallery('deleteMedia', ['mediaId' => 2]);

        $this->assertSame([1 => 0, 3 => 1, 4 => 2], $this->order(1));
    }

    public function testDeletingAnImageRemovesItsFileAndThumbnail(): void
    {
        $this->submitForm(
            'AdminModule:Gallery',
            'galleryMediaAdd',
            ['title' => 'Mazaný', 'description' => '', 'save' => 'Přidat'],
            ['action' => 'edit', 'id' => 2],
            ['media' => $this->uploadImage('mazany.png')],
        );
        $dir = $this->resourcesDir() . '/galleries/gallery_2';
        $this->assertFileExists("$dir/mazany.png");
        $row = $this->db()->table('gallery_items')->where('file_name', 'mazany.png')->fetch();
        $this->assertNotNull($row);

        $this->gallery('deleteMedia', ['mediaId' => (int) $row['id']], id: 2);

        $this->assertNull($this->db()->table('gallery_items')->get($row['id']));
        $this->assertFileDoesNotExist("$dir/mazany.png");
        $this->assertFileDoesNotExist("$dir/thumb_mazany.png");
    }

    public function testUnknownImagesAreIgnored(): void
    {
        foreach (['activateMedia' => ['flag' => 1], 'moveMedia' => ['up' => 1], 'deleteMedia' => []] as $signal => $extra) {
            $response = $this->gallery($signal, ['mediaId' => 9999] + $extra);
            $this->assertInstanceOf(RedirectResponse::class, $response, $signal);
        }

        $this->assertSame([1 => 0, 2 => 1, 3 => 2, 4 => 3], $this->order(1));
    }
}
