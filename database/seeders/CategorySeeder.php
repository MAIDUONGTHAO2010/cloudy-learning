<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
                'name' => 'Lập trình (IT & Software)',
                'icon' => 'fas fa-code',
                'children' => [
                    ['name' => 'Lập trình Web', 'icon' => 'fas fa-globe'],
                    ['name' => 'Lập trình Mobile', 'icon' => 'fas fa-mobile-alt'],
                    ['name' => 'Khoa học dữ liệu', 'icon' => 'fas fa-database'],
                    ['name' => 'Game Development', 'icon' => 'fas fa-gamepad'],
                ]
            ],
            [
                'name' => 'Kinh doanh (Business)',
                'icon' => 'fas fa-chart-line',
                'children' => [
                    ['name' => 'Khởi nghiệp', 'icon' => 'fas fa-lightbulb'],
                    ['name' => 'Quản trị nhân sự', 'icon' => 'fas fa-users'],
                    ['name' => 'Tài chính kế toán', 'icon' => 'fas fa-calculator'],
                ]
            ],
            [
                'name' => 'Thiết kế (Design)',
                'icon' => 'fas fa-paint-brush',
                'children' => [
                    ['name' => 'Thiết kế đồ họa', 'icon' => 'fas fa-vector-square'],
                    ['name' => 'Thiết kế UI/UX', 'icon' => 'fas fa-desktop'],
                    ['name' => 'Dựng phim & Video', 'icon' => 'fas fa-video'],
                ]
            ],
            [
                'name' => 'Ngoại ngữ (Languages)',
                'icon' => 'fas fa-language',
                'children' => [
                    ['name' => 'Tiếng Anh', 'icon' => 'fas fa-english-badge'],
                    ['name' => 'Tiếng Nhật', 'icon' => 'fas fa-torii-gate'],
                    ['name' => 'Tiếng Hàn', 'icon' => 'fas fa-korea'],
                ]
            ],
        ];

        foreach ($categories as $index => $item) {
            // Tạo danh mục cha
            $parent = Category::create([
                'name' => $item['name'],
                'slug' => Str::slug($item['name']),
                // 'icon' => $item['icon'],
                'order' => $index,
                'parent_id' => null,
                'is_active' => true,
            ]);

            // Tạo danh mục con
            if (isset($item['children'])) {
                foreach ($item['children'] as $childIndex => $child) {
                    Category::create([
                        'name' => $child['name'],
                        'slug' => Str::slug($child['name']),
                        // 'icon' => $child['icon'],
                        'order' => $childIndex,
                        'parent_id' => $parent->id, // Gắn ID của thằng cha vừa tạo
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}
