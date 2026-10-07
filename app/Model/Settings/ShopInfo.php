<?php declare(strict_types=1);

namespace App\Model\Settings;

final class ShopInfo
{
    public readonly ShopInfoItem $info;
    public readonly array $openingHours;

    public function __construct(
        array $info,
        array $openingHours
    ) {
        $this->info = new ShopInfoItem(...$info);
        $this->openingHours = array_map(fn($item) => new OpeningHoursItem(...$item), $openingHours);
    }
}
