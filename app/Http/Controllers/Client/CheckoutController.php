<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Mail\OrderCreatedMail;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = Cart::with('items.productVariant.product')->where('user_id', Auth::id())->first();

        if (! $cart || $cart->items->count() === 0) {
            return redirect()->route('client.products.index')->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        $addresses = Auth::user()->addresses()->orderByDesc('is_default')->orderByDesc('created_at')->get();
        $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();
        $selectedAddressId = session('selected_address_id') ?? old('user_address_id');
        $selectedAddress = $addresses->firstWhere('id', (int) $selectedAddressId) ?? $defaultAddress;

        return view('client.checkout.index', compact('cart', 'addresses', 'defaultAddress', 'selectedAddress'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_address_id' => 'required|exists:user_addresses,id',
            'payment_method' => 'required|in:cod,vietqr',
        ], [
            'user_address_id.required' => 'Vui lòng chọn địa chỉ giao hàng.',
        ]);

        $cart = Cart::with('items.productVariant')->where('user_id', Auth::id())->first();

        if (! $cart || $cart->items->count() === 0) {
            return redirect()->route('client.products.index')->with('error', 'Giỏ hàng trống.');
        }

        // Validate stock
        foreach ($cart->items as $item) {
            if ($item->quantity > $item->productVariant->stock) {
                return back()->with('error', 'Sản phẩm "'.$item->productVariant->product->name.' - '.$item->productVariant->name.'" không đủ số lượng tồn kho.');
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
                'customer_email' => $selectedAddress->receiver_email ?: Auth::user()->email,
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

            // Send Email
            try {
                if ($order->customer_email) {
                    Mail::to($order->customer_email)->send(new OrderCreatedMail($order));
                } elseif (Auth::user()->email) {
                    Mail::to(Auth::user()->email)->send(new OrderCreatedMail($order));
                }
            } catch (\Exception $e) {
                // Log the email error but don't stop the checkout
                \Log::error('Lỗi gửi email: '.$e->getMessage());
            }

            if ($request->payment_method === 'vietqr') {
                return redirect()->route('client.checkout.payment_qr', ['order_code' => $orderCode]);
            }

            return redirect()->route('client.orders.index')->with('success', 'Đặt hàng thành công!');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Đã xảy ra lỗi trong quá trình đặt hàng: '.$e->getMessage());
        }
    }
}
