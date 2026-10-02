<?php

namespace Model;

final class GalleryFacade
{


	public function __construct(
        private \Nette\Database\Explorer $dbconn
    )
	{ }

    public function getGalleryItems(string $order = 'sort_order', int|null $limit = null): \Nette\Database\Table\Selection
    {
        $query = $this->dbconn->table('gallery_items')->where('active', true)->order($order);
        if ($limit !== null) {
            $query = $query->limit($limit);
        }
        return $query;
    }


}
