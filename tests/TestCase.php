<?php

namespace Bashtannik\Fasti\Tests;

use Bashtannik\Fasti\Providers\FastiServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getEnvironmentSetUp($app): void
    {
        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $migration = include __DIR__.'/../database/migrations/2024_09_28_000000_create_scheduled_jobs_table.php';
        $migration->up();
    }

    protected function getPackageProviders($app): array
    {
        return [
            FastiServiceProvider::class,
        ];
    }
}
