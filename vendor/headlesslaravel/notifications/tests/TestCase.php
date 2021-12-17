<?php

namespace HeadlessLaravel\Notifications\Tests;

use HeadlessLaravel\Notifications\NotificationProvider;
use HeadlessLaravel\Notifications\Tests\Fixtures\Models\User;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            NotificationProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        include_once __DIR__ . '/Fixtures/Database/Migrations/create_users_table.php.stub';
        include_once __DIR__ . '/Fixtures/Database/Migrations/create_notifications_table.php.stub';

        (new \CreateUsersTable())->up();
        (new \CreateNotificationsTable())->up();
    }

    public function authUser()
    {
        $user = User::forceCreate([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => '$2y$10$MTibKZXWRvtO2gWpfpsngOp6FQXWUhHPTF9flhsaPdWvRtsyMUlC2',
        ]);

        $this->actingAs($user);

        return $user;
    }
}
