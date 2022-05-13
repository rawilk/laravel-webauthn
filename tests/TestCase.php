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
        // include_once __DIR__ . '/../database/migrations/create_laravel-webauthn_table.php.stub';
        // (new \CreatePackageTable())->up();
    }
}
