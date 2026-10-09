<?php declare(strict_types=1);

namespace Tests\Presentation;

use Nette\Application\Response;
use Nette\Application\Responses\RedirectResponse;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Tests\PresenterTestCase;

final class GalleryFormsTest extends PresenterTestCase
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

    private function rows(string $table): int
    {
        return self::container()->getByType(Explorer::class)->table($table)->count('*');
    }

    private function galleryDir(int $id): string
    {
        return $this->resourcesDir() . '/galleries/gallery_' . $id;
    }

    /** @param array<string, mixed> $post */
    private function addGallery(array $post): Response
    {
        return $this->submitForm('AdminModule:Gallery', 'galleryAdd', $post + ['save' => 'Vytvořit'], ['action' => 'add']);
    }

    /** @param array<string, mixed> $post */
    private function editGallery(int $id, array $post): Response
    {
        return $this->submitForm('AdminModule:Gallery', 'galleryEdit', $post + ['save' => 'Uložit'], ['action' => 'edit', 'id' => $id]);
    }

    /** @param array<string, mixed> $post */
    private function addMedia(int $galleryId, array $post, ?\Nette\Http\FileUpload $file): Response
    {
        return $this->submitForm(
            'AdminModule:Gallery',
            'galleryMediaAdd',
            $post + ['save' => 'Přidat'],
            ['action' => 'edit', 'id' => $galleryId],
            $file ? ['media' => $file] : [],
        );
    }

    // region gallery add

    public function testAddingAGalleryCreatesTheRowAndItsFolder(): void
    {
        $count = $this->rows('galleries');

        $response = $this->addGallery(['name' => 'Nová galerie', 'description' => 'Popis', 'allowComments' => '0']);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($count + 1, $this->rows('galleries'));

        $row = self::container()->getByType(Explorer::class)->table('galleries')->where('name', 'Nová galerie')->fetch();
        $this->assertNotNull($row);
        $this->assertSame('Popis', $row['description']);
        $this->assertSame(0, $row['allow_comments']);
        $this->assertDirectoryExists($this->galleryDir((int) $row['id']));
        $this->assertStringContainsString((string) $row['id'], $response->getUrl(), 'redirects to the new gallery');
    }

    public function testAGalleryNeedsAName(): void
    {
        $count = $this->rows('galleries');

        $html = $this->renderPage($this->addGallery(['name' => '', 'description' => '', 'allowComments' => '1']));

        $this->assertStringContainsString('Vyplňte prosím název galerie!', $html);
        $this->assertSame($count, $this->rows('galleries'));
    }

    // region gallery edit

    public function testEditingAGallerySavesItsFields(): void
    {
        $response = $this->editGallery(2, ['name' => 'Přejmenovaná', 'description' => 'Nový popis', 'allowComments' => '1']);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $row = $this->row('galleries', 2);
        $this->assertSame('Přejmenovaná', $row['name']);
        $this->assertSame('Nový popis', $row['description']);
        $this->assertSame(1, $row['allow_comments']);
    }

    public function testEditingAGalleryRejectsAnEmptyName(): void
    {
        $html = $this->renderPage($this->editGallery(2, ['name' => '', 'description' => '', 'allowComments' => '0']));

        $this->assertStringContainsString('Vyplňte prosím název galerie!', $html);
        $this->assertSame('Akce', $this->row('galleries', 2)['name']);
    }

    // region media add

    public function testUploadingAnImageStoresTheFileItsThumbnailAndTheRow(): void
    {
        $count = $this->rows('gallery_items');

        $response = $this->addMedia(2, ['title' => 'Nový obrázek', 'description' => 'Popisek'], $this->uploadImage('foto.png'));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($count + 1, $this->rows('gallery_items'));
        $this->assertFileExists($this->galleryDir(2) . '/foto.png');
        $this->assertFileExists($this->galleryDir(2) . '/thumb_foto.png');

        $row = self::container()->getByType(Explorer::class)->table('gallery_items')->where('file_name', 'foto.png')->fetch();
        $this->assertNotNull($row);
        $this->assertSame(2, $row['gallery_id']);
        $this->assertSame('Nový obrázek', $row['title']);
        $this->assertSame(0, $row['active'], 'new images start inactive');
        $this->assertSame(2, $row['sort_order'], 'appended after the two existing items of gallery 2');
    }

    public function testUploadingTheSameNameTwiceRenamesTheSecondFile(): void
    {
        $this->addMedia(2, ['title' => 'První', 'description' => ''], $this->uploadImage('foto.png'));
        $this->addMedia(2, ['title' => 'Druhý', 'description' => ''], $this->uploadImage('foto.png'));

        $this->assertFileExists($this->galleryDir(2) . '/foto.png');
        $this->assertFileExists($this->galleryDir(2) . '/0_foto.png');
        $this->assertNotNull(
            self::container()->getByType(Explorer::class)->table('gallery_items')->where('file_name', '0_foto.png')->fetch(),
        );
    }

    public function testUploadingANonImageIsRefused(): void
    {
        $count = $this->rows('gallery_items');

        $response = $this->addMedia(2, ['title' => 'Text', 'description' => ''], $this->upload('poznamky.txt', 'to neni obrazek'));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($count, $this->rows('gallery_items'));
        $this->assertFileDoesNotExist($this->galleryDir(2) . '/poznamky.txt');
    }

    public function testMediaNeedsAFileAndATitle(): void
    {
        $count = $this->rows('gallery_items');

        $html = $this->renderPage($this->addMedia(2, ['title' => '', 'description' => ''], null));

        $this->assertStringContainsString('Vyplňte prosím název položky!', $html);
        $this->assertStringContainsString('Musíte vložit soubor s obrázkem!', $html);
        $this->assertSame($count, $this->rows('gallery_items'));
    }

    // region media edit (one form per image, created by a Multiplier)

    public function testEditingAnImageCaptionSavesTitleAndDescription(): void
    {
        $response = $this->submitForm('AdminModule:Gallery', 'galleryMediaEdit-1', [
            'title' => 'Nový titulek',
            'description' => 'Nový popisek',
            'id' => '1',
            'save' => 'Uložit',
        ], ['action' => 'edit', 'id' => 1]);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $row = $this->row('gallery_items', 1);
        $this->assertSame('Nový titulek', $row['title']);
        $this->assertSame('Nový popisek', $row['description']);
        $this->assertSame('test1.jpg', $row['file_name'], 'other fields are untouched');
    }

    public function testAnImageNeedsATitle(): void
    {
        $html = $this->renderPage($this->submitForm('AdminModule:Gallery', 'galleryMediaEdit-1', [
            'title' => '',
            'description' => '',
            'id' => '1',
            'save' => 'Uložit',
        ], ['action' => 'edit', 'id' => 1]));

        $this->assertStringContainsString('Vyplňte prosím název položky!', $html);
        $this->assertSame('Pohled na obchod', $this->row('gallery_items', 1)['title']);
    }
}
