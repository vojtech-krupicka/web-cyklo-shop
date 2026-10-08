<?php declare(strict_types=1);

namespace Tests\Model;

use App\Model\Gallery\GalleryEntity;
use App\Model\Gallery\GalleryFacade;
use App\Model\Gallery\GalleryMediaEntity;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\DatabaseTestCase;

#[CoversClass(GalleryFacade::class)]
final class GalleryFacadeTest extends DatabaseTestCase
{
    private GalleryFacade $facade;

    protected function setUp(): void
    {
        parent::setUp();
        $this->facade = self::container()->getByType(GalleryFacade::class);
    }

    public function testGetGallery(): void
    {
        $gallery = $this->facade->getGallery(1);

        $this->assertNotNull($gallery);
        $this->assertSame('Obchod', $gallery->name);
        $this->assertTrue($gallery->active);
        $this->assertTrue($gallery->allowComments);
    }

    public function testGetGalleryWithNullOrUnknownIdReturnsNull(): void
    {
        $this->assertNull($this->facade->getGallery(null));
        $this->assertNull($this->facade->getGallery(9999));
    }

    public function testGalleriesAreOrderedByAddedDate(): void
    {
        $names = array_map(fn(GalleryEntity $g) => $g->name, $this->facade->getGalleries());

        $this->assertSame(['Obchod', 'Akce'], $names);
    }

    public function testInactiveGalleryIsHiddenUnlessRequested(): void
    {
        $gallery = $this->facade->getGallery(2);
        $this->assertNotNull($gallery);
        $gallery->active = false;
        $this->facade->persist($gallery);

        $this->assertNull($this->facade->getGallery(2));
        $this->assertNotNull($this->facade->getGallery(2, activeOnly: false));
        $this->assertCount(1, $this->facade->getGalleries());
        $this->assertCount(2, $this->facade->getGalleries(activeOnly: false));
    }

    public function testGetGalleryItemsInSortOrder(): void
    {
        $items = $this->facade->getGalleryItems(1);

        $this->assertSame(
            ['test1.jpg', 'test2.jpg', 'test3.jpg', 'test4.jpg'],
            array_map(fn(GalleryMediaEntity $m) => $m->filename, $items),
        );
        foreach ($items as $item) {
            $this->assertSame(1, $item->galleryId);
        }
    }

    public function testGalleryItemsLimitAndAllGalleries(): void
    {
        $this->assertCount(2, $this->facade->getGalleryItems(1, limit: 2));
        $this->assertCount(6, $this->facade->getGalleryItems());
    }

    public function testGetMedia(): void
    {
        $media = $this->facade->getMedia(1);

        $this->assertNotNull($media);
        $this->assertSame('Pohled na obchod', $media->title);
        $this->assertNull($this->facade->getMedia(null));
        $this->assertNull($this->facade->getMedia(9999));
    }

    public function testPersistNewGalleryAndMedia(): void
    {
        $gallery = new GalleryEntity(name: 'Nová galerie', active: true);
        $this->facade->persist($gallery);
        $this->assertNotNull($gallery->id);

        $media = new GalleryMediaEntity(galleryId: $gallery->id, filename: 'nova.jpg', title: 'Nový obrázek', active: true);
        $this->facade->persistMedia($media);
        $this->assertNotNull($media->id);

        $items = $this->facade->getGalleryItems($gallery->id);
        $this->assertCount(1, $items);
        $this->assertSame('nova.jpg', $items[0]->filename);
    }

    public function testPersistMediaUpdatesExistingRow(): void
    {
        $media = $this->facade->getMedia(1);
        $this->assertNotNull($media);
        $media->title = 'Nový název';

        $this->facade->persistMedia($media);

        $reloaded = $this->facade->getMedia(1);
        $this->assertNotNull($reloaded);
        $this->assertSame('Nový název', $reloaded->title);
    }

    public function testDeletingGalleryCascadesToItsItems(): void
    {
        $gallery = $this->facade->getGallery(1);
        $this->assertNotNull($gallery);
        $this->assertNotEmpty($this->facade->getGalleryItems(1, activeOnly: false));

        $this->facade->delete($gallery);

        $this->assertNull($this->facade->getGallery(1, activeOnly: false));
        $this->assertSame([], $this->facade->getGalleryItems(1, activeOnly: false));
    }

    public function testDeleteMediaRemovesOnlyThatItem(): void
    {
        $media = $this->facade->getMedia(1);
        $this->assertNotNull($media);

        $this->facade->deleteMedia($media);

        $this->assertNull($this->facade->getMedia(1));
        $this->assertCount(3, $this->facade->getGalleryItems(1));
    }
}
