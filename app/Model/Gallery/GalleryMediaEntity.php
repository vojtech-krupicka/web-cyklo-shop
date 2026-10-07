<?php declare(strict_types=1);

namespace App\Model\Gallery;

use App\Model\BaseEntity;

final class GalleryMediaEntity extends BaseEntity
{
    public function __construct(
        public ?int $id = null,
        public ?int $galleryId = null,
        public string $filename = '',
        public string $title = '',
        public string $description = '',
        public bool $active = false,
        public int $sortOrder = 0,
        public \DateTime $added = new \DateTime(),
    ) {}

    public static function fromActiveRow(\Nette\Database\Table\ActiveRow $row): self
    {
        return new self(
            id: (int) $row['id'],
            galleryId: (int) $row['gallery_id'],
            filename: (string) $row['file_name'],
            title: (string) $row['title'],
            description: (string) $row['description'],
            active: (bool) $row['active'],
            sortOrder: (int) $row['sort_order'],
            added: $row['added'],
        );
    }
}
