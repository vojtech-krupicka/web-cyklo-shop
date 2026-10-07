<?php declare(strict_types=1);

namespace App\Presentation\AdminModule\Default;

use App\Presentation\AdminModule;
use App\Model;

final class DefaultPresenter extends AdminModule\BaseSecuredPresenter
{
    public function __construct(
        private Model\Member\MemberFacade $membersFacade,
    ) {}

    public function renderDefault(): void
    {
        $this->template->pageHeading = 'Administrační systém';
        $this->template->members = $this->membersFacade->getAll();
    }
}
