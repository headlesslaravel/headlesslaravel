<?php

namespace HeadlessLaravel\Notifications\Tests;

use Illuminate\Support\Facades\Route;
use HeadlessLaravel\Notifications\Tests\Fixtures\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp():void
    {
        parent::setUp();

        Route::get('login', 'LoginController@index')->name('login');
    }

    public function test_must_be_authenticated()
    {
        $this->get('/notifications')->assertRedirect('login');
    }

    public function test_listing_all_notifications()
    {
        $user = $this->authUser();

        DatabaseNotification::create([
            'id' => '123',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => 'invoice',
            'data' => [
                'invoice_amount' => 100
            ]
        ]);

        DatabaseNotification::create([
            'id' => '456',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => 'invoice',
            'read_at' => now(),
            'data' => [
                'invoice_amount' => 100
            ]
        ]);

        $this->get('/notifications') ->assertJsonPath('total', 2);
    }

    public function test_listing_unread_notifications()
    {
        $user = $this->authUser();

        $expected = DatabaseNotification::create([
            'id' => '123',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => 'invoice',
            'data' => [
                'invoice_amount' => 100
            ]
        ]);

        $rejected = DatabaseNotification::create([
            'id' => 'read',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => 'invoice',
            'read_at' => now(),
            'data' => [
                'invoice_amount' => 100
            ]
        ]);

        $this->get('/notifications/unread')
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.id', $expected->id)
            ->assertJsonPath('data.0.notifiable_id', "{$user->id}")
            ->assertJsonPath('data.0.type', 'invoice')
            ->assertJsonPath('data.0.data.invoice_amount', 100);
    }

    public function test_listing_read_notifications()
    {
        $user = $this->authUser();

        $notification = DatabaseNotification::create([
            'id' => '123',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => 'invoice',
            'data' => [],
            'read_at' => now()
        ]);

        $this->get('/notifications/read')
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.id', $notification->id);

        $this->get('/notifications/unread')
            ->assertJsonPath('total', 0);
    }

    public function test_notification_count()
    {
        $user = $this->authUser();

        DatabaseNotification::create([
            'id' => '123',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => 'invoice',
            'data' => [
                'invoice_amount' => 100
            ]
        ]);

        $this->get('/notifications/count')
            ->assertJsonPath('unread', 1);
    }

    public function test_mark_as_read()
    {
        $user = $this->authUser();

        $notification = DatabaseNotification::create([
            'id' => '123',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => 'invoice',
            'data' => [
                'invoice_amount' => 100
            ]
        ]);

        $this->assertNull($notification->read_at);

        $this->post('/notifications/123/mark-as-read');

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_deleting_a_notification()
    {
        $user = $this->authUser();

        $notification = DatabaseNotification::create([
            'id' => '123',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => 'invoice',
            'data' => [
                'invoice_amount' => 100
            ]
        ]);

        $this->assertNull($notification->read_at);

        $this->delete('/notifications/123');

        $this->assertEquals(0, DatabaseNotification::count());
    }

    public function test_clearing_notifications()
    {
        $user = $this->authUser();

        DatabaseNotification::create([
            'id' => '123',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => 'invoice',
            'data' => []
        ]);

        DatabaseNotification::create([
            'id' => '345',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => 'invoice',
            'read_at' => now(),
            'data' => [],
        ]);

        $this->post('/notifications/clear')
            ->assertJsonPath('deleted', 2)
            ->assertJsonPath('success', true);

        $this->assertEquals(0, DatabaseNotification::count());
    }
}
