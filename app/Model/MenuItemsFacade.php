<?php declare(strict_types=1);

namespace App\Model;

final class MenuItemEntity extends BaseEntity
{
    public function __construct(
        public ?int $id = null,
        public ?int $parentId = null,
        public ?int $pageId = null,
        public string $name = '',
        public string $title = '',
        public string $urlQuery = '',
        public string $urlFragment = '',
        public string $urlRewriteName = '',
        public string $url = '',
        public bool $active = false,
        public int $sortOrder = 0,
    ) {}

    public static function fromActiveRow(\Nette\Database\Table\ActiveRow $row): self
    {
        return new self(
            id: (int) $row->id,
            parentId: $row->parent_id !== null ? (int) $row->parent_id : null,
            pageId: $row->page_id !== null ? (int) $row->page_id : null,
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
    public function getMenuItemByUri(string $uri): ?MenuItemEntity
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

    public function getMenuItemById(?int $id, bool $activeOnly = true): ?MenuItemEntity
    {
        if ($id === null) {
            return null;
        }
        $query = $this->dbconn->table('menu_items');
        if ($activeOnly) {
            $query->where('active', true);
        }

        $row = $query->get($id);
        return $row ? MenuItemEntity::fromActiveRow($row) : null;
    }

    public function getMenuItemByPageId(?int $pageId, bool $activeOnly = true): ?MenuItemEntity
    {
        if ($pageId === null) {
            return null;
        }
        $query = $this->dbconn->table('menu_items');
        if ($activeOnly) {
            $query->where('active', true);
        }

        $row = $query->where('page_id', $pageId)->limit(1)->fetch();
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

    public function persist(MenuItemEntity $item): void
    {
        $data = [
            'parent_id' => $item->parentId,
            'page_id' => $item->pageId,
            'name' => $item->name,
            'title' => $item->title,
            'url_query' => $item->urlQuery,
            'url_fragment' => $item->urlFragment,
            'url_rewrite_name' => $item->urlRewriteName,
            'url' => $item->url,
            'active' => $item->active,
            'sort_order' => $item->sortOrder,
        ];

        if ($item->id === null) {
            $row = $this->dbconn->table('menu_items')->insert($data);
            $item->id = (int) $row->id;
        } else {
            $this->dbconn->table('menu_items')->where('id', $item->id)->update($data);
        }
    }

    public function delete(MenuItemEntity $item): void
    {
        $this->dbconn->table('menu_items')->where('id', $item->id)->delete();
    }
}
