<?php declare(strict_types=1);

namespace App\Presentation\FrontModule\Contact;

use App\Presentation\FrontModule;


final class ContactPresenter extends FrontModule\BasePresenter
{

    public function __construct(
        private string $mapyApiKey,
    ) {}


    public function renderDefault(): void {
        $this->template->mapyApiKey = $this->mapyApiKey;
        $this->template->pageHeading = "Kontakt";
        $this->addBreadcrumbItem("Contact", "Kontakt");
    }

}
