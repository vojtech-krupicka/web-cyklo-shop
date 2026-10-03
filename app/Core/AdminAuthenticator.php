<?php declare(strict_types=1);

namespace App\Core;

use Nette\Security;

final class AdminAuthenticator implements Security\Authenticator
{
    public function __construct(
        private \Model\MembersFacade $membersFacade,
        private Security\Passwords $passwords,
    ) {}

    public function authenticate(string $username, string $password): AdminIdentity
    {
        $member = $this->membersFacade->getByUsername($username);
        if (!$member) {
            throw new Security\AuthenticationException('User not found.');
        }

        if (!$this->passwords->verify($password, $member->password)) {
            throw new Security\AuthenticationException('Invalid password.');
        }

        $member->lastLogin = new \DateTime();
        $this->membersFacade->persist($member);

        return AdminIdentity::fromMember([$member->role], $member);
    }
}
