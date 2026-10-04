<?php declare(strict_types=1);

namespace Model;

final class MenuItemEntity extends BaseEntity
{
    public function __construct(
        public readonly int $id,
        public readonly int $parentId,
        public readonly int $pageId,
        public readonly string $name,
        public readonly string $title,
        public readonly string $urlQuery,
        public readonly string $urlFragment,
        public readonly string $urlRewriteName,
        public readonly string $url,
        public readonly bool $active,
        public readonly int $sortOrder,
    ) {}

    public static function fromActiveRow(\Nette\Database\Table\ActiveRow $row): self
    {
        return new self(
            id: (int) $row->id,
            parentId: (int) $row->parent_id,
            pageId: (int) $row->page_id,
            name: (string) $row->name,
            title: (string) $row->title,
            urlQuery: (string) $row->url_query,
            urlFragment: (string) $row->url_fragment,
            urlRewriteName: (string) $row->url_rewrite_name,
            url: (string) $row->url,
            active: (bool) $row->active,
            sortOrder: (int) $row->sort_order,
        );
    }
}

final class MenuItemsFacade extends BaseFacade
{
    public function getCurrentMenuItemByUri(string $uri): ?MenuItemEntity
    {
        $row = $this
            ->dbconn
            ->table('menu_items')
            ->where('url', $uri)
            ->where('active', true)
            ->order('sort_order ASC')
            ->limit(1)
            ->fetch();
        return $row ? MenuItemEntity::fromActiveRow($row) : null;
    }

    public function getCurrentMenuItemById(?int $id): ?MenuItemEntity
    {
        if ($id === null) {
            return null;
        }
        $row = $this
            ->dbconn
            ->table('menu_items')
            ->where('active', true)
            ->get($id);
        return $row ? MenuItemEntity::fromActiveRow($row) : null;
    }

    public function getMenuItems(?int $parentId = null, bool $activeOnly = true, string $order = 'ASC'): array
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

        $query->order("sort_order {$order}");

        return MenuItemEntity::fromSelection($query);
    }
}
