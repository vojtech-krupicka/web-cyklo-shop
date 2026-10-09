<?php declare(strict_types=1);

namespace Tests\Core;

use App\Core\AdminAuthenticator;
use App\Core\AdminIdentity;
use App\Model\Member\MemberEntity;
use App\Model\Member\MemberFacade;
use Nette\Security\AuthenticationException;
use Nette\Security\Passwords;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(AdminAuthenticator::class)]
final class AdminAuthenticatorTest extends TestCase
{
    private const PASSWORD = 'aaa';

    private Passwords $passwords;
    private MemberFacade&MockObject $membersFacade;
    private AdminAuthenticator $authenticator;

    protected function setUp(): void
    {
        // low cost keeps the hashing fast in tests
        $this->passwords = new Passwords(PASSWORD_BCRYPT, ['cost' => 4]);
        $this->membersFacade = $this->createMock(MemberFacade::class);
        $this->authenticator = new AdminAuthenticator($this->membersFacade, $this->passwords);
    }

    public function testUnknownUserIsRejected(): void
    {
        $this->membersFacade->expects($this->once())->method('getByUsername')->with('nobody')->willReturn(null);
        $this->membersFacade->expects($this->never())->method('persist');

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessageIs('User not found.');

        $this->authenticator->authenticate('nobody', self::PASSWORD);
    }

    public function testWrongPasswordIsRejected(): void
    {
        $this->membersFacade->method('getByUsername')->willReturn($this->createMember());
        $this->membersFacade->expects($this->never())->method('persist');

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessageIs('Invalid password.');

        $this->authenticator->authenticate('test-admin', 'wrong-password');
    }

    public function testSuccessfulLoginReturnsIdentityAndStoresLastLogin(): void
    {
        $member = $this->createMember();
        $this->membersFacade->expects($this->once())->method('getByUsername')->with('test-admin')->willReturn($member);
        $this
            ->membersFacade
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(fn(MemberEntity $m) => $m === $member && $m->lastLogin instanceof \DateTime));

        $identity = $this->authenticator->authenticate('test-admin', self::PASSWORD);

        $this->assertInstanceOf(AdminIdentity::class, $identity);
        $this->assertSame(1, $identity->getId());
        $this->assertSame('test-admin', $identity->username);
        $this->assertSame(['admin'], $identity->getRoles());
        $this->assertNotNull($identity->lastLogin);
    }

    private function createMember(): MemberEntity
    {
        return new MemberEntity(
            id: 1,
            password: $this->passwords->hash(self::PASSWORD),
            username: 'test-admin',
            firstname: 'Test',
            surname: 'Admin',
            email: 'test-admin@example.com',
            role: 'admin',
            active: true,
            lastLogin: null,
        );
    }
}
