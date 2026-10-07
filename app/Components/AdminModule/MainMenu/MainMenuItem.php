<?php declare(strict_types=1);

namespace App\Components\AdminModule\MainMenu;

final class MainMenuItem
{
    public bool $isActive = false;

    public function __construct(
        public readonly string $presenter,
        public readonly string $title,
        string $presenterName,
    ) {
        $this->isActive = $presenterName === 'AdminModule:' . $presenter;
    }
}
