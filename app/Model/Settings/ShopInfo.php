<?php declare(strict_types=1);

namespace App\Model\Settings;

final class ShopInfo
{
    public readonly ShopInfoItem $info;

    /**
     * @var list<OpeningHoursItem>
     */
    public readonly array $openingHours;

    /**
     * @param array{
     *     name: string, url: string, street: string, city: string,
     *     phone: string, mobile: string, email: string, ico: string,
     *     location: array{lon: float, lat: float}
     * } $info
     * @param list<array{key: string, value: string}> $openingHours
     */
    public function __construct(
        array $info,
        array $openingHours
    ) {
        $this->info = new ShopInfoItem(...$info);
        $this->openingHours = array_map(fn($item) => new OpeningHoursItem(...$item), $openingHours);
    }
}
