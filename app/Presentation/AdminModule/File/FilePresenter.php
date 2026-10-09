<?php declare(strict_types=1);

namespace App\Presentation\AdminModule\File;

use App\Presentation\AdminModule;
use App\Model;
use Nette\Application\UI\Form;
use Nette\Forms\Control;
use Nette\Forms\Rendering\DefaultFormRenderer;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;
use Nette\Utils\Strings;

final class FilePresenter extends AdminModule\BaseSecuredPresenter
{
    const string DEFAULT_IMAGES_DIR = 'images';
    const string DEFAULT_FILES_DIR = 'files';

    /**
     * Extensions accepted for files that are not images (images get their real extension from
     * their content). Everything else, above all scripts such as .php, is refused, because the
     * upload folder is below the web root.
     */
    const array ALLOWED_FILE_EXTENSIONS = ['pdf', 'txt', 'csv', 'rtf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'zip'];

    public function __construct(
        private FileSystem $fileSystem,
        private Model\Settings\AppSettings $appSettings
    ) {}

    // #region Default

    public function renderDefault(): void
    {
        $this->template->pageHeading = 'Správce souborů';
        $this->addBreadcrumbItem('File', 'Správce souborů');

        $this->template->files = $this->generateFilesList(self::DEFAULT_FILES_DIR);
        $this->template->images = $this->generateFilesList(self::DEFAULT_IMAGES_DIR);
    }

    public function handleDelete(string $fileName, bool $img = true): void
    {
        $fileName = basename($fileName);  // a name only, never a path
        $dirName = $this->getDirName($img ? self::DEFAULT_IMAGES_DIR : self::DEFAULT_FILES_DIR);
        $fullPath = $this->fileSystem->joinPaths($dirName, $fileName);
        if (is_file($fullPath)) {
            unlink($fullPath);
            $this->generateTinyMCEFileList($img);  // Update the TinyMCE file list after deletion
            $this->flashMessage("Soubor '$fileName' byl úspěšně smazán!", 'info');
        } else {
            $this->flashMessage("Soubor '$fileName' neexistuje!", 'error');
        }

        $this->redirect('this');
    }

    // #region File Add

    protected function createComponentFileAdd(): Form
    {
        // New instance of nette form
        $form = new Form;
        $renderer = new DefaultFormRenderer;
        $renderer->wrappers['controls']['container'] = 'table class="form"';
        $form->setRenderer($renderer);
        $form->addGroup();

        // Add item name (text in menu)
        $form->addText('name', 'Nové jméno:');

        // File
        $form
            ->addUpload('file', '*Soubor:')
            ->setRequired('Musíte vložit soubor, který chcete nahrát!')
            ->addRule(
                fn(Control $control): bool => !$control->getValue() instanceof FileUpload || self::isAllowedUpload($control->getValue()),
                'Tento typ souboru nelze nahrát. Povoleny jsou obrázky a soubory typu: ' . implode(', ', self::ALLOWED_FILE_EXTENSIONS) . '.',
            );

        // Allow owerride
        $form
            ->addCheckbox('overwrite', 'Povolit přepsání souborů se stejným názvem?')
            ->setDefaultValue(false);

        // Add two buttons
        $form->addGroup();
        $form->addSubmit('save', 'Přidat');

        // Add callback
        $form->onSuccess[] = $this->fileAddSubmitted(...);

        // Return new form instance
        return $form;
    }

    public function fileAddSubmitted(Form $form, FileAddFormData $values): void
    {
        if (!$values->file->isOk()) {
            $this->flashMessage("Při nahrávání souboru '{$values->file->getSanitizedName()}' na server došlo k chybě!", 'error');
            $this->redirect('this');
        }

        // The form rule already refuses these; checked again here because this is the security boundary
        if (!self::isAllowedUpload($values->file)) {
            $this->flashMessage("Soubor '{$values->file->getSanitizedName()}' tohoto typu nelze nahrát!", 'error');
            $this->redirect('this');
        }

        $fileName = $values->file->getSanitizedName();
        $parts = explode('.', $fileName);
        $extension = strtolower(array_pop($parts));
        // No dots in the stem: "shell.php.txt" must not keep a ".php" segment, because some hosts
        // run PHP for any name that merely contains it
        $fileName = Strings::webalize(join('.', $parts), '_', lower: false) ?: 'soubor';

        $newFileName = ($form->values->name) ? Strings::webalize($form->values->name) : $fileName;
        $isImage = $values->file->isImage();
        $dirName = $this->getDirName($isImage ? self::DEFAULT_IMAGES_DIR : self::DEFAULT_FILES_DIR);

        if (!$form->values->overwrite) {
            $index = 0;
            if (file_exists($dirName . '/' . $newFileName . '.' . $extension)) {
                foreach (\Nette\Utils\Finder::findFiles('*')->in($dirName . '/') as $fn => $foo) {
                    $fn = substr($fn, strlen($dirName) + 1);

                    if (Strings::match($fn, '#^' . $newFileName . '_[0-9]*.' . $extension . '#')) {
                        $index++;
                    }
                }

                $newFileName = $newFileName . '_' . $index;
                $this->flashMessage("Obrázek byl přejmenován na '{$newFileName}.{$extension}' z důvodu konfliktu jmen!", 'warning');
            }
        }

        $newFileName .= '.' . $extension;
        $values->file->move($this->fileSystem->joinPaths($dirName, $newFileName));
        $this->generateTinyMCEFileList($isImage);

        if ($isImage) {
            $this->flashMessage("Nový obrázek '{$newFileName}' byl úspěšně nahrán na server!", 'info');
        } else {
            $this->flashMessage("Nový soubor '{$newFileName}' byl úspěšně nahrán na server!", 'info');
        }

        $this->redirect('this');
    }

    // #region Helpers

    /**
     * Images are always stored with the extension of their real type; any other file must have an allowed extension.
     */
    public static function isAllowedUpload(FileUpload $file): bool
    {
        if (!$file->isOk() || $file->isImage()) {
            return true;
        }

        $extension = strtolower(pathinfo($file->getSanitizedName(), PATHINFO_EXTENSION));

        return in_array($extension, self::ALLOWED_FILE_EXTENSIONS, true);
    }

    public function getDirName(string $folder): string
    {
        return $this->fileSystem->joinPaths($this->appSettings->resourcesDir, $folder);
    }

    /**
     * @return list<Model\File\FileListItem>
     */
    private function generateFilesList(string $folder): array
    {
        $dirName = $this->getDirName($folder);
        $files = [];

        if (!is_dir($dirName)) {
            return $files;
        }

        foreach (\Nette\Utils\Finder::findFiles('*')->in($dirName) as $fn => $file) {
            $files[] = new Model\File\FileListItem(
                fileName: $file->getFilename(),
                extension: $file->getExtension(),
                name: $file->getFilename(),
                fullPath: $file->getPath(),
                webPath: $this->template->baseUrl . "/resources/{$folder}/" . $file->getFilename(),
                fileSize: $file->getSize(),
                modified: new \DateTime(date('Y-m-d h:i:s', $file->getMTime()))
            );
        }

        return $files;
    }

    public function generateTinyMCEFileList(bool $img = true): void
    {
        // You can't simply echo everything right away because we need to set some headers first!
        $listName = ($img) ? 'tinyMCEImageList' : 'tinyMCELinkList';
        $output = "var {$listName} = new Array(";
        $delim = "\n";

        // Since TinyMCE3.x you need absolute image paths in the list...
        $wwwPath = (($img) ? '/resources/images' : $this->template->baseUrl . '/resources/files');
        $outputName = ($img) ? 'tinymce.imagelist.js' : 'tinymce.filelist.js';
        $outputPath = $this->appSettings->tinyMceDir;
        $this->fileSystem->createDir($outputPath);
        $dir = $this->getDirName(($img) ? self::DEFAULT_IMAGES_DIR : self::DEFAULT_FILES_DIR);

        $fileList = [];
        foreach (\Nette\Utils\Finder::findFiles('*')->in($dir . '/') as $fn => $file) {
            if (is_file("$fn")) {
                $fileName = $file->getFilename();
                $fileList[] = "{$delim}\t[\"{$fileName}\", \"{$wwwPath}/{$fileName}\"]";
            }
        }

        $output .= implode(',', $fileList) . "{$delim});";

        file_put_contents($this->fileSystem->joinPaths($outputPath, $outputName), $output);
        return;
    }
}
