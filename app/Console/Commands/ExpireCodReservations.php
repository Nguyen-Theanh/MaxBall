<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderCancellationNotifier;
use App\Services\OrderInventoryService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('orders:expire-cod-reservations')]
#[Description('Tự hủy đơn COD quá 24 giờ chưa được cửa hàng xác nhận và nhả hàng đang giữ')]
class ExpireCodReservations extends Command
{
    public function handle(
        OrderInventoryService $inventoryService,
        OrderCancellationNotifier $notifier
    ): int {
        $expiredCount = 0;

        Order::query()
            ->where('payment_method', 'cod')
            ->where('order_status', 'pending')
            ->whereNull('inventory_released_at')
            ->whereNotNull('reservation_expires_at')
            ->where('reservation_expires_at', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($orders) use ($inventoryService, $notifier, &$expiredCount): void {
                foreach ($orders as $order) {
                    $expiredOrder = $inventoryService->expireCod($order);

                    if (! $expiredOrder) {
                        continue;
                    }

                    $notifier->send($expiredOrder);
                    $expiredCount++;
                }
            });

        $this->info("Đã tự hủy {$expiredCount} đơn COD quá hạn và nhả hàng đang giữ.");

        return self::SUCCESS;
    }
}
