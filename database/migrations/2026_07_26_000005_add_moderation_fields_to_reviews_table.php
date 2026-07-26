<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->boolean('is_visible')->default(true)->after('content')->index();
            $table->boolean('is_admin_review')->default(false)->after('is_visible')->index();
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['is_visible']);
            $table->dropIndex(['is_admin_review']);
            $table->dropColumn(['is_visible', 'is_admin_review']);
        });
    }
};
