<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use App\Models\Order;
use Carbon\Carbon;

#[Signature('orders:auto-complete')]
#[Description('Tự động hoàn thành các đơn hàng đang giao trên 7 ngày')]
class AutoCompleteOrders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orders = Order::where('order_status', 'shipping')
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('shipped_at')
                      ->where('shipped_at', '<=', Carbon::now()->subDays(7));
                })->orWhere(function ($q) {
                    $q->whereNull('shipped_at')
                      ->where('updated_at', '<=', Carbon::now()->subDays(7));
                });
            })
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            $order->update([
                'order_status' => 'completed',
                'payment_status' => 'paid'
            ]);
            $count++;
        }

        $this->info("Đã tự động hoàn thành {$count} đơn hàng.");
    }
}
