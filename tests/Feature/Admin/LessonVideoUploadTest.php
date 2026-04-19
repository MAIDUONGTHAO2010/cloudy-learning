<?php

namespace Tests\Feature\Admin;

use App\Enums\User\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonVideoUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_request_presigned_video_upload_url(): void
    {
        $this->withoutMiddleware();

        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $response = $this->actingAs($admin)->postJson('/admin/api/lessons/presign-upload', [
            'file_name' => 'demo-video.mp4',
            'content_type' => 'video/mp4',
            'file_size' => 500 * 1024 * 1024,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'upload_url',
                'headers',
                'path',
                'video_url',
                'max_file_size',
            ]);
    }

    public function test_admin_upload_request_rejects_files_over_one_point_five_gb(): void
    {
        $this->withoutMiddleware();

        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $response = $this->actingAs($admin)->postJson('/admin/api/lessons/presign-upload', [
            'file_name' => 'huge-video.mp4',
            'content_type' => 'video/mp4',
            'file_size' => 1610612737,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file_size']);
    }
}
