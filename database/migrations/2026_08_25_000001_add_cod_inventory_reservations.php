<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->unsignedInteger('reserved_stock')->default(0)->after('stock');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('reserved_at')->nullable()->after('order_status');
            $table->timestamp('reservation_expires_at')->nullable()->after('reserved_at')->index();
            $table->timestamp('inventory_committed_at')->nullable()->after('reservation_expires_at');
            $table->timestamp('inventory_released_at')->nullable()->after('inventory_committed_at');
        });

        $this->backfillExistingCodOrders();

        DB::table('orders')
            ->where('order_status', 'processing')
            ->update(['order_status' => 'confirmed']);
    }

    public function down(): void
    {
        DB::table('orders')
            ->where('order_status', 'confirmed')
            ->update(['order_status' => 'processing']);

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'reserved_at',
                'reservation_expires_at',
                'inventory_committed_at',
                'inventory_released_at',
            ]);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('reserved_stock');
        });
    }

    private function backfillExistingCodOrders(): void
    {
        DB::table('orders')
            ->where('payment_method', 'cod')
            ->whereIn('order_status', ['processing', 'shipping', 'completed'])
            ->whereNull('inventory_committed_at')
            ->update(['inventory_committed_at' => DB::raw('updated_at')]);

        DB::table('orders')
            ->where('payment_method', 'cod')
            ->where('order_status', 'pending')
            ->orderBy('id')
            ->chunkById(200, function ($orders): void {
                $orderIds = $orders->pluck('id')->all();

                foreach ($orders as $order) {
                    $reservedAt = Carbon::parse($order->created_at);

                    DB::table('orders')
                        ->where('id', $order->id)
                        ->update([
                            'reserved_at' => $reservedAt,
                            'reservation_expires_at' => $reservedAt->copy()->addHours(24),
                        ]);
                }

                $reservedByVariant = DB::table('order_details')
                    ->whereIn('order_id', $orderIds)
                    ->select('product_variant_id', DB::raw('SUM(quantity) as quantity'))
                    ->groupBy('product_variant_id')
                    ->get();

                foreach ($reservedByVariant as $reservation) {
                    DB::table('product_variants')
                        ->where('id', $reservation->product_variant_id)
                        ->increment('reserved_stock', (int) $reservation->quantity);
                }
            });
    }
};
