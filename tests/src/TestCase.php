<?php

declare(strict_types=1);

namespace Tests;

use Tests\Models\User;
use Laravelcm\Subscriptions\SubscriptionServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TestCase extends Orchestra
{
    // use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            SubscriptionServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('auth.providers.users.model', User::class);
    }
}
