<?php declare(strict_types=1);

namespace App\Model\Settings;

final class OpeningHoursItem
{
    public function __construct(
        public readonly string $key,
        public readonly string $value,
    ) {}
}
