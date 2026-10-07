<?php declare(strict_types=1);

namespace Model;

final class MemberEntity extends BaseEntity
{
    public function __construct(
        public readonly int $id,
        public readonly string $password,
        public readonly string $username,
        public readonly string $firstname,
        public readonly string $surname,
        public readonly string $email,
        public readonly string $role,
        public readonly bool $active,
        public ?\DateTime $lastLogin,
    ) {}

    public static function fromActiveRow(\Nette\Database\Table\ActiveRow $row): self
    {
        return new self(
            id: (int) $row->id,
            password: (string) $row->password,
            username: (string) $row->nickname,
            firstname: (string) $row->firstname,
            surname: (string) $row->surname,
            email: (string) $row->email,
            role: (string) $row->role,
            active: (bool) $row->active,
            lastLogin: $row->last_logon,
        );
    }
}

final class MembersFacade extends BaseFacade
{
    public function getById(int $id): ?MemberEntity
    {
        $row = $this->dbconn->table('members')->get($id);
        return $row ? MemberEntity::fromActiveRow($row) : null;
    }

    public function getByUsername(string $username): ?MemberEntity
    {
        $row = $this->dbconn->table('members')->where('nickname', $username)->where('active', true)->fetch();
        return $row ? MemberEntity::fromActiveRow($row) : null;
    }

    public function getByEmail(string $email): ?MemberEntity
    {
        $row = $this->dbconn->table('members')->where('email', $email)->fetch();
        return $row ? MemberEntity::fromActiveRow($row) : null;
    }

    public function getAll(): array
    {
        $query = $this->dbconn->table('members')->order('last_logon DESC');
        return MemberEntity::fromSelection($query);
    }

    public function persist(MemberEntity $member): void
    {
        $this->dbconn->table('members')->where('id', $member->id)->update([
            'password' => $member->password,
            'nickname' => $member->username,
            'firstname' => $member->firstname,
            'surname' => $member->surname,
            'email' => $member->email,
            'role' => $member->role,
            'active' => $member->active,
            'last_logon' => $member->lastLogin->format('Y-m-d H:i:s'),
        ]);
    }
}
