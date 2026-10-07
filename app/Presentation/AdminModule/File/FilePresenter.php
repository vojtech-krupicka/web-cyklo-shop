<?php declare(strict_types=1);

namespace App\Presentation\AdminModule\File;

use App\Presentation\AdminModule;
use App\Model;
use Nette\Application\UI\Form;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;

class FileAddFormData
{
    public string $name;
    public bool $overwrite;
    public FileUpload $file;
}

class FileListItem
{
    public function __construct(
        public string $fileName,
        public string $extension,
        public string $name,
        public string $fullPath,
        public string $webPath,
        public int $fileSize,
        public \DateTime $modified
    ) {}
}

final class FilePresenter extends AdminModule\BaseSecuredPresenter
{
    const string DEFAULT_IMAGES_DIR = 'images';
    const string DEFAULT_FILES_DIR = 'files';

    public function __construct(
        private FileSystem $fileSystem,
        private Model\Settings\AppSettings $appSettings
    ) {}

    // #region Default

    public function renderDefault()
    {
        $this->template->pageHeading = 'Správce souborů';
        $this->addBreadcrumbItem('File', 'Správce souborů');

        $this->template->files = $this->generateFilesList(self::DEFAULT_FILES_DIR);
        $this->template->images = $this->generateFilesList(self::DEFAULT_IMAGES_DIR);
    }

    public function handleDelete(string $fileName, bool $img = true): void
    {
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
        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = 'table class="form"';
        $form->addGroup();

        // Add item name (text in menu)
        $form->addText('name', 'Nové jméno:');

        // File
        $form
            ->addUpload('file', '*Soubor:')
            ->setRequired('Musíte vložit soubor, který chcete nahrát!');

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

        $fileName = $values->file->getSanitizedName();
        $parts = explode('.', $fileName);
        $extension = array_pop($parts);
        $fileName = join('.', $parts);

        $newFileName = ($form->values->name) ? \Nette\Utils\Strings::webalize($form->values->name) : $fileName;
        $isImage = $values->file->isImage();
        $dirName = $this->getDirName($isImage ? self::DEFAULT_IMAGES_DIR : self::DEFAULT_FILES_DIR);

        if (!$form->values->overwrite) {
            $index = 0;
            if (file_exists($dirName . '/' . $newFileName . '.' . $extension)) {
                foreach (\Nette\Utils\Finder::findFiles('*')->in($dirName . '/') as $fn => $foo) {
                    $fn = substr($fn, strlen($dirName) + 1);

                    if (\Nette\Utils\Strings::match($fn, '#^' . $newFileName . '_[0-9]*.' . $extension . '#')) {
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

    public function getDirName(string $folder)
    {
        return $this->fileSystem->joinPaths($this->appSettings->resourcesDir, $folder);
    }

    private function generateFilesList(string $folder): array
    {
        $dirName = $this->getDirName($folder);
        $files = [];

        foreach (\Nette\Utils\Finder::findFiles('*')->in($dirName) as $fn => $file) {
            $files[] = new FileListItem(
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

    public function generateTinyMCEFileList(bool $img = true)
    {
        // You can't simply echo everything right away because we need to set some headers first!
        $listName = ($img) ? 'tinyMCEImageList' : 'tinyMCELinkList';
        $output = "var {$listName} = new Array(";
        $delim = "\n";

        // Since TinyMCE3.x you need absolute image paths in the list...
        $wwwPath = (($img) ? '/resources/images' : $this->template->baseUrl . '/resources/files');
        $outputName = ($img) ? 'tinymce.imagelist.js' : 'tinymce.filelist.js';
        $outputPath = $this->fileSystem->joinPaths($this->appSettings->wwwDir, 'assets/javascript');
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
