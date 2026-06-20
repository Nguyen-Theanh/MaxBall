<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
Schema::create('coupons', function (Blueprint $table) {
    $table->id();
    $table->string('code')->unique();
    $table->string('discount_type'); // percent / fixed
    $table->decimal('discount_value', 10, 2);
    $table->decimal('min_order_value', 10, 2)->nullable();
    $table->integer('usage_limit')->nullable();
    $table->integer('used_count')->default(0);
    $table->timestamp('expires_at')->nullable();
});    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
