<?php declare(strict_types=1);

namespace App\Presentation\AdminModule\Gallery;

use App\Model\GalleryMediaEntity;
use App\Presentation\AdminModule;
use App\Model;
use Nette\Application\UI\Form;
use Nette\Forms\Rendering\DefaultFormRenderer;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;

class GalleryAddEditFormData
{
    public string $name;
    public string $description;
    public int $allowComments;
}

class GalleryMediaAddFormData
{
    public string $title;
    public string $description;
    public FileUpload $media;
}

class GalleryMediaEditFormData
{
    public string $id;
    public string $title;
    public string $description;
}

final class GalleryPresenter extends AdminModule\BaseSecuredPresenter
{
    const string DEFAULT_DIR = '/galleries/gallery_';
    const int DEFAULT_THUMB_WIDTH = 188;
    const int DEFAULT_THUMB_HEIGHT = 128;

    public ?int $id = null;

    private ?Model\Gallery\GalleryEntity $gallery = null;
    private array $galleryItems = [];

    public function __construct(
        private FileSystem $fileSystem,
        private Model\Gallery\GalleryFacade $galleryFacade,
        private Model\Settings\AppSettings $appSettings
    ) {}

    public function beforeRender(): void
    {
        parent::beforeRender();

        $this->addBreadcrumbItem('Gallery', 'Galerie');
    }

    // #region Default

    public function renderDefault()
    {
        $this->template->pageHeading = 'Galerie';

        $this->template->galleries = $this->galleryFacade->getGalleries(activeOnly: false, order: 'added ASC');
    }

    // #region Default signals

    public function handleActivate(int $galleryId, bool $flag): void
    {
        $gallery = $this->galleryFacade->getGallery($galleryId, false);
        if (!$gallery) {
            $this->flashMessage("Galerie s id #$galleryId nebyla nalezena.", 'error');
        } else {
            $gallery->active = $flag;
            $this->galleryFacade->persist($gallery);

            $this->flashMessage("Galerie #$galleryId - {$gallery->name} byla " . ($flag ? 'aktivována' : 'deaktivována') . '.', 'info');
        }

        if ($this->isAjax()) {
            $this->redrawControl('galleries');
        } else {
            $this->redirect('this');
        }
    }

    public function handleDelete(int $galleryId): void
    {
        $gallery = $this->galleryFacade->getGallery($galleryId, false);
        if (!$gallery) {
            $this->flashMessage("Galerie s id #$galleryId nebyla nalezena.", 'error');
        } else {
            $this->galleryFacade->delete($gallery);
            $this->flashMessage("Galerie #$galleryId - {$gallery->name}' byla úspěšně smazána.", 'info');
        }

        if ($this->isAjax()) {
            $this->redrawControl('galleries');
        } else {
            $this->redirect('this');
        }
    }

    // #region Gallery add

    public function renderAdd()
    {
        $this->template->pageHeading = 'Nová galerie';

        $this->addBreadcrumbItem('Gallery', 'Nová galerie', action: 'add');
    }

    protected function createComponentGalleryAdd(): Form
    {
        // New instance of nette form
        $form = new Form;
        $renderer = new DefaultFormRenderer;
        $renderer->wrappers['controls']['container'] = 'table class="form"';
        $form->setRenderer($renderer);

        $form->addGroup();

        // Add item name (text in menu)
        $form
            ->addText('name', '*Název galerie:')
            ->setRequired('Vyplňte prosím název galerie!')
            ->addRule(Form::MaxLength, 'Název galerie je příliš dlouhý, max. délka je %d znaků!', 64);

        // Add item title (text in title attribute)
        $form
            ->addTextArea('description', 'Popis galerie:', 80, 4)
            ->addRule(Form::MaxLength, 'Titulek položky je příliš dlouhý, max. délka je %d znaků!', 256);

        // Allow comments
        $form
            ->addRadioList('allowComments', 'Povolit komentáře?', array(1 => 'Ano', 0 => 'Ne'))
            ->setDefaultValue(1);

        // Add two buttons
        $form->addGroup();
        $form->addSubmit('save', 'Vytvořit');
        $form->addSubmit('cancel', 'Zrušit')->onClick[] = function () {
            $this->redirect('Gallery:default');
        };

        // Add callback
        $form->onSuccess[] = $this->galleryAddSubmitted(...);

        // Return new form instance
        return $form;
    }

    public function galleryAddSubmitted(Form $form, GalleryAddEditFormData $values): void
    {
        $gallery = new Model\Gallery\GalleryEntity();
        $gallery->name = $values->name;
        $gallery->description = $values->description;
        $gallery->allowComments = (bool) $values->allowComments;

        $this->galleryFacade->persist($gallery);

        $dirName = $this->getDirName() . $gallery->id;
        $this->fileSystem->createDir($dirName);

        $this->flashMessage("Galerie '{$gallery->name}' byla úspěšně vytvořena.", 'info');
        $this->redirect('Gallery:edit', array('id' => $gallery->id));
    }

    // #region Gallery edit

    public function actionEdit(int $id)
    {
        $this->gallery = $this->galleryFacade->getGallery($id, false);
        if (!$this->gallery) {
            $this->flashMessage('Nebylo zadáno platné ID galerie. Nelze editovat', 'error');
            $this->redirect('Gallery:default');
        }

        $this->galleryItems = $this->galleryFacade->getGalleryItems($this->gallery->id, false);
    }

    public function renderEdit(int $id)
    {
        $this->template->pageHeading = 'Editace galerie';
        $this->addBreadcrumbItem('Gallery', 'Editace galerie', action: 'edit', argName: 'id', argValue: $this->gallery->id);

        $this->template->gallery = $this->gallery;
        $this->template->galleryItems = $this->galleryItems;
        $this->template->wwwDir = $this->appSettings->wwwDir;
    }

    protected function createComponentGalleryEdit()
    {
        // New instance of nette form
        $form = new Form;
        $renderer = new DefaultFormRenderer;
        $renderer->wrappers['controls']['container'] = 'table class="form"';
        $form->setRenderer($renderer);

        $form->addGroup();

        // Add item name (text in menu)
        $form
            ->addText('name', '*Název galerie:')
            ->setRequired('Vyplňte prosím název galerie!')
            ->addRule(Form::MaxLength, 'Název galerie je příliš dlouhý, max. délka je %d znaků!', 64)
            ->setDefaultValue($this->gallery->name);

        // Add item title (text in title attribute)
        $form
            ->addTextArea('description', 'Popis galerie:', 80, 4)
            ->addRule(Form::MaxLength, 'Popise galerie je příliš dlouhý, max. délka je %d znaků!', 256)
            ->setDefaultValue($this->gallery->description);

        // Allow comments
        $form
            ->addRadioList('allowComments', 'Povolit komentáře?', array(1 => 'Ano', 0 => 'Ne'))
            ->setDefaultValue($this->gallery->allowComments ? 1 : 0);

        // Add two buttons
        $form->addGroup();
        $form->addSubmit('save', 'Uložit');
        $form->addSubmit('save_and_back', 'Uložit a pokračovat');
        $form->addSubmit('cancel', 'Zrušit')->onClick[] = function () {
            $this->redirect('Gallery:default');
        };

        // Add callback
        $form->onSuccess[] = $this->galleryEditSubmitted(...);

        // Return new form instance
        return $form;
    }

    public function galleryEditSubmitted(Form $form, GalleryAddEditFormData $values)
    {
        $this->gallery->name = $values->name;
        $this->gallery->description = $values->description;
        $this->gallery->allowComments = (bool) $values->allowComments;

        $this->galleryFacade->persist($this->gallery);

        if ($form->isSubmitted() === $form['save_and_back']) {
            $this->redirect('Gallery:default');
        } else {
            $this->redirect('Gallery:edit', ['id' => $this->gallery->id]);
        }
    }

    // #region Gallery Media add

    protected function createComponentGalleryMediaAdd(): Form
    {
        // New instance of nette form
        $form = new Form;
        $renderer = new DefaultFormRenderer;
        $renderer->wrappers['controls']['container'] = 'table class="form"';
        $form->setRenderer($renderer);

        $form->addGroup();

        // Add item name (text in menu)
        $form
            ->addText('title', '*Název položky:')
            ->setRequired('Vyplňte prosím název položky!')
            ->addRule(Form::MaxLength, 'Název položky je příliš dlouhý, max. délka je %d znaků!', 64);

        // Add item title (text in title attribute)
        $form
            ->addTextArea('description', 'Popisek položky:', 80, 1)
            ->addRule(Form::MaxLength, 'Popisek položky je příliš dlouhý, max. délka je %d znaků!', 256);

        // File
        $form
            ->addUpload('media', '*Obrázek:')
            ->setRequired('Musíte vložit soubor s obrázkem!');

        // Add two buttons
        $form->addGroup();
        $form->addSubmit('save', 'Přidat');
        $form->addSubmit('save_and_back', 'Přidat a pokračovat');
        $form->addSubmit('cancel', 'Zrušit')->onClick[] = function () {
            $this->redirect('Gallery:default');
        };

        // Add callback
        $form->onSuccess[] = $this->galleryMediaAddSubmitted(...);

        // Return new form instance
        return $form;
    }

    public function galleryMediaAddSubmitted(Form $form, GalleryMediaAddFormData $values): void
    {
        // Check if new image is OK
        if (!$values->media->isOk()) {
            $this->flashMessage("Při nahrávání obrázku '{$values->media->getUntrustedName()}' na server došlo k chybě (#{$values->media->error})!", 'error');
            $this->redirect('this');
        }
        if (!$values->media->isImage()) {
            $this->flashMessage("Nahraný soubor '{$values->media->getUntrustedName()}' není obrázek!", 'error');
            $this->redirect('this');
        }

        $dirName = $this->getDirName() . $this->gallery->id;
        $this->fileSystem->createDir($dirName);

        $index = 0;

        $fileName = $values->media->getSanitizedName();
        if (file_exists($dirName . '/' . $fileName)) {
            foreach (\Nette\Utils\Finder::findFiles('*')->in($dirName . '/') as $fn => $foo) {
                $fn = substr($fn, strlen($dirName) + 1);
                if (\Nette\Utils\Strings::match($fn, '#^[0-9]*_' . $fileName . '#')) {
                    $index++;
                }
            }

            $fileName = $index . '_' . $fileName;
            $this->flashMessage("Obrázek byl přejmenován na '" . $fileName . "' z důvodu konfliktu jmen!", 'warning');
        }

        $media = new Model\Gallery\GalleryMediaEntity(
            galleryId: $this->gallery->id,
            filename: $fileName,
            title: $values->title,
            description: $values->description,
            active: false,
            sortOrder: count($this->galleryItems),
            added: new \DateTime(),
        );

        $values->media->move($dirName . '/' . $fileName);
        $thumb = $values->media->toImage();
        $thumb->resize(self::DEFAULT_THUMB_WIDTH, self::DEFAULT_THUMB_HEIGHT, \Nette\Utils\Image::Stretch);

        $thumbPath = $this->fileSystem->joinPaths($dirName, '/thumb_' . $fileName);
        $thumb->save($thumbPath);

        $this->galleryFacade->persistMedia($media);

        if ($form->isSubmitted() === $form['save_and_back']) {
            $this->redirect('Gallery:default');
        } else {
            $this->redirect('Gallery:edit', ['id' => $this->gallery->id]);
        }
    }

    // #region Gallery media edit

    protected function createComponentGalleryMediaEdit(): \Nette\Application\UI\Multiplier
    {
        // Create multiplier
        $control = new \Nette\Application\UI\Multiplier(function ($id) {
            // New instance of nette form
            $form = new Form;
            $item = $this->galleryFacade->getMedia((int) $id, false);

            // Add item name (text in menu)
            $form
                ->addText('title', '')
                ->setRequired('Vyplňte prosím název položky!')
                ->addRule(\Nette\Application\UI\Form::MaxLength, 'Název položky je příliš dlouhý, max. délka je %d znaků!', 64)
                ->setDefaultValue($item->title);

            // Add item title (text in title attribute)
            $form
                ->addTextArea('description', '', 80, 1)
                ->addRule(\Nette\Application\UI\Form::MaxLength, 'Popisek položky je příliš dlouhý, max. délka je %d znaků!', 256)
                ->setDefaultValue($item->description);

            // File
            $form->addHidden('id', $id);

            // Add button
            $form->addSubmit('save', 'Uložit');

            // Add callback
            $form->onSuccess[] = $this->galleryMediaEditSubmitted(...);

            // Return new form instance
            return $form;
        });

        return $control;
    }

    public function galleryMediaEditSubmitted(Form $form, GalleryMediaEditFormData $values)
    {
        $media = $this->galleryFacade->getMedia((int) $values->id, false);
        if ($media) {
            $media->title = $values->title;
            $media->description = $values->description;
            $this->galleryFacade->persistMedia($media);

            $this->flashMessage("Popisek obrázku '#" . $values->id . ' - ' . $media->title . "' byl úspěšně upraven!", 'info');
        } else {
            $this->flashMessage("Obrázek s id '#" . $values->id . "' neexistuje!", 'error');
        }

        if ($this->isAjax()) {
            $this->redrawControl('galleryItem-' . $values->id);
        } else {
            $this->redirect('this');
        }

        return;
    }

    // #region Gallery media edit signals

    public function handleActivateMedia(int $mediaId, bool $flag): void
    {
        $media = $this->galleryFacade->getMedia($mediaId, false);

        if (!$media) {
            $this->flashMessage("Obrázek s id '#" . $mediaId . "' neexistuje!", 'error');
        } else {
            $media->active = $flag;
            $this->galleryFacade->persistMedia($media);

            if ($flag) {
                $this->flashMessage("Popisek obrázku '#" . $mediaId . ' - ' . $media->filename . "' byl úspěšně aktivován!", 'info');
            } else {
                $this->flashMessage("Popisek obrázku '#" . $mediaId . ' - ' . $media->filename . "' byl úspěšně deaktivován!", 'info');
            }
        }

        if ($this->isAjax()) {
            $this->redrawControl('galleryItems');
        } else {
            $this->redirect('this#snippet--galleryItem-' . $mediaId);
        }
    }

    public function handleDeleteMedia(int $mediaId): void
    {
        $media = $this->galleryFacade->getMedia($mediaId, false);

        if (!$media) {
            $this->flashMessage("Obrázek s id '#" . $mediaId . "' neexistuje!", 'error');
        } else {
            $index = 0;
            $allMedia = $this->galleryFacade->getGalleryItems($media->galleryId, false);
            foreach ($allMedia as $index => $m) {
                $m->sortOrder = $index;
                $this->galleryFacade->persistMedia($m);
                $index++;
            }

            $this->galleryFacade->deleteMedia($media);

            $dirName = $this->getDirName();
            $fileName = $media->filename;
            $this->fileSystem->delete($this->fileSystem->joinPaths($dirName, $fileName));
            $this->fileSystem->delete($this->fileSystem->joinPaths($dirName, 'thumb_' . $fileName));

            $this->flashMessage("Popisek obrázku '#" . $mediaId . ' - ' . $media->filename . "' byl úspěšně smazán!", 'info');
        }

        if ($this->isAjax()) {
            $this->redrawControl('galleryItems');
        } else {
            $this->redirect('this');
        }
    }

    public function handleMoveMedia(int $mediaId, bool $up): void
    {
        $media = $this->galleryFacade->getMedia($mediaId, false);

        if (!$media) {
            $this->flashMessage("Obrázek s id '#" . $mediaId . "' neexistuje!", 'error');
        } else {
            $siblingMedia = $this->galleryFacade->getGalleryItems($media->galleryId, false);
            $siblingKeys = array_keys($siblingMedia);
            $index = array_search($media->id, $siblingKeys, true);

            if ($index !== false) {
                $swapIndex = $up ? $index - 1 : $index + 1;
                $swapIndex = max(0, min($swapIndex, count($siblingMedia) - 1));
                if (isset($siblingMedia[$siblingKeys[$swapIndex]])) {
                    $swapItem = $siblingMedia[$siblingKeys[$swapIndex]];
                    $tempOrder = $media->sortOrder;
                    $media->sortOrder = $swapItem->sortOrder;
                    $swapItem->sortOrder = $tempOrder;
                    $this->galleryFacade->persistMedia($media);
                    $this->galleryFacade->persistMedia($swapItem);

                    $this->flashMessage("Obrázek '#" . $mediaId . ' - ' . $media->filename . "' byl úspěšně přesunut!", 'info');
                }
            } else {
                $this->flashMessage("Obrázek '#" . $mediaId . ' - ' . $media->filename . "' nelze přesunout!", 'error');
            }
        }

        if ($this->isAjax()) {
            $this->redrawControl('galleryItems');
        } else {
            $this->redirect('this');
        }
    }

    // #region Helpers

    public function getDirName()
    {
        return $this->fileSystem->joinPaths($this->appSettings->resourcesDir, self::DEFAULT_DIR);
    }
}
