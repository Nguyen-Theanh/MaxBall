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
        $columnsToAdd = [
            'name' => !Schema::hasColumn('product_variants', 'name'),
            'base_price' => !Schema::hasColumn('product_variants', 'base_price'),
            'discount_price' => !Schema::hasColumn('product_variants', 'discount_price'),
        ];

        if (in_array(true, $columnsToAdd, true)) {
            Schema::table('product_variants', function (Blueprint $table) use ($columnsToAdd) {
                if ($columnsToAdd['name']) {
                    $table->string('name')->nullable()->after('product_id');
                }

                if ($columnsToAdd['base_price']) {
                    $table->integer('base_price')->default(0)->after('sku');
                }

                if ($columnsToAdd['discount_price']) {
                    $table->integer('discount_price')->nullable()->after('base_price');
                }
            });
        }

        // Migrate data
        if (
            Schema::hasColumn('product_variants', 'name')
            && Schema::hasColumn('product_variants', 'color')
            && Schema::hasColumn('product_variants', 'size')
        ) {
            DB::statement("UPDATE product_variants SET name = NULLIF(CONCAT_WS(' - ', NULLIF(color, ''), NULLIF(size, '')), '') WHERE name IS NULL OR name = ''");
        }

        if (
            Schema::hasColumn('product_variants', 'base_price')
            && Schema::hasColumn('product_variants', 'price')
        ) {
            DB::statement("UPDATE product_variants SET base_price = price WHERE base_price = 0 OR base_price IS NULL");
        }

        $columnsToDrop = array_values(array_filter(['size', 'color', 'price'], function (string $column) {
            return Schema::hasColumn('product_variants', $column);
        }));

        if (!empty($columnsToDrop)) {
            Schema::table('product_variants', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }

        if ($this->indexExists('product_variants', 'product_variants_sku_unique')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->dropUnique('product_variants_sku_unique');
            });
        }

        // Make sku nullable. using DB statement to avoid needing doctrine/dbal
        if (Schema::hasColumn('product_variants', 'sku')) {
            DB::statement("ALTER TABLE product_variants MODIFY sku VARCHAR(255) NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columnsToAdd = [
            'size' => !Schema::hasColumn('product_variants', 'size'),
            'color' => !Schema::hasColumn('product_variants', 'color'),
            'price' => !Schema::hasColumn('product_variants', 'price'),
        ];

        if (in_array(true, $columnsToAdd, true)) {
            Schema::table('product_variants', function (Blueprint $table) use ($columnsToAdd) {
                if ($columnsToAdd['size']) {
                    $table->string('size')->nullable();
                }

                if ($columnsToAdd['color']) {
                    $table->string('color')->nullable();
                }

                if ($columnsToAdd['price']) {
                    $table->decimal('price', 10, 2)->default(0);
                }
            });
        }

        if (
            Schema::hasColumn('product_variants', 'price')
            && Schema::hasColumn('product_variants', 'base_price')
        ) {
            DB::statement("UPDATE product_variants SET price = base_price");
        }

        $columnsToDrop = array_values(array_filter(['name', 'base_price', 'discount_price'], function (string $column) {
            return Schema::hasColumn('product_variants', $column);
        }));

        if (!empty($columnsToDrop)) {
            Schema::table('product_variants', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }

        if (!$this->indexExists('product_variants', 'product_variants_sku_unique')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->unique('sku');
            });
        }

        if (Schema::hasColumn('product_variants', 'sku')) {
            DB::statement("ALTER TABLE product_variants MODIFY sku VARCHAR(255) NOT NULL");
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        return !empty(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]));
    }
};
