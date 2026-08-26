<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->text('content');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('promotion_announcements')->insert([
            'id' => 1,
            'title' => 'Ưu đãi dành cho bạn',
            'content' => 'Đánh giá sản phẩm sau khi hoàn thành đơn hàng để nhận voucher freeship. Theo dõi MaxBall để cập nhật thêm các chương trình khuyến mãi mới nhất.',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_announcements');
    }
};
