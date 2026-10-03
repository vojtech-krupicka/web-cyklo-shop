<?php declare(strict_types=1);

namespace App\Presentation\FrontModule\Sitemap;

use App\Presentation\FrontModule;


final class SitemapPresenter extends FrontModule\BasePresenter
{
    public function renderDefault(): void {
        $this->template->pageHeading = "Mapa stránek";
        $this->addBreadcrumbItem("Sitemap", "Mapa stránek");
    }
}
