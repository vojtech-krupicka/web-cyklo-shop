<?php declare(strict_types=1);

namespace Model;

final class MenuItemsFacade extends BaseFacade
{
    public function getCurrentMenuItemByUri(string $uri): ?\Nette\Database\Table\ActiveRow
    {
        return $this
            ->dbconn
            ->table('menu_items')
            ->where('url', $uri)
            ->where('active', true)
            ->order('sort_order ASC')
            ->limit(1)
            ->fetch();
    }

    public function getCurrentMenuItemById(?int $id): ?\Nette\Database\Table\ActiveRow
    {
        return !$id ? null : $this
            ->dbconn
            ->table('menu_items')
            ->where('id', $id)
            ->where('active', true)
            ->order('sort_order ASC')
            ->limit(1)
            ->fetch();
    }

    public function getMenuItems(?int $parentId = null, bool $activeOnly = true, string $order = 'ASC'): \Nette\Database\Table\Selection
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
