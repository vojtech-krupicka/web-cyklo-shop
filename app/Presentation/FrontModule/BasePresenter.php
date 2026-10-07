<?php declare(strict_types=1);

namespace App\Presentation\FrontModule;

use App\Components\FrontModule\CmsMenu;
use App\Components\FrontModule\MainMenu;
use App\Model;
use Nette\DI\Attributes\Inject;
use App;

abstract class BasePresenter extends App\Presentation\BasePresenter
{
    #[Inject]
    public Model\SeoSettings $seoSettings;

    #[Inject]
    public CmsMenu\CmsMenuControlFactory $cmsMenuControlFactory;

    #[Inject]
    public MainMenu\MainMenuControlFactory $mainMenuControlFactory;

    public function beforeRender()
    {
        parent::beforeRender();

        $this->addBreadcrumbItem('Homepage', 'Úvod');

        $this->template->seoTitle = $this->seoSettings->title;
        $this->template->seoKeywords = $this->seoSettings->keywords;
        $this->template->seoDescription = $this->seoSettings->description;

        $this->template->activeItemId = null;
    }

    protected function createComponentCmsMenu(): CmsMenu\CmsMenuControl
    {
        return $this->cmsMenuControlFactory->create();
    }

    protected function createComponentMainMenu(): MainMenu\MainMenuControl
    {
        return $this->mainMenuControlFactory->create();
    }
}
