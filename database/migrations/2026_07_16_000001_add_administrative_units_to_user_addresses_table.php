<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->string('address_line')->nullable()->after('receiver_phone');
            $table->unsignedSmallInteger('province_code')->nullable()->after('address_line');
            $table->string('province_name')->nullable()->after('province_code');
            $table->unsignedInteger('ward_code')->nullable()->after('province_name');
            $table->string('ward_name')->nullable()->after('ward_code');
        });
    }

    public function down(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropColumn([
                'address_line',
                'province_code',
                'province_name',
                'ward_code',
                'ward_name',
            ]);
        });
    }
};
