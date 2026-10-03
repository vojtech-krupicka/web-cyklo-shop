<?php declare(strict_types=1);

namespace App\Presentation\FrontModule\Cms;

use App\Presentation\FrontModule;
use Nette\Application\Attributes\Persistent;
use Nette\Database\Table\ActiveRow;


final class CmsPresenter extends FrontModule\BasePresenter
{


	#[Persistent]
	public ?string $uri = null;

	private ?ActiveRow $menuItem = null;
	private ?ActiveRow $page = null;


    public function __construct(
        private \Model\PagesFacade $pagesFacade,
        private \Model\MenuItemsFacade $menuItemsFacade,
    )
    { }


    public function renderDefault(): void
	{
        // Get current menu item
		$this->menuItem = $this->menuItemsFacade->getCurrentMenuItemByUri($this->uri);
		if(!$this->menuItem) {
			throw new \Nette\Application\BadRequestException("Stránka s URL '".$this->uri."' neexistuje!");
		}

		// Get page
		$this->page = $this->pagesFacade->getPageById($this->menuItem['page_id']);
		if(!$this->page) {
			throw new \Nette\Application\BadRequestException("Stránka s URL '".$this->uri."' neexistuje!");
		}

        // Get all parent menu items for breadcrumb
        $parentItems = array();
		$parentItems[] = $this->menuItem;

		$parentObj = $this->menuItemsFacade->getCurrentMenuItemById($this->menuItem['parent_id']);
		while($parentObj) {
			$parentItems[] = $parentObj;
			$parentId = $parentObj['parent_id'];
			$parentObj = $this->menuItemsFacade->getCurrentMenuItemById($parentId);
		}

        // Add all breadcrumb items
        foreach(array_reverse($parentItems) as $item) {
			$this->addBreadcrumbItem("Cms", $item->title, argName: "uri", argValue: $item->url);
        }

        // Set template
		//$this->template->parentItems = array_reverse($parentItems);
		$this->template->menuItem = $this->menuItem;
		$this->template->activeItemId = $this->menuItem->id;

		if(!empty($this->page->seo_title)) {
			$this->template->seoTitle = $this->page->seo_title;
		}
		if(!empty($this->page->seo_keywords)) {
			$this->template->seoKeywords = $this->page->seo_keywords;
		}
		if(!empty($this->page->seo_description)) {
			$this->template->seoDescription = $this->page->seo_description;
		}

		$this->template->page = $this->page;
        $this->template->pageHeading = $this->page->heading;


    }
}
