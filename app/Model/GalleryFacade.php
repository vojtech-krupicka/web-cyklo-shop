<?php declare(strict_types=1);

namespace Model;

final class GalleryEntity extends BaseEntity
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $description,
        public readonly bool $active,
        public readonly bool $allowComments,
        public \DateTime $added,
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

final class GalleryMediaEntity extends BaseEntity
{
    public function __construct(
        public readonly int $id,
        public readonly int $galleryId,
        public readonly string $filename,
        public readonly string $title,
        public readonly string $description,
        public readonly bool $active,
        public readonly int $sortOrder,
        public \DateTime $added,
    ) {}

    public static function fromActiveRow(\Nette\Database\Table\ActiveRow $row): self
    {
        return new self(
            id: (int) $row->id,
            galleryId: (int) $row->gallery_id,
            filename: (string) $row->file_name,
            title: (string) $row->title,
            description: (string) $row->description,
            active: (bool) $row->active,
            sortOrder: (int) $row->sort_order,
            added: $row->added,
        );
    }
}

final class GalleryFacade extends BaseFacade
{
    public function getGallery(int $id, bool $active = true): ?GalleryEntity
    {
        $row = $this->dbconn->table('galleries')->where('active', $active)->get($id);
        return $row ? GalleryEntity::fromActiveRow($row) : null;
    }

    public function getGalleries(bool $active = true, $order = 'added'): array
    {
        $result = $this->dbconn->table('galleries')->where('active', $active)->order($order);
        return GalleryEntity::fromSelection($result);
    }

    public function getGalleryItems(
        ?int $galleryId = null,
        bool $active = true,
        string $order = 'sort_order',
        int|null $limit = null,
    ): array {
        $query = $this->dbconn->table('gallery_items')->where('active', $active)->order($order);
        if ($galleryId !== null) {
            $query = $query->where('gallery_id', $galleryId);
        }
        if ($limit !== null) {
            $query = $query->limit($limit);
        }
        return GalleryMediaEntity::fromSelection($query);
    }
}
