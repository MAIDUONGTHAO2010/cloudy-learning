<?php

namespace Tests\Feature;

use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_send_contact_message_and_admin_receives_notification(): void
    {
        $response = $this->postJson('/api/contact', [
            'name' => 'Nguyen Van A',
            'email' => 'nguyenvana@example.com',
            'subject' => 'General inquiry',
            'message' => 'I need help with course enrollment.',
        ]);

        $response->assertCreated()
            ->assertJson([
                'message' => 'Message sent successfully.',
            ]);

        $this->assertDatabaseHas('notifications', [
            'target' => 'admin',
            'type' => 'contact_message',
            'title' => 'New contact message',
            'is_read' => false,
        ]);

        $notification = Notification::query()->latest('id')->first();

        $this->assertNotNull($notification);
        $this->assertStringContainsString('Nguyen Van A', $notification->body);
        $this->assertStringContainsString('nguyenvana@example.com', $notification->body);
        $this->assertStringContainsString('I need help with course enrollment.', $notification->body);
    }
}
