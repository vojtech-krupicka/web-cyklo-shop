<?php declare(strict_types=1);

namespace App;

use Nette\Bootstrap\Configurator;
use Nette;

class Bootstrap
{
    private readonly Configurator $configurator;
    private readonly string $rootDir;

    public function __construct()
    {
        $this->rootDir = dirname(__DIR__);
        $this->configurator = new Configurator;
        $this->configurator->setTempDirectory($this->rootDir . '/temp');
    }

    public function bootWebApplication(): Nette\DI\Container
    {
        $this->initializeEnvironment();
        $this->setupContainer();
        return $this->configurator->createContainer();
    }

    public function initializeEnvironment(): void
    {
        $this->configurator->setDebugMode(getenv('NETTE_DEBUG') === '1');
        $this->configurator->enableTracy($this->rootDir . '/log');
    }

    private function setupContainer(): void
    {
        $configDir = $this->rootDir . '/config';
        $this->configurator->addConfig($configDir . '/common.neon');
        $this->configurator->addConfig($configDir . '/services.neon');

        $env = getenv('APP_ENV') ?: 'prod';
        $local = "$configDir/local.$env.neon";
        if (!is_file($local)) {
            throw new \RuntimeException("Missing config file '$local', copy 'config/local.example.neon' to it.");
        }
        $this->configurator->addConfig($local);
    }
}
