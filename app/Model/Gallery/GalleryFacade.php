<?php declare(strict_types=1);

namespace App\Model\Gallery;

use App\Model\BaseFacade;

final class GalleryFacade extends BaseFacade
{
    public function getGallery(?int $id, bool $activeOnly = true): ?GalleryEntity
    {
        if ($id === null) {
            return null;
        }
        $query = $this->dbconn->table('galleries');
        if ($activeOnly) {
            $query->where('active', true);
        }

        $row = $query->get($id);
        return $row ? GalleryEntity::fromActiveRow($row) : null;
    }

    public function getGalleries(bool $activeOnly = true, string $order = 'added'): array
    {
        $query = $this->dbconn->table('galleries');
        if ($activeOnly) {
            $query->where('active', true);
        }

        $query->order($order);
        return GalleryEntity::fromSelection($query);
    }

    public function getMedia(?int $id, bool $activeOnly = true): ?GalleryMediaEntity
    {
        $query = $this->dbconn->table('gallery_items');
        if ($activeOnly) {
            $query->where('active', true);
        }

        if ($id === null) {
            return null;
        }
        $row = $query->get($id);
        return $row ? GalleryMediaEntity::fromActiveRow($row) : null;
    }

    public function getGalleryItems(
        ?int $galleryId = null,
        bool $activeOnly = true,
        string $order = 'sort_order',
        int|null $limit = null,
    ): array {
        $query = $this->dbconn->table('gallery_items')->order($order);
        if ($galleryId !== null) {
            $query->where('gallery_id', $galleryId);
        }
        if ($activeOnly) {
            $query->where('active', true);
        }
        if ($limit !== null) {
            $query->limit($limit);
        }
        return GalleryMediaEntity::fromSelection($query);
    }

    public function persist(GalleryEntity $gallery): void
    {
        $data = [
            'name' => $gallery->name,
            'description' => $gallery->description,
            'active' => $gallery->active,
            'allow_comments' => $gallery->allowComments,
            'added' => $gallery->added,
        ];

        if ($gallery->id === null) {
            $row = $this->dbconn->table('galleries')->insert($data);
            $gallery->id = (int) $row['id'];
        } else {
            $this->dbconn->table('galleries')->get($gallery->id)->update($data);
        }
    }

    public function delete(GalleryEntity $gallery): void
    {
        $this->dbconn->table('galleries')->get($gallery->id)->delete();
    }

    public function persistMedia(GalleryMediaEntity $media): void
    {
        $data = [
            'gallery_id' => $media->galleryId,
            'file_name' => $media->filename,
            'title' => $media->title,
            'description' => $media->description,
            'active' => $media->active,
            'sort_order' => $media->sortOrder,
            'added' => $media->added,
        ];

        if ($media->id === null) {
            $row = $this->dbconn->table('gallery_items')->insert($data);
            $media->id = (int) $row['id'];
        } else {
            $this->dbconn->table('gallery_items')->get($media->id)->update($data);
        }
    }

    public function deleteMedia(GalleryMediaEntity $media): void
    {
        $this->dbconn->table('gallery_items')->get($media->id)->delete();
    }
}
