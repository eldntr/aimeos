<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Notifications\Notification;

class DummyNotification extends Notification
{
    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Test',
            'message' => 'Hello World'
        ];
    }
}

class NotificationApiTest extends TestCase
{
    public function test_user_can_get_notifications()
    {
        $user = User::factory()->create();
        $user->notify(new DummyNotification());

        $response = $this->actingAs($user, 'sanctum')->get('/api/notifications');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data.data')
                 ->assertJsonPath('unread_count', 1);
    }

    public function test_user_can_mark_notification_as_read()
    {
        $user = User::factory()->create();
        $user->notify(new DummyNotification());
        
        $notification = $user->unreadNotifications->first();

        $response = $this->actingAs($user, 'sanctum')->patch("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200)
                 ->assertJsonPath('unread_count', 0);
                 
        $this->assertNotNull($user->fresh()->notifications->first()->read_at);
    }
}
