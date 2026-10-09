<?php declare(strict_types=1);

namespace App\Model\Settings;

final class Location
{
    public function __construct(
        public readonly float $lon,
        public readonly float $lat,
    ) {}
}
