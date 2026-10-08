<?php declare(strict_types=1);

namespace Tests\Presentation;

use Nette\Application\Response;
use Nette\Application\Responses\RedirectResponse;
use Nette\Http\FileUpload;
use PHPUnit\Framework\Attributes\DataProvider;
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

    /** @return list<string> names of all files stored anywhere below the upload sandbox */
    private function storedFileNames(): array
    {
        if (!is_dir($this->resourcesDir())) {
            return [];
        }

        $names = [];
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->resourcesDir(), \FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            $names[] = $file->getFilename();
        }

        return $names;
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

    // region upload: scripts must never be stored (regression: unrestricted file upload / RCE)

    /** @return iterable<string, array{string}> */
    public static function provideExecutableNames(): iterable
    {
        foreach (['php', 'phtml', 'phar', 'pht', 'php5', 'php7', 'php8', 'PHP', 'pHp'] as $extension) {
            yield ".$extension" => ["shell.$extension"];
        }
    }

    #[DataProvider('provideExecutableNames')]
    public function testScriptsAreRefusedBecauseTheyWouldRunOnTheServer(string $name): void
    {
        $this->uploadFile($this->upload($name, '<?php echo "pwned";'));

        $stored = array_filter(
            $this->storedFileNames(),
            fn(string $file) => preg_match('#\.(ph(p\d?|tml|ar|t)|phps)$#i', $file) === 1,
        );
        $this->assertSame([], array_values($stored), 'no executable file may end up in the web root');
    }

    public function testACustomNameCannotSmuggleInAScriptExtension(): void
    {
        $this->uploadFile($this->upload('harmless.txt', '<?php echo "pwned";'), ['name' => 'shell.php']);

        $this->assertSame([], preg_grep('#\.ph(p\d?|tml|ar|t)$#i', $this->storedFileNames()) ?: []);
    }

    public function testAnImageNamedAsAScriptIsStoredAsAnImage(): void
    {
        $this->uploadFile($this->uploadImage('photo.php'));

        $this->assertFileExists($this->images() . '/photo.png', 'the extension comes from the real image type');
        $this->assertFileDoesNotExist($this->images() . '/photo.php');
        $this->assertFileDoesNotExist($this->files() . '/photo.php');
    }

    public function testOrdinaryDocumentsAreStillAccepted(): void
    {
        foreach (['dokument.pdf', 'poznamky.txt', 'archiv.zip', 'text.docx'] as $name) {
            $this->uploadFile($this->upload($name, 'content of ' . $name));

            $this->assertFileExists($this->files() . '/' . $name, $name);
        }
    }
}
