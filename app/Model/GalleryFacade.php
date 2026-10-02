<?php declare(strict_types=1);

namespace Model;

final class GalleryFacade
{


	public function __construct(
        private \Nette\Database\Explorer $dbconn
    )
	{ }


    public function getGallery(int $id, bool $active = true, $order = 'added'): \Nette\Database\Table\ActiveRow|null
    {
        return $this->getGalleries($active, $order)->where('id', $id)->fetch();
    }

    public function getGalleries(bool $active = true, $order = 'added'): \Nette\Database\Table\Selection
    {
        return $this->dbconn->table('galleries')->where('active', $active)->order($order);
    }

    public function getGalleryItems(?int $galleryId = null, bool $active = true, string $order = 'sort_order', int|null $limit = null): \Nette\Database\Table\Selection
    {
        $query = $this->dbconn->table('gallery_items')->where('active', $active)->order($order);
        if ($galleryId !== null) {
            $query = $query->where('gallery_id', $galleryId);
        }
        if ($limit !== null) {
            $query = $query->limit($limit);
        }
        return $query;
    }


}
