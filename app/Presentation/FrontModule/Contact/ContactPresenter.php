<?php declare(strict_types=1);

namespace App\Presentation\FrontModule\Contact;

use App\Presentation\FrontModule;


final class ContactPresenter extends FrontModule\BasePresenter
{

    public function renderDefault(): void {
        $this->template->pageHeading = "Kontakt";
        $this->addBreadcrumbItem("Contact", "Kontakt");
    }

}
