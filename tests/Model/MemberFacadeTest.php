<?php declare(strict_types=1);

namespace Tests\Model;

use App\Model\Member\MemberEntity;
use App\Model\Member\MemberFacade;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\DatabaseTestCase;

#[CoversClass(MemberFacade::class)]
final class MemberFacadeTest extends DatabaseTestCase
{
    private MemberFacade $facade;

    protected function setUp(): void
    {
        parent::setUp();
        $this->facade = self::container()->getByType(MemberFacade::class);
    }

    public function testGetById(): void
    {
        $member = $this->facade->getById(1);

        $this->assertNotNull($member);
        $this->assertSame('test-admin', $member->username);
        $this->assertSame('Admin', $member->surname);
        $this->assertSame('admin', $member->role);
        $this->assertTrue($member->active);
        $this->assertNull($member->lastLogin);
        $this->assertStringStartsWith('$2y$', $member->password, 'stored as a bcrypt hash');
    }

    public function testUnknownIdReturnsNull(): void
    {
        $this->assertNull($this->facade->getById(9999));
    }

    public function testGetByEmail(): void
    {
        $member = $this->facade->getByEmail('test-editor@example.com');

        $this->assertNotNull($member);
        $this->assertSame(2, $member->id);
        $this->assertSame('editor', $member->role);
        $this->assertNull($this->facade->getByEmail('nobody@example.com'));
    }

    public function testGetByUsernameFindsOnlyActiveMembers(): void
    {
        $member = $this->facade->getByUsername('test-editor');
        $this->assertNotNull($member);
        $this->assertSame(2, $member->id);

        $deactivated = new MemberEntity(
            id: $member->id,
            password: $member->password,
            username: $member->username,
            firstname: $member->firstname,
            surname: $member->surname,
            email: $member->email,
            role: $member->role,
            active: false,
            lastLogin: null,
        );
        $this->facade->persist($deactivated);

        $this->assertNull($this->facade->getByUsername('test-editor'), 'a deactivated member cannot sign in');
        $this->assertNotNull($this->facade->getById(2), 'but is still found by id');
    }

    public function testGetAllListsEveryoneWithTheMostRecentLoginFirst(): void
    {
        $this->assertCount(2, $this->facade->getAll());

        $editor = $this->facade->getById(2);
        $this->assertNotNull($editor);
        $editor->lastLogin = new \DateTime('2024-05-01 10:00:00');
        $this->facade->persist($editor);

        $all = $this->facade->getAll();

        $this->assertSame([2, 1], array_map(fn(MemberEntity $m) => $m->id, $all));
        $this->assertEquals(new \DateTime('2024-05-01 10:00:00'), $all[0]->lastLogin);
    }

    public function testPersistUpdatesTheMember(): void
    {
        $member = $this->facade->getById(1);
        $this->assertNotNull($member);

        $changed = new MemberEntity(
            id: $member->id,
            password: $member->password,
            username: $member->username,
            firstname: 'Změněné',
            surname: $member->surname,
            email: 'jiny@example.com',
            role: $member->role,
            active: $member->active,
            lastLogin: new \DateTime('2024-06-01 12:30:00'),
        );
        $this->facade->persist($changed);

        $reloaded = $this->facade->getById(1);
        $this->assertNotNull($reloaded);
        $this->assertSame('Změněné', $reloaded->firstname);
        $this->assertSame('jiny@example.com', $reloaded->email);
        $this->assertEquals(new \DateTime('2024-06-01 12:30:00'), $reloaded->lastLogin);
    }
}
