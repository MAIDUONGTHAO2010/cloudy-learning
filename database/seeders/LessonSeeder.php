<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LessonSeeder extends Seeder
{
    public function run(): void
    {
        $lessonsByCourse = [
            'Web Development with Laravel' => [
                ['title' => 'Introduction to Laravel & Environment Setup', 'duration' => 20, 'order' => 0],
                ['title' => 'Basic Routing & Controllers',                 'duration' => 30, 'order' => 1],
                ['title' => 'Blade Template Engine',                       'duration' => 25, 'order' => 2],
                ['title' => 'Eloquent ORM & Migration',                    'duration' => 40, 'order' => 3],
                ['title' => 'Authentication & Authorization',              'duration' => 35, 'order' => 4],
            ],
            'Vue.js 3 & TypeScript' => [
                ['title' => 'Introduction to Vue 3 & Vite',    'duration' => 20, 'order' => 0],
                ['title' => 'Composition API & ref/reactive',  'duration' => 35, 'order' => 1],
                ['title' => 'Component & Props',               'duration' => 30, 'order' => 2],
                ['title' => 'Vue Router',                      'duration' => 25, 'order' => 3],
                ['title' => 'TypeScript with Vue 3',           'duration' => 40, 'order' => 4],
            ],
            'Data Science with Python' => [
                ['title' => 'Python Basics for Data Science',          'duration' => 30, 'order' => 0],
                ['title' => 'NumPy & Pandas',                          'duration' => 45, 'order' => 1],
                ['title' => 'Data Visualization with Matplotlib',      'duration' => 35, 'order' => 2],
                ['title' => 'Machine Learning with Scikit-learn',      'duration' => 60, 'order' => 3],
            ],
            'Modern UI/UX Design' => [
                ['title' => 'UI Design Principles',       'duration' => 25, 'order' => 0],
                ['title' => 'Figma Basics',               'duration' => 40, 'order' => 1],
                ['title' => 'Design System & Components', 'duration' => 35, 'order' => 2],
                ['title' => 'Prototype & User Testing',   'duration' => 30, 'order' => 3],
            ],
        ];

        foreach ($lessonsByCourse as $courseTitle => $lessons) {
            $course = Course::where('title', $courseTitle)->first();

            if (! $course) {
                continue;
            }

            foreach ($lessons as $lessonData) {
                $slug = Str::slug($lessonData['title']);
                $original = $slug;
                $count = 1;

                while (Lesson::where('slug', $slug)->exists()) {
                    $slug = $original.'-'.$count++;
                }

                Lesson::create([
                    'course_id' => $course->id,
                    'title' => $lessonData['title'],
                    'slug' => $slug,
                    'duration' => $lessonData['duration'],
                    'order' => $lessonData['order'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
