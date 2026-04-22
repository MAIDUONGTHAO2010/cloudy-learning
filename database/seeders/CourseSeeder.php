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
        // Create 2 instructors if they don't exist yet
        $instructorA = User::firstOrCreate(
            ['email' => 'instructor@cloudylearning.com'],
            [
                'name' => 'Nguyen Van An',
                'password' => Hash::make('Aa@123456789'),
                'role' => UserRole::INSTRUCTOR,
            ]
        );

        $instructorB = User::firstOrCreate(
            ['email' => 'student@cloudylearning.com'],
            [
                'name' => 'Huynh Ngo Vu Binh',
                'password' => Hash::make('Aa@123456789'),
                'role' => UserRole::STUDENT,
            ]
        );

        $courses = [
            [
                'user_id' => $instructorA->id,
                'title' => 'Web Development with Laravel',
                'description' => 'Learn Laravel from beginner to advanced and build real-world web applications with PHP.',
                'is_active' => true,
                'order' => 0,
            ],
            [
                'user_id' => $instructorA->id,
                'title' => 'Vue.js 3 & TypeScript',
                'description' => 'Build modern user interfaces with Vue 3, Composition API, and TypeScript.',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'user_id' => $instructorB->id,
                'title' => 'Data Science with Python',
                'description' => 'Data analysis, machine learning, and data visualization with Python.',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'user_id' => $instructorB->id,
                'title' => 'Modern UI/UX Design',
                'description' => 'UI design principles, user experience, and the Figma tool.',
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
