<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
            $table->foreign('product_variant_id')
                ->references('id')
                ->on('product_variants')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
            $table->foreign('product_variant_id')
                ->references('id')
                ->on('product_variants')
                ->cascadeOnDelete();
        });
    }
};
