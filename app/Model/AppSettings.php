<?php declare(strict_types=1);

namespace App\Model;

final class AppSettings
{
    public function __construct(
        public readonly string $wwwDir,
        public readonly string $resourcesDir
    ) {}
}
