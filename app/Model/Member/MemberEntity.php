<?php declare(strict_types=1);

namespace App\Model\Member;

use App\Model\BaseEntity;

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

    public static function fromActiveRow(\Nette\Database\Table\ActiveRow $row): static
    {
        return new self(
            id: (int) $row['id'],
            password: (string) $row['password'],
            username: (string) $row['nickname'],
            firstname: (string) $row['firstname'],
            surname: (string) $row['surname'],
            email: (string) $row['email'],
            role: (string) $row['role'],
            active: (bool) $row['active'],
            lastLogin: $row['last_logon'],
        );
    }
}
