<?php declare(strict_types=1);

namespace App\Model\Page;

use App\Model\BaseEntity;

final class PageEntity extends BaseEntity
{
    public function __construct(
        public ?int $id = null,
        public string $heading = '',
        public string $seoTitle = '',
        public string $seoKeywords = '',
        public string $seoDescription = '',
        public string $content = '',
        public bool $isHomepage = false,
        public bool $allowComments = false,
        public \DateTime $created = new \DateTime(),
        public ?\DateTime $modified = new \DateTime(),
    ) {}

    public static function fromActiveRow(\Nette\Database\Table\ActiveRow $row): static
    {
        return new self(
            id: (int) $row['id'],
            heading: (string) $row['heading'],
            seoTitle: (string) $row['seo_title'],
            seoKeywords: (string) $row['seo_keywords'],
            seoDescription: (string) $row['seo_description'],
            content: (string) $row['content'],
            isHomepage: (bool) $row['is_homepage'],
            allowComments: (bool) $row['allow_comments'],
            created: $row['created'],
            modified: $row['modified'],
        );
    }
}
