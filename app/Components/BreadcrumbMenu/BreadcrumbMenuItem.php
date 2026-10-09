<?php declare(strict_types=1);

namespace App\Components\BreadcrumbMenu;

final class BreadcrumbMenuItem
{
    public function __construct(
        public readonly string $presenter,
        public readonly string $title,
        public readonly string $action = '',
        public readonly ?string $argName = null,
        public readonly mixed $argValue = null,
    ) {}
}
