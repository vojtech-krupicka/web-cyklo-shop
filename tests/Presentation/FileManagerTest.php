<?php declare(strict_types=1);

namespace Tests\Presentation;

use Nette\Application\Response;
use Nette\Application\Responses\RedirectResponse;
use Nette\Http\FileUpload;
use Tests\PresenterTestCase;

final class FileManagerTest extends PresenterTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAsAdmin();
    }

    private function images(): string
    {
        return $this->resourcesDir() . '/images';
    }

    private function files(): string
    {
        return $this->resourcesDir() . '/files';
    }

    /** @param array<string, mixed> $post */
    private function uploadFile(FileUpload $file, array $post = []): Response
    {
        return $this->submitForm('AdminModule:File', 'fileAdd', $post + ['name' => '', 'save' => 'Přidat'], ['action' => 'default'], ['file' => $file]);
    }

    private function tinyMceList(string $name): string
    {
        $path = self::container()->getByType(\App\Model\Settings\AppSettings::class)->tinyMceDir . '/' . $name;
        $this->assertFileExists($path, 'the TinyMCE list was generated in the sandbox');

        return (string) file_get_contents($path);
    }

    // region page

    public function testThePageRendersOnAFreshInstallWithoutAnyUploadFolders(): void
    {
        $this->assertDirectoryDoesNotExist($this->resourcesDir());

        $html = $this->renderPage($this->runPresenter('AdminModule:File', ['action' => 'default']));

        $this->assertStringContainsString('Nebyly nalezeny žádné soubory!', $html);
    }

    public function testUploadedFilesAreListedOnThePage(): void
    {
        $this->uploadFile($this->uploadImage('foto.png'));
        $this->uploadFile($this->upload('dokument.txt', 'text'));

        $html = $this->renderPage($this->runPresenter('AdminModule:File', ['action' => 'default']));

        $this->assertStringContainsString('foto.png', $html);
        $this->assertStringContainsString('dokument.txt', $html);
    }

    // region upload

    public function testAnImageGoesToTheImagesFolderAndIsAddedToTheTinyMceList(): void
    {
        $response = $this->uploadFile($this->uploadImage('foto.png'));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertFileExists($this->images() . '/foto.png');
        $this->assertStringContainsString('foto.png', $this->tinyMceList('tinymce.imagelist.js'));
    }

    public function testAnyOtherFileGoesToTheFilesFolderAndIsAddedToTheTinyMceList(): void
    {
        $this->uploadFile($this->upload('dokument.txt', 'text'));

        $this->assertFileExists($this->files() . '/dokument.txt');
        $this->assertFileDoesNotExist($this->images() . '/dokument.txt');
        $this->assertStringContainsString('dokument.txt', $this->tinyMceList('tinymce.filelist.js'));
    }

    public function testACustomNameReplacesTheOriginalOneAndKeepsTheExtension(): void
    {
        $this->uploadFile($this->uploadImage('IMG_0001.png'), ['name' => 'Můj Soubor']);

        $this->assertFileExists($this->images() . '/muj-soubor.png');
    }

    public function testASecondFileWithTheSameNameIsRenamedInsteadOfOverwritten(): void
    {
        $this->uploadFile($this->upload('dokument.txt', 'první'));
        $this->uploadFile($this->upload('dokument.txt', 'druhý'));
        $this->uploadFile($this->upload('dokument.txt', 'třetí'));

        $this->assertSame('první', file_get_contents($this->files() . '/dokument.txt'));
        $this->assertSame('druhý', file_get_contents($this->files() . '/dokument_0.txt'));
        $this->assertSame('třetí', file_get_contents($this->files() . '/dokument_1.txt'));
    }

    public function testTheOverwriteOptionReplacesTheExistingFile(): void
    {
        $this->uploadFile($this->upload('dokument.txt', 'stará verze'));

        $this->uploadFile($this->upload('dokument.txt', 'nová verze'), ['overwrite' => '1']);

        $this->assertSame('nová verze', file_get_contents($this->files() . '/dokument.txt'));
        $this->assertFileDoesNotExist($this->files() . '/dokument_0.txt');
    }

    public function testTheFormNeedsAFile(): void
    {
        $html = $this->renderPage($this->submitForm('AdminModule:File', 'fileAdd', ['name' => '', 'save' => 'Přidat'], ['action' => 'default']));

        $this->assertStringContainsString('Musíte vložit soubor, který chcete nahrát!', $html);
    }

    // region delete

    public function testDeletingAnImageRemovesTheFileAndUpdatesTheList(): void
    {
        $this->uploadFile($this->uploadImage('foto.png'));
        $this->uploadFile($this->uploadImage('druha.png'));

        $response = $this->signal('AdminModule:File', 'delete', ['fileName' => 'foto.png', 'img' => 1]);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertFileDoesNotExist($this->images() . '/foto.png');
        $this->assertFileExists($this->images() . '/druha.png');
        $list = $this->tinyMceList('tinymce.imagelist.js');
        $this->assertStringNotContainsString('foto.png', $list);
        $this->assertStringContainsString('druha.png', $list);
    }

    public function testDeletingAPlainFileUsesTheFilesFolder(): void
    {
        $this->uploadFile($this->upload('dokument.txt', 'text'));

        $this->signal('AdminModule:File', 'delete', ['fileName' => 'dokument.txt', 'img' => 0]);

        $this->assertFileDoesNotExist($this->files() . '/dokument.txt');
    }

    public function testDeletingAMissingFileChangesNothing(): void
    {
        $this->uploadFile($this->uploadImage('foto.png'));

        $response = $this->signal('AdminModule:File', 'delete', ['fileName' => 'neexistuje.png', 'img' => 1]);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertFileExists($this->images() . '/foto.png');
    }

    public function testDeleteCannotReachFilesOutsideTheUploadFolder(): void
    {
        $this->uploadFile($this->uploadImage('foto.png'));
        $outside = $this->resourcesDir() . '/tajne.txt';
        file_put_contents($outside, 'nesmí zmizet');

        $this->signal('AdminModule:File', 'delete', ['fileName' => '../tajne.txt', 'img' => 1]);

        $this->assertFileExists($outside, 'a path with ../ must not leave the images folder');
    }
}
