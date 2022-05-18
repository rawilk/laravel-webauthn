<?php

namespace Rawilk\Webauthn\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Rawilk\Webauthn\WebauthnServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Rawilk\\Webauthn\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            WebauthnServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $testMigrations = [
            'create_users_table.php',
        ];

        foreach ($testMigrations as $path) {
            $migration = include __DIR__ . '/Support/database/migrations/' . $path;
            $migration->up();
        }

        $migration = include __DIR__ . '/../database/migrations/create_webauthn_table.php.stub';
        $migration->up();
    }
}
