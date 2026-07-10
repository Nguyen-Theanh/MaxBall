<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('name')->nullable()->after('product_id');
            $table->integer('base_price')->default(0)->after('sku');
            $table->integer('discount_price')->nullable()->after('base_price');
        });

        // Migrate data
        DB::statement("UPDATE product_variants SET name = CONCAT_WS(' - ', color, size), base_price = price");

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['size', 'color', 'price']);
            $table->dropUnique('product_variants_sku_unique');
        });

        // Make sku nullable. using DB statement to avoid needing doctrine/dbal
        DB::statement("ALTER TABLE product_variants MODIFY sku VARCHAR(255) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->decimal('price', 10, 2)->default(0);
        });

        DB::statement("UPDATE product_variants SET price = base_price");

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['name', 'base_price', 'discount_price']);
            $table->unique('sku');
        });
        
        DB::statement("ALTER TABLE product_variants MODIFY sku VARCHAR(255) NOT NULL");
    }
};
