<?php declare(strict_types=1);

namespace App\Presentation;

use App\Components\BreadcrumbMenu;
use Nette\DI\Attributes\Inject;
use Nette;

/**
 * @abstract  \BasePresenter
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    #[Inject]
    public \Model\ShopInfo $shopInfo;

    #[Inject]
    public BreadcrumbMenu\BreadcrumbMenuControlFactory $breadcrumbMenuControlFactory;

    public function startup(): void
    {
        // Zavola rodice !!!
        parent::startup();

        // Nastavi generovani absolutnich URL
        $this->absoluteUrls = true;

        // Nastaveni ticheho modu pro generovani odkazu
        $this->invalidLinkMode = self::InvalidLinkWarning;

        return;
    }

    public function beforeRender()
    {
        parent::beforeRender();

        $this->template->pageHeading = null;
        $this->template->breadcrumbItems = [];

        $this->template->shopInfo = $this->shopInfo->info;
        $this->template->openingHours = $this->shopInfo->openingHours;
    }

    protected function addBreadcrumbItem(string $presenter, string $title, string $action = '', ?string $argName = null, mixed $argValue = null): void
    {
        $this->template->breadcrumbItems[] = new BreadcrumbMenu\BreadcrumbMenuItem($presenter, $title, $action, $argName, $argValue);
    }

    protected function createComponentBreadcrumb(): BreadcrumbMenu\BreadcrumbMenuControl
    {
        return $this->breadcrumbMenuControlFactory->create();
    }

    /**
     * Overloading flash message method for authomatic invalidate snippet.
     * @param mixed $message
     * @param mixed $type
     * @return \stdClass
     */
    public function flashMessage($message, $type = 'info'): \stdClass
    {
        $flash = parent::flashMessage($message, $type);

        if ($this->isAjax()) {
            $this->invalidateControl('flashes');
        }

        return $flash;
    }
}
