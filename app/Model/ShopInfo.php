<?php declare(strict_types=1);

namespace Model;


final class ShopInfoItems
{
    public function __construct(
        public readonly string $name,
        public readonly string $url,
        public readonly string $street,
        public readonly string $city,
        public readonly string $phone,
        public readonly string $mobile,
		public readonly string $email,
		public readonly string $ico,
    ) {}
}

final class OpeningHoursItems
{
    public function __construct(
        public readonly string $key,
        public readonly string $value,
    ) {}
}

final class ShopInfo
{
    public readonly ShopInfoItems $info;
    public readonly array $openingHours;

	public function __construct(
		array $info,
		array $openingHours
	) {
		$this->info = new ShopInfoItems(...$info);
		$this->openingHours = array_map(fn($item) => new OpeningHoursItems(...$item), $openingHours);
	}
}
