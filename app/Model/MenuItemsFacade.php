<?php

namespace Model;

final class MenuItemsFacade
{

	public function __construct(
        private \Nette\Database\Explorer $dbconn
    )
	{ }


    public function getMenuItems(?int $parentId = null, bool $activeOnly = true, string $order = "ASC"): \Nette\Database\Table\Selection
    {
        $query = $this->dbconn->table('menu_items');

        if ($parentId !== null) {
            $query->where('parent_id', $parentId);
        } else {
            $query->where('parent_id IS NULL');
        }

        if ($activeOnly) {
            $query->where('active', true);
        }

        return $query->order("sort_order {$order}");
    }

}
