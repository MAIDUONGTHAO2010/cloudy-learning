<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Programming (IT & Software)',
                'icon' => 'fas fa-code',
                'children' => [
                    ['name' => 'Web Development', 'icon' => 'fas fa-globe'],
                    ['name' => 'Mobile Development', 'icon' => 'fas fa-mobile-alt'],
                    ['name' => 'Data Science', 'icon' => 'fas fa-database'],
                    ['name' => 'Game Development', 'icon' => 'fas fa-gamepad'],
                ],
            ],
            [
                'name' => 'Business',
                'icon' => 'fas fa-chart-line',
                'children' => [
                    ['name' => 'Entrepreneurship', 'icon' => 'fas fa-lightbulb'],
                    ['name' => 'Human Resources Management', 'icon' => 'fas fa-users'],
                    ['name' => 'Finance & Accounting', 'icon' => 'fas fa-calculator'],
                ],
            ],
            [
                'name' => 'Design',
                'icon' => 'fas fa-paint-brush',
                'children' => [
                    ['name' => 'Graphic Design', 'icon' => 'fas fa-vector-square'],
                    ['name' => 'UI/UX Design', 'icon' => 'fas fa-desktop'],
                    ['name' => 'Video Production', 'icon' => 'fas fa-video'],
                ],
            ],
            [
                'name' => 'Languages',
                'icon' => 'fas fa-language',
                'children' => [
                    ['name' => 'English', 'icon' => 'fas fa-english-badge'],
                    ['name' => 'Japanese', 'icon' => 'fas fa-torii-gate'],
                    ['name' => 'Korean', 'icon' => 'fas fa-korea'],
                ],
            ],
        ];

        foreach ($categories as $index => $item) {
            // Create parent category
            $parent = Category::create([
                'name' => $item['name'],
                'slug' => Str::slug($item['name']),
                // 'icon' => $item['icon'],
                'order' => $index,
                'parent_id' => null,
                'is_active' => true,
            ]);

            // Create child categories
            if (isset($item['children'])) {
                foreach ($item['children'] as $childIndex => $child) {
                    Category::create([
                        'name' => $child['name'],
                        'slug' => Str::slug($child['name']),
                        // 'icon' => $child['icon'],
                        'order' => $childIndex,
                        'parent_id' => $parent->id, // Attach to the parent just created
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}
