<?php declare(strict_types=1);

namespace App\Core;

use App\Model;
use Nette\Security;

final class AdminIdentity implements Security\IIdentity
{
    public function __construct(
        public readonly int $id,
        public array $roles,
        public readonly string $username,
        public readonly string $firstname,
        public readonly string $surname,
        public readonly string $email,
        public readonly string $role,
        public readonly bool $active,
        public \DateTime $lastLogin,
    ) {}

    public static function fromMember(array $roles, Model\Member\MemberEntity $member): self
    {
        return new self(
            id: (int) $member->id,
            roles: $roles,
            username: (string) $member->username,
            firstname: (string) $member->firstname,
            surname: (string) $member->surname,
            email: (string) $member->email,
            role: (string) $member->role,
            active: (bool) $member->active,
            lastLogin: $member->lastLogin,
        );
    }

    /**
     * Returns the ID of user.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Sets a list of roles that the user is a member of.
     * @param  list<string>  $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * Returns a list of roles that the user is a member of.
     * @return list<string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }
}
