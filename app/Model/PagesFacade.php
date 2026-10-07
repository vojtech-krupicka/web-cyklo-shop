<?php declare(strict_types=1);

namespace App\Model;

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

    public function createHomepage(): PageEntity
    {
        $data = [
            'heading' => 'Homepage',
            'seo_title' => 'Homepage',
            'seo_keywords' => '',
            'seo_description' => '',
            'content' => '',
            'is_homepage' => true,
            'allow_comments' => false,
            'created' => new \DateTime(),
            'modified' => new \DateTime(),
        ];
        $this->dbconn->table('pages')->insert($data);
        return $this->getHomepage();
    }

    public function persist(PageEntity $item): void
    {
        $data = [
            'heading' => $item->heading,
            'seo_title' => $item->seoTitle,
            'seo_keywords' => $item->seoKeywords,
            'seo_description' => $item->seoDescription,
            'content' => $item->content,
            'is_homepage' => $item->isHomepage,
            'allow_comments' => $item->allowComments,
            'created' => $item->created,
            'modified' => $item->modified,
        ];

        if ($item->id === null) {
            $row = $this->dbconn->table('pages')->insert($data);
            $item->id = (int) $row->id;
        } else {
            $this->dbconn->table('pages')->where('id', $item->id)->update($data);
        }
    }

    public function delete(PageEntity $page): void
    {
        $this->dbconn->table('pages')->where('id', $page->id)->delete();
    }
}
