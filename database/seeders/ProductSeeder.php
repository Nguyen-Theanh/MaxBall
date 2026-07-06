<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::whereNotNull('parent_id')->get();

        if ($categories->isEmpty()) {
            $this->command?->warn('Không tìm thấy danh mục con nào. Hãy chạy CategorySeeder trước!');
            return;
        }

        $now = now();

        foreach ($categories as $category) {
            for ($i = 1; $i <= 3; $i++) {
                $name = 'Áo đấu ' . $category->name . ' Premium 0' . $i;
                
                Product::create([
                    'category_id'     => $category->id,
                    'name'            => $name,
                    'slug'            => Str::slug($name) . '-' . uniqid(),
                    'description'     => 'Mẫu ' . $name . ' sở hữu chất liệu vải thun lạnh cao cấp, co giãn 4 chiều, siêu thấm hút mồ hôi. Form dáng chuẩn thi đấu thể thao mang lại sự thoải mái tối đa.',
                    'base_price'      => rand(199, 399) * 1000,
                    'discount_price'  => rand(0, 1) ? rand(129, 179) * 1000 : null,
                    'status'          => 1,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);
            }
        }
        
        $this->command?->info('Đã tự động bơm sản phẩm mẫu thành công!');
    }
}