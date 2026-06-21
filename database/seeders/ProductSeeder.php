<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('categories') || ! Schema::hasTable('products')) {
            $this->command?->warn('Can bang categories va products truoc khi seed san pham.');

            return;
        }

        $now = now();

        $categories = [
            ['name' => 'Club Jersey', 'slug' => 'club-jersey'],
            ['name' => 'National Team', 'slug' => 'national-team'],
            ['name' => 'Limited Edition', 'slug' => 'limited-edition'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'status' => true,
                    'parent_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $categoryIds = DB::table('categories')
            ->whereIn('slug', collect($categories)->pluck('slug')->all())
            ->pluck('id', 'slug');

$products = [
    [
        'category_slug' => 'club-jersey',
        'name' => 'Manchester United Home Jersey 2025/26',
        'slug' => 'manchester-united-home-2025-26',
        'thumbnail' => 'https://supersports.com.vn/cdn/shop/files/JI7428-6_1024x1024.jpg?v=1770856689',
        'base_price' => 899000,
        'discount_price' => 749000,
        'description' => 'Áo sân nhà Manchester United mùa 2025/26, form slim fit, chất liệu thoáng khí, phù hợp thi đấu và mặc thường ngày.',
    ],
    [
        'category_slug' => 'club-jersey',
        'name' => 'Real Madrid Home Jersey 2025/26',
        'slug' => 'real-madrid-home-2025-26',
        'thumbnail' => 'https://vanauthentic.com/watermark/product/750x750x2/upload/product/519585a21e7e467a8c2cb0291f8ab88c_9705.jpeg',
        'base_price' => 950000,
        'discount_price' => 799000,
        'description' => 'Áo sân nhà Real Madrid với tông trắng đặc trưng, thiết kế cổ polo hiện đại, logo thêu sắc nét.',
    ],
    [
        'category_slug' => 'club-jersey',
        'name' => 'PSG Away Jersey 2025/26',
        'slug' => 'psg-away-jersey-2025-26',
        'thumbnail' => 'https://aobongda.net/Pic/images/Module/as%20roma/as%20roma/juventus/inter%20milan/bayern/dortmund/psg/3.png',
        'base_price' => 820000,
        'discount_price' => null,
        'description' => 'Áo sân khách Paris Saint-Germain màu navy cá tính, dễ phối đồ, phù hợp cả đá bóng và streetwear.',
    ],
    [
        'category_slug' => 'national-team',
        'name' => 'Argentina Home Jersey 2024',
        'slug' => 'argentina-home-2024',
        'thumbnail' => 'https://vanauthentic.com/watermark/product/750x750x2/upload/product/bce60cd31eae4cafbee4f305ea625ee4_9745.jpeg',
        'base_price' => 750000,
        'discount_price' => 690000,
        'description' => 'Áo đội tuyển Argentina với sọc xanh trắng huyền thoại, chất liệu nhẹ, thấm hút mồ hôi tốt.',
    ],
    [
        'category_slug' => 'national-team',
        'name' => 'Brazil Home Jersey 2024',
        'slug' => 'brazil-home-2024',
        'thumbnail' => 'https://thethaokimthanh.vn/images/products/207/ao_dau_tuyen_brazil_world_cup_2014.jpg',
        'base_price' => 890000,
        'discount_price' => null,
        'description' => 'Áo đội tuyển Brazil màu vàng truyền thống, thiết kế năng động, phù hợp thi đấu cường độ cao.',
    ],
    [
        'category_slug' => 'limited-edition',
        'name' => 'Japan Sakura Limited Edition Jersey',
        'slug' => 'japan-sakura-limited-edition',
        'thumbnail' => 'https://vanauthentic.com/watermark/product/750x750x2/upload/product/86b5c1b41a6d4b63b5bf4fcf93570b7e_7123.jpeg',
        'base_price' => 990000,
        'discount_price' => 849000,
        'description' => 'Phiên bản giới hạn đội tuyển Nhật Bản với họa tiết hoa anh đào độc đáo, chất liệu cao cấp.',
    ],
];
        foreach ($products as $product) {
            DB::table('products')->updateOrInsert(
                ['slug' => $product['slug']],
                [
                    'category_id' => $categoryIds[$product['category_slug']],
                    'name' => $product['name'],
                    'status' => true,
                    'thumbnail' => $product['thumbnail'],
                    'description' => $product['description'],
                    'base_price' => $product['base_price'],
                    'discount_price' => $product['discount_price'],
                    'view_count' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
