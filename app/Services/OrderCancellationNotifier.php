<?php

namespace App\Services;

use App\Mail\OrderCancelledMail;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OrderCancellationNotifier
{
    public function send(Order $order): void
    {
        $order->loadMissing('user');
        $recipient = $order->customer_email ?: $order->user?->email;

        if (! $recipient) {
            Log::warning('Không thể gửi email hủy đơn vì đơn hàng không có địa chỉ email.', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
            ]);

            return;
        }

        try {
            Mail::to($recipient)->send(new OrderCancelledMail($order));
        } catch (Throwable $exception) {
            Log::error('Lỗi gửi email hủy đơn hàng.', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
