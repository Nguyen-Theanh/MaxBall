<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('order_detail_id')
                ->nullable()
                ->after('order_id')
                ->constrained('order_details')
                ->cascadeOnDelete();
            $table->unique('order_detail_id');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique(['order_detail_id']);
            $table->dropConstrainedForeignId('order_detail_id');
        });
    }
};
