<?php declare(strict_types=1);

namespace App\Presentation\FrontModule;

use App;
use App\Components\FrontModule\CmsMenu;
use App\Components\FrontModule\MainMenu;
use App\Components\FrontModule\BreadcrumbMenu;
use Nette\Application\Attributes\Persistent;
use Nette\DI\Attributes\Inject;

abstract class BasePresenter extends App\Presentation\BasePresenter
{

    #[Inject]
    public \Model\SeoSettings $seoSettings;

    #[Inject]
    public \Model\ShopInfo $shopInfo;

    #[Inject]
    public CmsMenu\CmsMenuControlFactory  $cmsMenuControlFactory;

    #[Inject]
    public MainMenu\MainMenuControlFactory  $mainMenuControlFactory;

    #[Inject]
    public BreadcrumbMenu\BreadcrumbMenuControlFactory  $breadcrumbMenuControlFactory;

	public function beforeRender()
	{
		parent::beforeRender();

		$this->template->pageHeading = null;
        $this->template->breadcrumbItems = [];
        $this->addBreadcrumbItem("Homepage", "Úvod");

		$this->template->seoTitle = $this->seoSettings->title;
		$this->template->seoKeywords = $this->seoSettings->keywords;
		$this->template->seoDescription = $this->seoSettings->description;

		$this->template->shopInfo = $this->shopInfo->info;
		$this->template->openingHours = $this->shopInfo->openingHours;

		$this->template->activeItemId = null;
	}

    protected function addBreadcrumbItem(string $presenter, string $title, string $action = "", ?string $argName = null, mixed $argValue = null): void
    {
        $this->template->breadcrumbItems[] = new BreadcrumbMenu\BreadcrumbMenuItem($presenter, $title, $action, $argName, $argValue);
    }

    protected function createComponentCmsMenu(): CmsMenu\CmsMenuControl
	{
		return $this->cmsMenuControlFactory->create();
	}

    protected function createComponentMainMenu(): MainMenu\MainMenuControl
	{
		return $this->mainMenuControlFactory->create();
	}

    protected function createComponentBreadcrumb(): BreadcrumbMenu\BreadcrumbMenuControl
	{
		return $this->breadcrumbMenuControlFactory->create();
	}

}
