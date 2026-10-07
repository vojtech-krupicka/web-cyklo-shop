<?php declare(strict_types=1);

namespace App\Model\MenuItem;

use App\Model\BaseEntity;

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

    public static function fromActiveRow(\Nette\Database\Table\ActiveRow $row): static
    {
        return new self(
            id: (int) $row['id'],
            parentId: $row['parent_id'] !== null ? (int) $row['parent_id'] : null,
            pageId: $row['page_id'] !== null ? (int) $row['page_id'] : null,
            name: (string) $row['name'],
            title: (string) $row['title'],
            urlQuery: (string) $row['url_query'],
            urlFragment: (string) $row['url_fragment'],
            urlRewriteName: (string) $row['url_rewrite_name'],
            url: (string) $row['url'],
            active: (bool) $row['active'],
            sortOrder: (int) $row['sort_order'],
        );
    }
}
