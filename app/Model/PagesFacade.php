<?php declare(strict_types=1);

namespace Model;

final class PagesFacade
{


	public function __construct(
        private \Nette\Database\Explorer $dbconn
    )
	{ }


    public function getHomepage(): ?\Nette\Database\Table\ActiveRow
    {
        return $this->dbconn->table('pages')->where('is_homepage', true)->fetch();
    }

}
