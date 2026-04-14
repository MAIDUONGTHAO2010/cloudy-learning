<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\CourseSeeder;
use Database\Seeders\LessonSeeder;
use Database\Seeders\QuizSeeder;
use Database\Seeders\CourseReviewSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'content@cloudylearning.com',
        //     'password' => 'Aa@123456789'
        // ]);

        $this->call([
            // CategorySeeder::class,
            // CourseSeeder::class,
            // LessonSeeder::class,
            // QuizSeeder::class,
            CourseReviewSeeder::class,
        ]);
    }
}
