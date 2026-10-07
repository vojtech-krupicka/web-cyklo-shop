<?php declare(strict_types=1);

namespace App\Model\Settings;

final class ShopInfoItem
{
    public readonly Location $location;

    public function __construct(
        public readonly string $name,
        public readonly string $url,
        public readonly string $street,
        public readonly string $city,
        public readonly string $phone,
        public readonly string $mobile,
        public readonly string $email,
        public readonly string $ico,
        array $location,
    ) {
        $this->location = new Location(...$location);
    }
}
