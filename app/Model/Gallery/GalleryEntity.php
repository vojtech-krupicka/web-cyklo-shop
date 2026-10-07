<?php declare(strict_types=1);

namespace App\Model\Gallery;

use App\Model\BaseEntity;

final class GalleryEntity extends BaseEntity
{
    public function __construct(
        public ?int $id = null,
        public string $name = '',
        public string $description = '',
        public bool $active = false,
        public bool $allowComments = true,
        public \DateTime $added = new \DateTime(),
    ) {}

    public static function fromActiveRow(\Nette\Database\Table\ActiveRow $row): self
    {
        return new self(
            id: (int) $row->id,
            name: (string) $row->name,
            description: (string) $row->description,
            active: (bool) $row->active,
            allowComments: (bool) $row->allow_comments,
            added: $row->added,
        );
    }
}
