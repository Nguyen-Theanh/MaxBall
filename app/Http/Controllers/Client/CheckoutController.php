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
    public function prepare(Request $request)
    {
        $request->validate([
            'selected_items' => 'required|array|min:1',
            'selected_items.*' => 'exists:cart_items,id'
        ], [
            'selected_items.required' => 'Vui lòng chọn ít nhất một sản phẩm để thanh toán.',
        ]);

        // Clear any previous buy_now_item to avoid conflicts
        if (session()->has('buy_now_item')) {
            session()->forget('buy_now_item');
        }

        session(['selected_items' => $request->selected_items]);
        
        return response()->json([
            'success' => true,
            'redirect' => route('client.checkout.index')
        ]);
    }

    public function index()
    {
        if (session()->has('buy_now_item')) {
            $buyNowData = session('buy_now_item');
            $variant = \App\Models\ProductVariant::with('product')->find($buyNowData['product_variant_id']);
            if (!$variant) {
                session()->forget('buy_now_item');
                return redirect()->route('client.products.index')->with('error', 'Sản phẩm không tồn tại.');
            }
            $fakeItem = new \App\Models\CartItem([
                'product_variant_id' => $variant->id,
                'quantity' => $buyNowData['quantity']
            ]);
            $fakeItem->setRelation('productVariant', $variant);
            $cart = new \App\Models\Cart(['user_id' => Auth::id()]);
            $cart->setRelation('items', collect([$fakeItem]));
        } else {
            $cart = Cart::with('items.productVariant.product')->where('user_id', Auth::id())->first();
            
            if ($cart && session()->has('selected_items')) {
                $selectedIds = session('selected_items');
                $filteredItems = $cart->items->whereIn('id', $selectedIds);
                $cart->setRelation('items', $filteredItems);
            }
        }

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
            'payment_method' => 'required|in:cod,vietqr,wallet',
        ], [
            'user_address_id.required' => 'Vui lòng chọn địa chỉ giao hàng.',
        ]);

        if (session()->has('buy_now_item')) {
            $buyNowData = session('buy_now_item');
            $variant = \App\Models\ProductVariant::with('product')->find($buyNowData['product_variant_id']);
            if (!$variant) {
                session()->forget('buy_now_item');
                return redirect()->route('client.products.index')->with('error', 'Sản phẩm không tồn tại.');
            }
            $fakeItem = new \App\Models\CartItem([
                'product_variant_id' => $variant->id,
                'quantity' => $buyNowData['quantity']
            ]);
            $fakeItem->setRelation('productVariant', $variant);
            $cart = new \App\Models\Cart(['user_id' => Auth::id()]);
            $cart->setRelation('items', collect([$fakeItem]));
        } else {
            $cart = Cart::with('items.productVariant.product')->where('user_id', Auth::id())->first();
            
            if ($cart && session()->has('selected_items')) {
                $selectedIds = session('selected_items');
                $filteredItems = $cart->items->whereIn('id', $selectedIds);
                $cart->setRelation('items', $filteredItems);
            }
        }

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

            $shippingFee = $subTotal >= 500000 ? 0 : 30000; // Free shipping for orders >= 500k
            $orderCode = strtoupper(Str::random(10));
            $appliedDiscountCoupon = null;
            $appliedFreeshipCoupon = null;
            $discountAmount = 0;

            // Handle Freeship Coupon
            if ($request->filled('freeship_coupon_code')) {
                $coupon = Coupon::where('code', $request->freeship_coupon_code)
                    ->where('status', true)
                    ->where('discount_type', 'freeship')
                    ->first();
                    
                if ($coupon) {
                    $userVoucher = \App\Models\UserVoucher::where('user_id', Auth::id())->where('coupon_id', $coupon->id)->first();
                    if (!$userVoucher || !$userVoucher->is_used) {
                        $appliedFreeshipCoupon = $coupon;
                        $shippingFee = 0; // Completely free ship
                    }
                }
            }

            // Handle Discount Coupon
            if ($request->filled('coupon_code')) {
                $coupon = Coupon::where('code', $request->coupon_code)
                    ->where('status', true)
                    ->where(function($q) {
                        $q->whereNull('start_date')->orWhere('start_date', '<=', now());
                    })
                    ->where(function($q) {
                        $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                    })
                    ->first();

                if ($coupon && $subTotal >= ($coupon->min_order_value ?? 0)) {
                    if (!$coupon->usage_limit || $coupon->used_count < $coupon->usage_limit) {
                        $userVoucher = \App\Models\UserVoucher::where('user_id', Auth::id())->where('coupon_id', $coupon->id)->first();
                        if (!$userVoucher || !$userVoucher->is_used) {
                            $appliedDiscountCoupon = $coupon;
                            if ($coupon->discount_type == 'fixed') {
                                $discountAmount = $coupon->discount_value;
                            } else {
                                $discountAmount = ($subTotal * $coupon->discount_value) / 100;
                            }
                            if ($discountAmount > $subTotal) {
                                $discountAmount = $subTotal;
                            }
                        }
                    }
                }
            }

            $totalAmount = $subTotal + $shippingFee - $discountAmount;

            $selectedAddress = Auth::user()->addresses()->findOrFail($request->user_address_id);

            if ($request->payment_method === 'wallet') {
                if (Auth::user()->wallet_balance < $totalAmount) {
                    return back()->with('error', 'Số dư trong Ví MaxBall không đủ để thanh toán đơn hàng này.');
                }
            }

            $order = Order::create([
                'user_id' => Auth::id(),
                'coupon_id' => $appliedDiscountCoupon ? $appliedDiscountCoupon->id : null,
                'freeship_coupon_id' => $appliedFreeshipCoupon ? $appliedFreeshipCoupon->id : null,
                'order_code' => $orderCode,
                'customer_name' => $selectedAddress->receiver_name,
                'customer_phone' => $selectedAddress->receiver_phone,
                'customer_email' => $selectedAddress->receiver_email ?: Auth::user()->email,
                'customer_address' => $selectedAddress->address_detail,
                'sub_total' => $subTotal,
                'shipping_fee' => $shippingFee,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method === 'wallet' ? 'paid' : 'pending',
                'order_status' => 'pending',
            ]);

            // Create order details
            foreach ($cartItems as $item) {
                $price = $item->productVariant->discount_price ?: $item->productVariant->base_price;
                \App\Models\OrderDetail::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'price' => $price,
                ]);

                // Reduce stock
                $item->productVariant->decrement('stock', $item->quantity);
            }

            // Process Freeship Coupon
            if ($appliedFreeshipCoupon) {
                $appliedFreeshipCoupon->increment('used_count');
                $userVoucherFreeship = \App\Models\UserVoucher::firstOrCreate(
                    ['user_id' => Auth::id(), 'coupon_id' => $appliedFreeshipCoupon->id]
                );
                $userVoucherFreeship->update(['is_used' => true, 'used_at' => now()]);
            }

            // Process Discount Coupon
            if ($appliedDiscountCoupon) {
                $appliedDiscountCoupon->increment('used_count');
                $userVoucherDiscount = \App\Models\UserVoucher::firstOrCreate(
                    ['user_id' => Auth::id(), 'coupon_id' => $appliedDiscountCoupon->id]
                );
                $userVoucherDiscount->update(['is_used' => true, 'used_at' => now()]);
            }

            if ($request->payment_method === 'wallet') {
                $user = Auth::user();
                $user->decrement('wallet_balance', $totalAmount);
                \App\Models\WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'payment',
                    'amount' => $totalAmount,
                    'description' => 'Thanh toán đơn hàng #' . $orderCode,
                ]);
                
                // Deduct stock for wallet payment
                foreach ($cart->items as $item) {
                    $item->productVariant->decrement('stock', $item->quantity);
                }
            }

            foreach ($cart->items as $item) {
                $price = $item->productVariant->discount_price ?: $item->productVariant->base_price;

                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $item->productVariant->id,
                    'quantity' => $item->quantity,
                    'price' => $price,
                ]);

            }

            // Clear cart or session
            if (session()->has('buy_now_item')) {
                session()->forget('buy_now_item');
            } else if (session()->has('selected_items')) {
                // Only delete the items that were purchased
                \App\Models\CartItem::whereIn('id', session('selected_items'))->delete();
                session()->forget('selected_items');
            } else {
                // Fallback (shouldn't happen, but just in case)
                $cart->items()->delete();
            }

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
            } elseif ($request->payment_method === 'wallet') {
                return redirect()->route('client.checkout.success', ['order_code' => $orderCode]);
            }

            return redirect()->route('client.orders.index')->with('success', 'Đặt hàng thành công!');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Đã xảy ra lỗi trong quá trình đặt hàng: '.$e->getMessage());
        }
    }
}
