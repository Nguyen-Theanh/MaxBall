<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Hiển thị trang quét mã QR thanh toán
     */
    public function showQr($order_code)
    {
        $order = Order::where('order_code', $order_code)->firstOrFail();

        // Nếu đơn hàng đã thanh toán, chuyển hướng đến trang thành công
        if ($order->payment_status === 'paid') {
            return redirect()->route('client.checkout.success', ['order_code' => $order_code]);
        }

        // Thông tin tài khoản ngân hàng của bạn
        $bankId = 'MB'; // MB Bank
        $accountNo = '4007052006';
        $accountName = 'NGUYEN GIA TUAN';
        $amount = $order->total_amount;
        $addInfo = $order->order_code; // Nội dung chuyển khoản là mã đơn hàng

        // URL tạo mã VietQR động từ vietqr.io
        $qrUrl = "https://img.vietqr.io/image/{$bankId}-{$accountNo}-compact2.png?amount={$amount}&addInfo={$addInfo}&accountName=" . urlencode($accountName);

        return view('client.checkout.payment_qr', compact('order', 'qrUrl', 'accountNo', 'accountName', 'bankId'));
    }

    /**
     * AJAX endpoint để kiểm tra trạng thái đơn hàng liên tục
     */
    public function checkStatus($order_code)
    {
        $order = Order::where('order_code', $order_code)->first();

        if ($order && $order->payment_status === 'paid') {
            return response()->json(['paid' => true]);
        }

        return response()->json(['paid' => false]);
    }

    /**
     * Trang hiển thị thông báo thanh toán thành công
     */
    public function success($order_code)
    {
        $order = Order::where('order_code', $order_code)->firstOrFail();
        return view('client.checkout.success', compact('order'));
    }

    /**
     * Webhook từ SePay khi có biến động số dư
     */
    public function sepayWebhook(Request $request)
    {
        // SePay gửi data dạng JSON body
        $data = $request->all();
        
        Log::info('SePay Webhook received: ', $data);

        // Lấy nội dung chuyển khoản và số tiền từ payload của SePay
        // Xem tài liệu SePay: https://my.sepay.vn/docs/webhook
        $content = $data['content'] ?? '';
        $transferAmount = $data['transferAmount'] ?? 0;
        
        // Cố gắng tìm mã đơn hàng trong nội dung chuyển khoản
        $orders = Order::where('payment_status', '!=', 'paid')->get();
        
        foreach ($orders as $order) {
            Log::info("Checking order: {$order->order_code} vs content: {$content}. transferAmount: {$transferAmount}, total_amount: {$order->total_amount}");
            
            // Kiểm tra xem mã đơn hàng có xuất hiện trong nội dung chuyển khoản không
            if (stripos($content, $order->order_code) !== false) {
                Log::info("Content matches!");
                // Kiểm tra xem số tiền chuyển có khớp không (có thể cho phép sai số nhỏ hoặc >=)
                if ($transferAmount >= $order->total_amount) {
                    $order->update(['payment_status' => 'paid']);
                    
                    // Deduct stock on successful online payment
                    $order->load('details.variant');
                    foreach ($order->details as $detail) {
                        if ($detail->variant) {
                            $detail->variant->decrement('stock', $detail->quantity);
                        }
                    }

                    Log::info("Order {$order->order_code} marked as PAID via SePay.");
                    return response()->json(['success' => true, 'message' => 'Order updated']);
                } else {
                    Log::info("Amount mismatch! Transfer: {$transferAmount}, Order Total: {$order->total_amount}");
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Webhook received but no matching order found']);
    }
}
