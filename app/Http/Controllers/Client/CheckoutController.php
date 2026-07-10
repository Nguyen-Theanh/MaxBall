<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = Cart::with('items.productVariant.product')->where('user_id', Auth::id())->first();

        if (!$cart || $cart->items->count() === 0) {
            return redirect()->route('client.products.index')->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        $addresses = Auth::user()->addresses()->orderByDesc('is_default')->orderByDesc('created_at')->get();
        $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();

        return view('client.checkout.index', compact('cart', 'addresses', 'defaultAddress'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_address_id' => 'required|exists:user_addresses,id',
            'payment_method' => 'required|in:cod,vnpay',
        ], [
            'user_address_id.required' => 'Vui lòng chọn địa chỉ giao hàng.',
        ]);

        $cart = Cart::with('items.productVariant')->where('user_id', Auth::id())->first();

        if (!$cart || $cart->items->count() === 0) {
            return redirect()->route('client.products.index')->with('error', 'Giỏ hàng trống.');
        }

        // Validate stock
        foreach ($cart->items as $item) {
            if ($item->quantity > $item->productVariant->stock) {
                return back()->with('error', 'Sản phẩm "' . $item->productVariant->product->name . ' - ' . $item->productVariant->name . '" không đủ số lượng tồn kho.');
            }
        }

        try {
            DB::beginTransaction();

            $subTotal = 0;
            foreach ($cart->items as $item) {
                $price = $item->productVariant->discount_price ?: $item->productVariant->base_price;
                $subTotal += $price * $item->quantity;
            }

            $shippingFee = 30000; // Fake fixed shipping fee
            $totalAmount = $subTotal + $shippingFee;
            $orderCode = strtoupper(Str::random(10));

            $selectedAddress = Auth::user()->addresses()->findOrFail($request->user_address_id);

            $order = Order::create([
                'user_id' => Auth::id(),
                'order_code' => $orderCode,
                'customer_name' => $selectedAddress->receiver_name,
                'customer_phone' => $selectedAddress->receiver_phone,
                'customer_address' => $selectedAddress->address_detail,
                'sub_total' => $subTotal,
                'shipping_fee' => $shippingFee,
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'order_status' => 'pending',
            ]);

            foreach ($cart->items as $item) {
                $price = $item->productVariant->discount_price ?: $item->productVariant->base_price;
                
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $item->productVariant->id,
                    'quantity' => $item->quantity,
                    'price' => $price,
                ]);

                // Deduct stock
                $item->productVariant->decrement('stock', $item->quantity);
            }

            // Clear cart
            $cart->items()->delete();

            DB::commit();

            if ($request->payment_method === 'vnpay') {
                return $this->createVnPayPayment($order);
            }

            return redirect()->route('client.orders.index')->with('success', 'Đặt hàng thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Đã xảy ra lỗi trong quá trình đặt hàng: ' . $e->getMessage());
        }
    }

    private function createVnPayPayment(Order $order)
    {
        $vnp_TmnCode = env('VNP_TMN_CODE', 'YOUR_TMN_CODE');
        $vnp_HashSecret = env('VNP_HASH_SECRET', 'YOUR_HASH_SECRET');
        $vnp_Url = env('VNP_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
        $vnp_Returnurl = route('client.checkout.vnpay_return');
        $vnp_TxnRef = $order->order_code;
        $vnp_OrderInfo = "Thanh toan don hang " . $order->order_code;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $order->total_amount * 100;
        $vnp_Locale = 'vn';
        $vnp_IpAddr = request()->ip();

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef
        );

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return redirect()->away($vnp_Url);
    }

    public function vnpayReturn(Request $request)
    {
        $vnp_HashSecret = env('VNP_HASH_SECRET', 'YOUR_HASH_SECRET');
        $inputData = array();
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        
        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        $order = Order::where('order_code', $inputData['vnp_TxnRef'])->first();

        if ($secureHash == $vnp_SecureHash) {
            if ($inputData['vnp_ResponseCode'] == '00') {
                if ($order) {
                    $order->update(['payment_status' => 'paid']);
                }
                return redirect()->route('client.orders.index')->with('success', 'Thanh toán đơn hàng thành công!');
            } else {
                if ($order) {
                    $order->update(['payment_status' => 'failed']);
                }
                return redirect()->route('client.orders.index')->with('error', 'Thanh toán đơn hàng thất bại hoặc đã bị hủy.');
            }
        } else {
            return redirect()->route('client.orders.index')->with('error', 'Chữ ký số không hợp lệ.');
        }
    }
}
