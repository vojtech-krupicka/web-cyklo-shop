<?php declare(strict_types=1);

namespace App\Model\Settings;

final class SeoSettings
{
    public function __construct(
        public readonly string $title,
        public readonly string $keywords,
        public readonly string $description,
    ) {}
}
