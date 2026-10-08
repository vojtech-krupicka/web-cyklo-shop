<?php declare(strict_types=1);

namespace Tests;

use App\Bootstrap;
use Nette\Database\Connection;
use Nette\DI\Container;
use PHPUnit\Framework\TestCase;

/**
 * Base class for tests that use the real (test) database.
 * Every test runs inside a transaction that is rolled back afterwards,
 * so tests can write freely and the mock data from the migrations stays intact.
 */
abstract class DatabaseTestCase extends TestCase
{
    private static ?Container $container = null;

    protected static function container(): Container
    {
        return self::$container ??= (new Bootstrap)->bootWebApplication();
    }

    protected function setUp(): void
    {
        $connection = self::container()->getByType(Connection::class);
        $database = $connection->query('SELECT DATABASE()')->fetchField();
        if (!str_ends_with((string) $database, '_test')) {
            self::fail("Refusing to run database tests against '$database' (name must end in _test).");
        }

        $connection->beginTransaction();
    }

    protected function tearDown(): void
    {
        self::container()->getByType(Connection::class)->rollBack();
    }
}
