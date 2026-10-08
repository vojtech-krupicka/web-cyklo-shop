<?php declare(strict_types=1);

namespace App\Presentation\AdminModule\Cms;

use App\Presentation\AdminModule;
use App\Model;
use Nette\Application\UI\Form;
use Nette\Forms\Rendering\DefaultFormRenderer;
use Nette\Utils\DateTime;

final class CmsPresenter extends AdminModule\BaseSecuredPresenter
{
    public ?int $id = null;
    private ?Model\MenuItem\MenuItemEntity $parentItem = null;
    private Model\MenuItem\MenuItemEntity $menuItem;
    private Model\Page\PageEntity $page;
    private ?Model\MenuItem\MenuItemEntity $pageMenuItem = null;

    public function __construct(
        private Model\Page\PageFacade $pagesFacade,
        private Model\MenuItem\MenuItemFacade $menuItemsFacade,
    ) {}

    public function beforeRender(): void
    {
        parent::beforeRender();

        $this->addBreadcrumbItem('Cms', 'Vlastní stránky');
    }

    /**
     * @return list<Model\MenuItem\MenuItemEntity>
     */
    public function getMenuItems(?int $parentId = null, string $order = 'ASC'): array
    {
        return $this->menuItemsFacade->getMenuItems($parentId, false, $order);
    }

    // #region Default

    public function renderDefault(): void
    {
        $this->template->pageHeading = 'Vlastní stránky';

        $this->template->homepage = $this->pagesFacade->getHomepage();
        $this->template->menuItems = $this->getMenuItems();
    }

    // #region Default signals

    public function handleActivate(int $itemId, bool $flag = false): void
    {
        $item = $this->menuItemsFacade->getMenuItemById($itemId, false);
        if (!$item) {
            $this->flashMessage("Položka menu s id #$itemId nebyla nalezena.", 'error');
        } else {
            $item->active = $flag;
            $this->menuItemsFacade->persist($item);
            $this->flashMessage("Položka menu #$itemId - {$item->name}' byla úspěšně " . ($flag ? 'aktivována' : 'deaktivována') . '.', 'info');
        }

        if ($this->isAjax()) {
            $this->redrawControl('menuItems');
        } else {
            $this->redirect('this');
        }
    }

    public function handleCreateHomepage(): void
    {
        $this->pagesFacade->createHomepage();
        $this->flashMessage('Nová uvodní stránka byla úspěšně vytvořena.', 'info');
        $this->redirect('this');
    }

    public function handleDelete(int $itemId): void
    {
        $item = $this->menuItemsFacade->getMenuItemById($itemId);
        if (!$item) {
            $this->flashMessage("Položka menu s id #$itemId nebyla nalezena.", 'error');
        } else {
            $items = $this->menuItemsFacade->getMenuItems($item->parentId, false, 'ASC');
            $items = array_filter($items, fn($i) => $i->sortOrder > $item->sortOrder);
            foreach ($items as $i) {
                $i->sortOrder--;
                $this->menuItemsFacade->persist($i);
            }

            if ($item->pageId != null) {
                $page = $this->pagesFacade->getPageById($item->pageId);
                if ($page) {
                    $this->pagesFacade->delete($page);
                }
            }

            $this->menuItemsFacade->delete($item);
            $this->flashMessage("Položka menu #$itemId - {$item->name}' byla úspěšně smazána.", 'info');
        }

        if ($this->isAjax()) {
            $this->redrawControl('menuItems');
        } else {
            $this->redirect('this');
        }
    }

    public function handleMove(int $itemId, bool $up = false): void
    {
        $item = $this->menuItemsFacade->getMenuItemById($itemId);
        if (!$item) {
            $this->flashMessage("Položka menu s id #$itemId nebyla nalezena.", 'error');
        } else {
            $siblingItems = $this->menuItemsFacade->getMenuItems($item->parentId, false, 'ASC');
            $siblingKeys = array_keys($siblingItems);
            $index = array_search($item->id, $siblingKeys, true);

            if ($index !== false) {
                $swapIndex = $up ? $index - 1 : $index + 1;
                $swapIndex = max(0, min($swapIndex, count($siblingItems) - 1));
                if (isset($siblingItems[$siblingKeys[$swapIndex]])) {
                    $swapItem = $siblingItems[$siblingKeys[$swapIndex]];
                    $tempOrder = $item->sortOrder;
                    $item->sortOrder = $swapItem->sortOrder;
                    $swapItem->sortOrder = $tempOrder;
                    $this->menuItemsFacade->persist($item);
                    $this->menuItemsFacade->persist($swapItem);
                }
            }
        }

        if ($this->isAjax()) {
            $this->redrawControl('menuItems');
        } else {
            $this->redirect('this');
        }
    }

    // #region AddMenuItem action

    public function actionItemAdd(?int $id = null): void
    {
        $this->id = $id;
        $this->parentItem = $id ? $this->menuItemsFacade->getMenuItemById($this->id, false) : null;
    }

    public function renderItemAdd(): void
    {
        $this->template->pageHeading = 'Nová položka';
        $this->addBreadcrumbItem('Cms', 'Nová položka', action: 'itemAdd');
    }

    protected function createComponentMenuItemAdd(): Form
    {
        $form = new Form();
        $renderer = new DefaultFormRenderer;
        $renderer->wrappers['controls']['container'] = 'table class="form"';
        $form->setRenderer($renderer);

        $form->addGroup();

        // Add item name (text in menu)
        $form
            ->addText('name', '*Popisek položky:')
            ->setRequired('Vyplňte prosím popisek položky!')
            ->addRule(Form::MaxLength, 'Popisek položky je příliš dlouhý, max. délka je %d znaků!', 64);

        // Add item title (text in title attribute)
        $form
            ->addText('title', 'Titulek položky:')
            ->addRule(Form::MaxLength, 'Titulek položky je příliš dlouhý, max. délka je %d znaků!', 64);

        // Add item URL (relative or absolute)
        $form->addGroup();
        $form
            ->addText('fullUrl', 'Adresa položky:')
            ->setDisabled(true)
            ->setDefaultValue($this->template->baseUrl . '/' . (($this->parentItem) ? $this->parentItem->url : ''));

        $url = $form->addText('url', 'URL položky:');

        // If parent id is sets, check if parent is external, if it is external, child should be
        // external too, if is not external, child should be external or local
        if ($this->parentItem && $this->parentItem->pageId === null) {
            $url->addRule(Form::URL, 'URL Adresa není ve správném formátu!');
        } else {
            // Add URL type (local or extern)
            $extern = $form->addCheckbox('extern', 'Odkaz na externí stránky?');

            // Add condition on URL (if URL is extern, URL must be valid URL)
            $url
                ->addConditionOn($extern, Form::Equal, true)
                ->addRule(Form::URL, 'URL Adresa není ve správném formátu!');
        }

        // Add two buttons
        $form->addGroup();
        $form->addSubmit('save', 'Vytvořit');
        $form->addSubmit('cancel', 'Zrušit')->onClick[] = function () {
            $this->redirect('Cms:default');
        };

        $form->onSuccess[] = $this->menuItemAddSubmitted(...);

        return $form;
    }

    public function menuItemAddSubmitted(Form $form, MenuItemAddFormData $values): void
    {
        $parentUrl = '';
        $parentId = $this->parentItem ? $this->parentItem->id : null;
        $menuItems = $this->menuItemsFacade->getMenuItems($parentId, false, 'DESC');
        $lastItem = array_shift($menuItems);
        $sortOrder = $lastItem ? $lastItem->sortOrder + 1 : 0;

        $menuItem = new Model\MenuItem\MenuItemEntity(
            parentId: $parentId,
            pageId: null,
            name: $values->name,
            title: $values->title,
            urlQuery: '',
            urlFragment: '',
            urlRewriteName: '',
            url: $values->url,
            active: false,
            sortOrder: $sortOrder,
        );

        // If ID not empty
        if ($this->parentItem !== null) {
            $parentId = $this->parentItem->id;
            if ($this->parentItem->pageId == null) {
                $values->extern = true;
            } else {
                $parentUrl = $this->parentItem->url . '/';
            }
        }
        // Check title
        if ($menuItem->title == null) {
            $menuItem->title = $menuItem->name;
        }
        // If new item is not extern
        if ($values->extern === false) {
            if ($menuItem->url == null) {
                $menuItem->urlRewriteName = \Nette\Utils\Strings::webalize($values->name);
            } else {
                $menuItem->urlRewriteName = \Nette\Utils\Strings::webalize($values->url);
            }

            // Set full URL
            $menuItem->url = $parentUrl . $menuItem->urlRewriteName;

            // Create new page for this item
            $newPage = new Model\Page\PageEntity(
                heading: $menuItem->name,
                seoTitle: $menuItem->title,
            );
            $this->pagesFacade->persist($newPage);
            $menuItem->pageId = $newPage->id;
        }

        // Create the menu item in the database
        $this->menuItemsFacade->persist($menuItem);

        $this->flashMessage("Nová položka menu '" . $menuItem->id . ' - ' . $menuItem->name . "' byla úspěšně vytvořena!", 'info');
        $this->redirect('Cms:');
    }

    // #region EditMenuItem action

    public function actionItemEdit(int $id): void
    {
        $this->id = $id;
        $menuItem = $this->menuItemsFacade->getMenuItemById($this->id, false);
        if (!$menuItem) {
            $this->flashMessage('Nebylo zadáno platné ID položky menu. Nelze editovat', 'error');
            $this->redirect('Cms:');
        }

        $this->menuItem = $menuItem;
        $this->parentItem = $this->menuItemsFacade->getMenuItemById($this->menuItem->parentId, false);
    }

    public function renderItemEdit(): void
    {
        $this->template->menuItem = $this->menuItem;

        $this->template->pageHeading = 'Úprava položky';
        $this->addBreadcrumbItem('Cms', 'Úprava položky', action: 'itemEdit', argName: 'id', argValue: $this->id);
    }

    protected function createComponentMenuItemEdit(): Form
    {
        // New instance of nette form
        $form = new Form;
        $renderer = new DefaultFormRenderer;
        $renderer->wrappers['controls']['container'] = 'table class="form"';
        $form->setRenderer($renderer);

        $form->addGroup();

        // Add item name (text in menu)
        $form
            ->addText('name', '*Popisek položky:')
            ->setRequired('Vyplňte prosím popisek položky!')
            ->addRule(Form::MaxLength, 'Popisek položky je příliš dlouhý, max. délka je %d znaků!', 64)
            ->setDefaultValue($this->menuItem->name);

        // Add item title (text in title attribute)
        $form
            ->addText('title', 'Titulek položky:')
            ->addRule(Form::MaxLength, 'Titulek položky je příliš dlouhý, max. délka je %d znaků!', 64)
            ->setDefaultValue($this->menuItem->title);

        $form->addGroup();

        // Add full URL
        if ($this->menuItem->pageId != null) {
            $form
                ->addText('fullUrl', 'Adresa položky:')
                ->setDisabled(true)
                ->setDefaultValue($this->template->baseUrl . (($this->parentItem) ? $this->parentItem->url . '/' : ''));
        }

        // Add item URL (relative or absolute)
        $url = $form
            ->addText('url', 'URL položky:')
            ->setDefaultValue(($this->menuItem->pageId) ? $this->menuItem->urlRewriteName : $this->menuItem->url);

        // If parent id is sets, check if parent is external, if it is external, child should be
        // external too, if is not external, child should be external or local
        if ($this->menuItem->pageId === null) {
            $url->addRule(Form::URL, 'URL Adresa není ve správném formátu!');
        }

        $form->addGroup();
        $form
            ->addSelect('parentId', 'Nadřazená položka:', $this->generateMenuItemsInSelectbox($this->id))
            ->setPrompt('-- hlavní úroveň --')
            ->setDefaultValue($this->menuItem->parentId);

        // Add two buttons
        $form->addGroup();
        $form->addSubmit('save', 'Uložit');
        $form->addSubmit('save_and_back', 'Uložit a pokračovat');
        $form->addSubmit('cancel', 'Zrušit')->onClick[] = function () {
            $this->redirect('Cms:default');
        };

        // Add callback
        $form->onSuccess[] = $this->menuItemEditSubmitted(...);

        // Return new form instance
        return $form;
    }

    public function menuItemEditSubmitted(Form $form, MenuItemEditFormData $values): void
    {
        $parentUrl = '';
        $menuItem = $this->menuItemsFacade->getMenuItemById($this->id, false);
        if (!$menuItem) {
            throw new \Nette\Application\BadRequestException('Položka menu neexistuje!');
        }

        // Set new parent
        if ($menuItem->parentId != $values->parentId) {
            $this->parentItem = $this->menuItemsFacade->getMenuItemById($values->parentId, false);

            $index = 0;
            $oldParentItems = $this->menuItemsFacade->getMenuItems($menuItem->parentId, false);
            foreach ($oldParentItems as $index => $item) {
                $item->sortOrder = $index;
                $this->menuItemsFacade->persist($item);
                $index++;
            }

            $menuItem->parentId = $values->parentId;
            $newParentItems = $this->menuItemsFacade->getMenuItems($values->parentId, false);
            $menuItem->sortOrder = count($newParentItems);
        }

        // If ID not empty
        if ($this->parentItem !== null) {
            if ($this->parentItem->pageId != null) {
                $parentUrl = $this->parentItem->url . '/';
            }
        }
        // Check title
        $menuItem->name = $values->name;
        $menuItem->title = $values->title;
        if ($menuItem->title == null) {
            $menuItem->title = $menuItem->name;
        }
        // If new item is not extern
        if ($menuItem->pageId != null) {
            if ($values->url == null) {
                $menuItem->urlRewriteName = \Nette\Utils\Strings::webalize($values->name);
            } else {
                $menuItem->urlRewriteName = \Nette\Utils\Strings::webalize($values->url);
            }

            // Set full URL
            $menuItem->url = $parentUrl . $menuItem->urlRewriteName;

            // Edit all URLs for children items
            $this->updateChildrenUrl($menuItem->id, $menuItem->url);
        } else {
            $menuItem->url = $values->url;
        }

        $this->menuItemsFacade->persist($menuItem);
        $this->flashMessage("Položka menu '" . $menuItem->id . ' - ' . $menuItem->name . "' byla úspěšně aktualizována.", 'info');
        if ($form->isSubmitted() === $form['save_and_back']) {
            $this->redirect('Cms:default');
        }

        $this->redirect('this');
    }

    // #region EditPage action

    public function actionPageEdit(int $id): void
    {
        $this->id = $id;
        $page = $this->pagesFacade->getPageById($id);
        $pageMenuItem = $this->menuItemsFacade->getMenuItemByPageId($id, false);

        if (!$page || (!$page->isHomepage && !$pageMenuItem)) {
            $this->flashMessage('Nebylo zadáno platné ID stránky obsahu. Nelze editovat', 'error');
            $this->redirect('Cms:');
        }

        $this->page = $page;
        $this->pageMenuItem = $pageMenuItem;

        if ($this->isAjax()) {
            $this->redrawControl('comments');
        }
    }

    public function renderPageEdit(): void
    {
        $this->template->page = $this->page;
        $this->template->menuItem = $this->pageMenuItem;

        $this->template->pageHeading = 'Úprava obsahu stránky';
        $this->addBreadcrumbItem('Cms', 'Úprava stránky', action: 'pageEdit', argName: 'id', argValue: $this->id);
    }

    protected function createComponentPageEdit(): Form
    {
        // New instance of nette form
        $form = new Form;
        $renderer = new DefaultFormRenderer;
        $renderer->wrappers['controls']['container'] = 'table class="form"';
        $form->setRenderer($renderer);

        $form->addGroup();

        $form
            ->addText('created', 'Vytvořeno:')
            ->setDisabled(true)
            ->setDefaultValue(DateTime::from($this->page->created)->format('Y-m-d H:i:s'));
        $form
            ->addText('modified', 'Naposledy upraveno:')
            ->setDisabled(true)
            ->setDefaultValue(DateTime::from($this->page->modified)->format('Y-m-d H:i:s'));

        $form->addGroup();
        $form
            ->addText('heading', 'Nadpis stránky:')
            ->setDefaultValue($this->page->heading);

        $form->addGroup();
        $form
            ->addText('seoTitle', '(SEO) Titulek:')
            ->setDefaultValue($this->page->seoTitle);
        $form
            ->addText('seoKeywords', '(SEO) Klíčová slova:')
            ->setDefaultValue($this->page->seoKeywords);
        $form
            ->addTextArea('seoDescription', '(SEO) Popis:', 80, 3)
            ->setDefaultValue($this->page->seoDescription);

        $form->addGroup();
        $form
            ->addRadioList('allowComments', 'Povolit komentáře?', array(1 => 'Ano', 0 => 'Ne'))
            ->setDefaultValue($this->page->allowComments ? 1 : 0);

        $form
            ->addTextArea('content', null, 80, 40)
            ->setRequired('Musíte napsat nějaký obsah, který se bude zobrazovat na stránce!')
            ->setDefaultValue($this->page->content);

        // Add two buttons
        $form->addGroup();
        $form->addSubmit('save', 'Uložit');
        $form->addSubmit('save_and_back', 'Uložit a pokračovat');
        $form->addSubmit('cancel', 'Zrušit')->onClick[] = function () {
            $this->redirect('Cms:default');
        };

        // Add callback
        $form->onSuccess[] = $this->pageEditSubmitted(...);

        // Return new form instance
        return $form;
    }

    public function pageEditSubmitted(Form $form, PageEditFormData $values): void
    {
        $this->page->modified = new \DateTime();
        $this->page->heading = $values->heading;
        $this->page->seoTitle = $values->seoTitle;
        $this->page->seoKeywords = $values->seoKeywords;
        $this->page->seoDescription = $values->seoDescription;
        $this->page->allowComments = (bool) $values->allowComments;
        $this->page->content = $values->content;

        $this->pagesFacade->persist($this->page);

        $this->flashMessage("Stránka obsahu '" . $this->page->id . ' - ' . $values->heading . "' byla úspěšně aktualizována.", 'info');
        if ($form->isSubmitted() === $form['save_and_back']) {
            $this->redirect('Cms:default');
        }

        $this->redirect('this');
    }

    // #region Helpers

    private function updateChildrenUrl(?int $parentId, string $parentUrl): void
    {
        $children = $this->menuItemsFacade->getMenuItems($parentId, false);
        foreach ($children as $child) {
            $child->url = $parentUrl . '/' . $child->urlRewriteName;
            $this->menuItemsFacade->persist($child);
            $this->updateChildrenUrl($child->id, $child->url);
        }
    }

    /**
     * @return array<int|string, string>
     */
    private function generateMenuItemsInSelectbox(?int $itemId, ?int $parentId = null, int $level = 0): array
    {
        $items = [];
        $result = $this->menuItemsFacade->getMenuItems($parentId, false);

        foreach ($result as $item) {
            if ($item->id === $itemId) {
                continue;
            }
            $items[$item->id] = str_repeat('-', 4 * $level) . ' ' . $item->name;
            $children = $this->generateMenuItemsInSelectbox($itemId, $item->id, $level + 1);
            if (!empty($children)) {
                foreach ($children as $id => $child) {
                    $items[$id] = $child;
                }
            }
        }

        return $items;
    }
}
