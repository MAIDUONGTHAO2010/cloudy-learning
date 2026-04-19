<?php

namespace Tests\Feature\Admin;

use App\Enums\User\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseThumbnailUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_request_presigned_course_thumbnail_upload_url(): void
    {
        $this->withoutMiddleware();

        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $response = $this->actingAs($admin)->postJson('/admin/api/courses/presign-thumbnail', [
            'file_name' => 'thumbnail.png',
            'content_type' => 'image/png',
            'file_size' => 1024 * 1024,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'upload_url',
                'headers',
                'path',
                'thumbnail_url',
                'max_file_size',
            ]);
    }
}
