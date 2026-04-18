<?php

namespace Database\Seeders;

use App\Enums\User\UserRole;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo 2 instructor nếu chưa tồn tại
        $instructorA = User::firstOrCreate(
            ['email' => 'instructor.a@cloudylearning.com'],
            [
                'name' => 'Nguyen Van An',
                'password' => Hash::make('Aa@123456789'),
                'role' => UserRole::INSTRUCTOR,
            ]
        );

        $instructorB = User::firstOrCreate(
            ['email' => 'instructor.b@cloudylearning.com'],
            [
                'name' => 'Tran Thi Bich',
                'password' => Hash::make('Aa@123456789'),
                'role' => UserRole::INSTRUCTOR,
            ]
        );

        $courses = [
            [
                'user_id' => $instructorA->id,
                'title' => 'Lập trình Web với Laravel',
                'description' => 'Học Laravel từ cơ bản đến nâng cao, xây dựng ứng dụng web thực tế với PHP.',
                'is_active' => true,
                'order' => 0,
            ],
            [
                'user_id' => $instructorA->id,
                'title' => 'Vue.js 3 & TypeScript',
                'description' => 'Xây dựng giao diện người dùng hiện đại với Vue 3, Composition API và TypeScript.',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'user_id' => $instructorB->id,
                'title' => 'Khoa học dữ liệu với Python',
                'description' => 'Phân tích dữ liệu, machine learning và trực quan hóa dữ liệu bằng Python.',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'user_id' => $instructorB->id,
                'title' => 'Thiết kế UI/UX hiện đại',
                'description' => 'Nguyên tắc thiết kế giao diện, trải nghiệm người dùng và công cụ Figma.',
                'is_active' => false,
                'order' => 3,
            ],
        ];

        foreach ($courses as $item) {
            $slug = Str::slug($item['title']);
            $original = $slug;
            $count = 1;

            while (Course::where('slug', $slug)->exists()) {
                $slug = $original.'-'.$count++;
            }

            Course::create(array_merge($item, ['slug' => $slug]));
        }
    }
}
