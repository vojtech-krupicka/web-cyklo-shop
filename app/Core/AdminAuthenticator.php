<?php declare(strict_types=1);

namespace App\Core;

use Nette\Security;

final class AdminAuthenticator implements Security\Authenticator
{
    public function __construct(
        private \Model\MembersFacade $membersFacade,
        private Security\Passwords $passwords,
    ) {}

    public function authenticate(string $username, string $password): Security\SimpleIdentity
    {
        $row = $this->membersFacade->getByUsername($username);
        if (!$row) {
            throw new Security\AuthenticationException('User not found.');
        }

        if (!$this->passwords->verify($password, $row->password)) {
            throw new Security\AuthenticationException('Invalid password.');
        }

        return new Security\SimpleIdentity($row->id, $row->role, $row->toArray());
    }
}
