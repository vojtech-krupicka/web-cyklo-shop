<?php declare(strict_types=1);

namespace App\Presentation\FrontModule\Cms;

use App\Presentation\FrontModule;
use App\Model;
use Nette\Application\Attributes\Persistent;

final class CmsPresenter extends FrontModule\BasePresenter
{
    #[Persistent]
    public ?string $uri = null;

    private Model\MenuItem\MenuItemEntity $menuItem;
    private Model\Page\PageEntity $page;

    public function __construct(
        private Model\Page\PageFacade $pagesFacade,
        private Model\MenuItem\MenuItemFacade $menuItemsFacade,
    ) {}

    public function renderDefault(): void
    {
        if (!$this->uri) {
            throw new \Nette\Application\BadRequestException('Nebyla zadána platná URL!');
        }

        // Get current menu item
        $menuItem = $this->menuItemsFacade->getMenuItemByUri($this->uri);
        if (!$menuItem) {
            throw new \Nette\Application\BadRequestException("Stránka s URL '" . $this->uri . "' neexistuje!");
        }

        // Get page
        if (!$menuItem->pageId) {
            throw new \Nette\Application\BadRequestException("Stránka s URL '" . $this->uri . "' neexistuje!");
        }

        $page = $this->pagesFacade->getPageById($menuItem->pageId);
        if (!$page) {
            throw new \Nette\Application\BadRequestException("Stránka s URL '" . $this->uri . "' neexistuje!");
        }

        $this->page = $page;
        $this->menuItem = $menuItem;

        // Get all parent menu items for breadcrumb
        $parentItems = [];
        $parentItems[] = $this->menuItem;

        $parentItem = $this->menuItemsFacade->getMenuItemById($this->menuItem->parentId);
        while ($parentItem) {
            $parentItems[] = $parentItem;
            $parentId = $parentItem->parentId;
            $parentItem = $this->menuItemsFacade->getMenuItemById($parentId);
        }

        // Add all breadcrumb items
        foreach (array_reverse($parentItems) as $item) {
            $this->addBreadcrumbItem('Cms', $item->title, argName: 'uri', argValue: $item->url);
        }

        // Set template
        $this->template->menuItem = $this->menuItem;
        $this->template->activeItemId = $this->menuItem->id;

        if (!empty($this->page->seoTitle)) {
            $this->template->seoTitle = $this->page->seoTitle;
        }
        if (!empty($this->page->seoKeywords)) {
            $this->template->seoKeywords = $this->page->seoKeywords;
        }
        if (!empty($this->page->seoDescription)) {
            $this->template->seoDescription = $this->page->seoDescription;
        }

        $this->template->page = $this->page;
        $this->template->pageHeading = $this->page->heading;
    }
}
