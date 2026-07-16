<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use App\Models\Order;
use Carbon\Carbon;

#[Signature('orders:auto-complete')]
#[Description('Tự động hoàn thành các đơn hàng đang giao trên 48h')]
class AutoCompleteOrders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orders = Order::where('order_status', 'shipping')
            ->where('payment_method', 'cod')
            ->where('updated_at', '<=', Carbon::now()->subDays(2))
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            $order->update([
                'order_status' => 'completed',
                'payment_status' => 'paid'
            ]);
            $count++;
        }

        $this->info("Đã tự động hoàn thành {$count} đơn hàng COD.");
    }
}
