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
        // Lấy tất cả các danh mục con (những danh mục có parent_id như Nike, Adidas, Áo Quốc Gia...)
        $categories = Category::whereNotNull('parent_id')->get();

        // Nếu chưa chạy Seeder danh mục, đưa ra cảnh báo để tránh lỗi
        if ($categories->isEmpty()) {
            $this->command?->warn('Không tìm thấy danh mục con nào. Hãy chạy CategorySeeder trước!');
            return;
        }

        $now = now();

        foreach ($categories as $category) {
            // Mỗi danh mục con sẽ tự động sinh ra đúng 3 sản phẩm mẫu
            for ($i = 1; $i <= 3; $i++) {
                $name = 'Áo đấu ' . $category->name . ' Premium 0' . $i;
                
                Product::create([
                    'category_id'     => $category->id,
                    'name'            => $name,
                    'slug'            => Str::slug($name) . '-' . uniqid(), // Thêm chuỗi ngẫu nhiên để không trùng slug
                    'description'     => 'Mẫu ' . $name . ' sở hữu chất liệu vải thun lạnh cao cấp, co giãn 4 chiều, siêu thấm hút mồ hôi. Form dáng chuẩn thi đấu thể thao mang lại sự thoải mái tối đa.',
                    'base_price'      => rand(199, 399) * 1000, // Giá gốc ngẫu nhiên từ 199k đến 399k
                    'discount_price'  => rand(0, 1) ? rand(129, 179) * 1000 : null, // 50% cơ hội có giá giảm mẫu
                    'status'          => 1,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);
            }
        }
        
        $this->command?->info('Đã tự động bơm sản phẩm mẫu thành công!');
    }
}