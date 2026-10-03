<?php declare(strict_types=1);

namespace Model;

final class MembersFacade
{
    public function __construct(
        private \Nette\Database\Explorer $dbconn
    ) {}

    public function getById(int $id): ?\Nette\Database\Table\ActiveRow
    {
        return $this->dbconn->table('members')->get($id);
    }

    public function getByUsername(string $username): ?\Nette\Database\Table\ActiveRow
    {
        return $this->dbconn->table('members')->where('nickname', $username)->where('active', true)->fetch();
    }

    public function getByEmail(string $email): ?\Nette\Database\Table\ActiveRow
    {
        return $this->dbconn->table('members')->where('email', $email)->fetch();
    }
}
