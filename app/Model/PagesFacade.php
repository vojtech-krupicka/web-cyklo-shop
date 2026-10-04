<?php declare(strict_types=1);

namespace Model;

final class PageEntity extends BaseEntity
{
    public function __construct(
        public readonly int $id,
        public readonly string $heading,
        public readonly string $seoTitle,
        public readonly string $seoKeywords,
        public readonly string $seoDescription,
        public readonly string $content,
        public readonly bool $isHomepage,
        public readonly bool $allowComments,
        public \DateTime $created,
        public \DateTime $modified,
    ) {}

    public static function fromActiveRow(\Nette\Database\Table\ActiveRow $row): self
    {
        return new self(
            id: (int) $row->id,
            heading: (string) $row->heading,
            seoTitle: (string) $row->seo_title,
            seoKeywords: (string) $row->seo_keywords,
            seoDescription: (string) $row->seo_description,
            content: (string) $row->content,
            isHomepage: (bool) $row->is_homepage,
            allowComments: (bool) $row->allow_comments,
            created: $row->created,
            modified: $row->modified,
        );
    }
}

final class PagesFacade extends BaseFacade
{
    public function getPageById(int $id): ?PageEntity
    {
        $row = $this->dbconn->table('pages')->get($id);
        return $row ? PageEntity::fromActiveRow($row) : null;
    }

    public function getHomepage(): ?PageEntity
    {
        $row = $this->dbconn->table('pages')->where('is_homepage', true)->fetch();
        return $row ? PageEntity::fromActiveRow($row) : null;
    }
}
