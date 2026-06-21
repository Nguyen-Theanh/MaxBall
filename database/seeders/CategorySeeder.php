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
            'Giày Đá Bóng' => [
                'Nike',
                'Adidas',
                'F1'
            ],
            'Áo Bóng Đá' => [
                'Áo CLB Thế Giới',
                'Áo Quốc Gia',
                'Áo Không Logo'
            ],
            'Phụ Kiện Bóng Đá' => [
                'Áo lót & Chống nắng',
                'Banh',
                'Bó gót & bó gối',
                'Găng tay thủ môn',
                'RTE bảo vệ ống chân',
                'Vớ'
            ]
        ];

        foreach ($categories as $parentName => $children) {
            // 1. Tạo danh mục cha
            $parent = Category::create([
                'name' => $parentName,
                'slug' => Str::slug($parentName),
                'status' => 1,
                'parent_id' => null, // Là danh mục gốc
            ]);

            // 2. Tạo các danh mục con và gán parent_id
            foreach ($children as $childName) {
                Category::create([
                    'name' => $childName,
                    'slug' => Str::slug($childName),
                    'status' => 1,
                    'parent_id' => $parent->id, // Trỏ về ID của danh mục cha
                ]);
            }
        }
    }
}