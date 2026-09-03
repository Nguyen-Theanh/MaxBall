<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Mail\OrderCreatedMail;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ProductVariant;
use App\Models\UserVoucher;
use App\Models\WalletTransaction;
use App\Services\OrderInventoryService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function prepare(Request $request)
    {
        $request->validate([
            'selected_items' => 'required|array|min:1',
            'selected_items.*' => 'exists:cart_items,id',
        ], [
            'selected_items.required' => 'Vui lòng chọn ít nhất một sản phẩm để thanh toán.',
        ]);

        // Tránh xung đột giữa "Mua ngay" và checkout từ giỏ hàng
        if (session()->has('buy_now_item')) {
            session()->forget('buy_now_item');
        }

        session([
            'selected_items' => $request->selected_items,
        ]);

        return response()->json([
            'success' => true,
            'redirect' => route('client.checkout.index'),
        ]);
    }

    public function index()
    {
        $cart = $this->getCheckoutCart();

        if (! $cart || $cart->items->count() === 0) {
            return redirect()
                ->route('client.products.index')
                ->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        $addresses = Auth::user()
            ->addresses()
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        $defaultAddress =
            $addresses->firstWhere('is_default', true)
            ?? $addresses->first();

        $selectedAddressId =
            session('selected_address_id')
            ?? old('user_address_id');

        $selectedAddress =
            $addresses->firstWhere(
                'id',
                (int) $selectedAddressId
            )
            ?? $defaultAddress;

        return view(
            'client.checkout.index',
            compact(
                'cart',
                'addresses',
                'defaultAddress',
                'selectedAddress'
            )
        );
    }

    public function store(
        Request $request,
        OrderInventoryService $inventoryService
    ) {
        $request->validate([
            'user_address_id' => 'required|exists:user_addresses,id',
            'payment_method' => 'required|in:cod,vietqr,wallet',
            'coupon_code' => 'nullable|string|max:100',
            'freeship_coupon_code' => 'nullable|string|max:100',
        ], [
            'user_address_id.required' => 'Vui lòng chọn địa chỉ giao hàng.',
        ]);

        $cart = $this->getCheckoutCart();

        if (! $cart || $cart->items->count() === 0) {
            return redirect()
                ->route('client.products.index')
                ->with('error', 'Giỏ hàng trống.');
        }

        /*
        |--------------------------------------------------------------------------
        | Kiểm tra tồn kho khả dụng
        |--------------------------------------------------------------------------
        */
        foreach ($cart->items as $item) {
            $variant = $item->productVariant;

            if (! $variant) {
                return back()->with(
                    'error',
                    'Một sản phẩm trong đơn hàng không còn tồn tại.'
                );
            }

            if ($item->quantity > $variant->available_stock) {
                return back()->with(
                    'error',
                    'Sản phẩm "'
                    .$variant->product->name
                    .' - '
                    .$variant->name
                    .'" không đủ số lượng tồn kho.'
                );
            }
        }

        try {
            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | Tính tổng tiền hàng
            |--------------------------------------------------------------------------
            */
            $subTotal = 0;

            foreach ($cart->items as $item) {
                $variant = $item->productVariant;

                $price = $variant->discount_price
                    ?: $variant->base_price;

                $subTotal += $price * $item->quantity;
            }

            /*
            |--------------------------------------------------------------------------
            | Phí vận chuyển
            |--------------------------------------------------------------------------
            */
            $shippingFee =
                $subTotal >= 500000
                    ? 0
                    : 30000;

            $orderCode = strtoupper(Str::random(10));

            $appliedDiscountCoupon = null;
            $appliedFreeshipCoupon = null;

            $discountAmount = 0;

            /*
            |--------------------------------------------------------------------------
            | Voucher freeship
            |--------------------------------------------------------------------------
            */
            if ($request->filled('freeship_coupon_code')) {
                $appliedFreeshipCoupon = $this->resolveCheckoutCoupon(
                    $request->string('freeship_coupon_code')->trim()->toString(),
                    ['freeship'],
                    $subTotal
                );
                $shippingFee = 0;
            }

            /*
            |--------------------------------------------------------------------------
            | Voucher giảm giá
            |--------------------------------------------------------------------------
            */
            if ($request->filled('coupon_code')) {
                $appliedDiscountCoupon = $this->resolveCheckoutCoupon(
                    $request->string('coupon_code')->trim()->toString(),
                    ['fixed', 'percent'],
                    $subTotal
                );

                if ($appliedDiscountCoupon->discount_type === 'fixed') {
                    $discountAmount = (float) $appliedDiscountCoupon->discount_value;
                } else {
                    $discountAmount = ($subTotal * $appliedDiscountCoupon->discount_value) / 100;
                    $discountAmount = min(
                        $discountAmount,
                        (float) $appliedDiscountCoupon->max_discount_amount
                    );
                }

                if ($discountAmount > $subTotal) {
                    $discountAmount = $subTotal;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Tổng thanh toán
            |--------------------------------------------------------------------------
            */
            $totalAmount =
                $subTotal
                + $shippingFee
                - $discountAmount;

            /*
            |--------------------------------------------------------------------------
            | Địa chỉ giao hàng
            |--------------------------------------------------------------------------
            */
            $selectedAddress = Auth::user()
                ->addresses()
                ->findOrFail(
                    $request->user_address_id
                );

            /*
            |--------------------------------------------------------------------------
            | Kiểm tra số dư ví
            |--------------------------------------------------------------------------
            */
            if ($request->payment_method === 'wallet') {
                if (
                    Auth::user()->wallet_balance
                    < $totalAmount
                ) {
                    DB::rollBack();

                    return back()->with(
                        'error',
                        'Số dư trong Ví MaxBall không đủ để thanh toán đơn hàng này.'
                    );
                }
            }

            if ($request->payment_method === 'vietqr' && $totalAmount < 2000) {
                DB::rollBack();
                return back()->with(
                    'error',
                    'Thanh toán trực tuyến chỉ áp dụng cho đơn hàng từ 2.000đ trở lên. Vui lòng chọn thanh toán khi nhận hàng (COD).'
                )->withInput();
            }

            /*
            |--------------------------------------------------------------------------
            | Tạo đơn hàng
            |--------------------------------------------------------------------------
            */
            $order = Order::create([
                'user_id' => Auth::id(),

                'coupon_id' => $appliedDiscountCoupon
                        ? $appliedDiscountCoupon->id
                        : null,

                'freeship_coupon_id' => $appliedFreeshipCoupon
                        ? $appliedFreeshipCoupon->id
                        : null,

                'order_code' => $orderCode,

                'customer_name' => $selectedAddress->receiver_name,

                'customer_phone' => $selectedAddress->receiver_phone,

                'customer_email' => $selectedAddress->receiver_email
                    ?: Auth::user()->email,

                'customer_address' => $selectedAddress->address_detail,

                'sub_total' => $subTotal,

                'shipping_fee' => $shippingFee,

                'discount_amount' => $discountAmount,

                'total_amount' => $totalAmount,

                'payment_method' => $request->payment_method,

                'payment_status' => $request->payment_method === 'wallet'
                        ? 'paid'
                        : 'pending',

                'order_status' => 'pending',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Tạo chi tiết đơn hàng
            |--------------------------------------------------------------------------
            |
            | Chỉ tạo MỘT lần.
            |
            */
            foreach ($cart->items as $item) {
                $variant = $item->productVariant;

                $price = $variant->discount_price
                    ?: $variant->base_price;

                OrderDetail::create([
                    'order_id' => $order->id,

                    'product_variant_id' => $variant->id,

                    'quantity' => $item->quantity,

                    'price' => $price,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Voucher freeship
            |--------------------------------------------------------------------------
            */
            if ($appliedFreeshipCoupon) {
                $appliedFreeshipCoupon->increment(
                    'used_count'
                );

                $userVoucherFreeship =
                    UserVoucher::firstOrCreate([
                        'user_id' => Auth::id(),

                        'coupon_id' => $appliedFreeshipCoupon->id,
                    ]);

                $userVoucherFreeship->update([
                    'is_used' => true,
                    'used_at' => now(),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Voucher giảm giá
            |--------------------------------------------------------------------------
            */
            if ($appliedDiscountCoupon) {
                $appliedDiscountCoupon->increment(
                    'used_count'
                );

                $userVoucherDiscount =
                    UserVoucher::firstOrCreate([
                        'user_id' => Auth::id(),

                        'coupon_id' => $appliedDiscountCoupon->id,
                    ]);

                $userVoucherDiscount->update([
                    'is_used' => true,
                    'used_at' => now(),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Thanh toán bằng ví
            |--------------------------------------------------------------------------
            */
            if ($request->payment_method === 'wallet') {
                $user = Auth::user();

                $user->decrement(
                    'wallet_balance',
                    $totalAmount
                );

                WalletTransaction::create([
                    'user_id' => $user->id,

                    'type' => 'payment',

                    'amount' => $totalAmount,

                    'description' => 'Thanh toán đơn hàng #'
                        .$orderCode,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Reserve hàng cho COD
            |--------------------------------------------------------------------------
            |
            | COD:
            |
            | Đặt hàng
            | → giữ tồn kho
            | → admin có 24h xác nhận
            | → xác nhận thì chốt tồn
            | → hết hạn/hủy thì nhả tồn
            |
            */
            if ($request->payment_method === 'cod') {
                $order = $inventoryService->reserveCod(
                    $order
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Xóa sản phẩm đã checkout
            |--------------------------------------------------------------------------
            */
            if (session()->has('buy_now_item')) {
                session()->forget('buy_now_item');
            } elseif (session()->has('selected_items')) {
                CartItem::whereIn(
                    'id',
                    session('selected_items')
                )
                    ->whereHas(
                        'cart',
                        function ($query) {
                            $query->where(
                                'user_id',
                                Auth::id()
                            );
                        }
                    )
                    ->delete();

                session()->forget('selected_items');
            } else {
                /*
                | Fallback nếu checkout toàn bộ giỏ.
                */
                if ($cart->exists) {
                    $cart->items()->delete();
                }
            }

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | Gửi email
            |--------------------------------------------------------------------------
            */
            try {
                if ($order->customer_email) {
                    Mail::to(
                        $order->customer_email
                    )->send(
                        new OrderCreatedMail($order)
                    );
                } elseif (Auth::user()->email) {
                    Mail::to(
                        Auth::user()->email
                    )->send(
                        new OrderCreatedMail($order)
                    );
                }
            } catch (\Exception $e) {
                Log::error(
                    'Lỗi gửi email: '
                    .$e->getMessage()
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Redirect theo phương thức thanh toán
            |--------------------------------------------------------------------------
            */
            if ($request->payment_method === 'vietqr') {
                return redirect()->route(
                    'client.checkout.payment_qr',
                    [
                        'order_code' => $orderCode,
                    ]
                );
            }

            if ($request->payment_method === 'wallet') {
                return redirect()->route(
                    'client.checkout.success',
                    [
                        'order_code' => $orderCode,
                    ]
                );
            }

            return redirect()
                ->route('client.orders.index')
                ->with(
                    'success',
                    'Đặt hàng thành công! Sản phẩm được giữ trong 24 giờ để cửa hàng xác nhận.'
                );
        } catch (DomainException $e) {
            DB::rollBack();

            return back()->with(
                'error',
                $e->getMessage()
            );
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error(
                'Checkout error: '
                .$e->getMessage()
            );

            return back()->with(
                'error',
                'Đã xảy ra lỗi trong quá trình đặt hàng: '
                .$e->getMessage()
            );
        }
    }

    /**
     * Lấy và khóa voucher ngay trước khi tạo đơn để tránh voucher bị xóa,
     * tắt hoặc hết lượt giữa lúc khách áp mã và lúc hoàn tất thanh toán.
     *
     * @param  array<int, string>  $discountTypes
     */
    private function resolveCheckoutCoupon(
        string $code,
        array $discountTypes,
        float $subTotal
    ): Coupon {
        $coupon = Coupon::query()
            ->where('code', $code)
            ->lockForUpdate()
            ->first();

        if (
            ! $coupon
            || ! in_array($coupon->discount_type, $discountTypes, true)
            || ! $coupon->is_currently_available
        ) {
            throw new DomainException(
                "Voucher “{$code}” đã không còn hiệu lực. Vui lòng chọn voucher khác trước khi đặt hàng."
            );
        }

        if ($subTotal < (float) ($coupon->min_order_value ?? 0)) {
            $minimumOrderValue = number_format(
                (float) $coupon->min_order_value,
                0,
                ',',
                '.'
            );

            throw new DomainException(
                "Đơn hàng chưa đạt giá trị tối thiểu {$minimumOrderValue}đ để dùng voucher “{$coupon->code}”."
            );
        }

        $userVoucher = UserVoucher::query()
            ->where('user_id', Auth::id())
            ->where('coupon_id', $coupon->id)
            ->lockForUpdate()
            ->first();

        if ((! $coupon->is_public && ! $userVoucher) || $userVoucher?->is_used) {
            throw new DomainException(
                "Voucher “{$coupon->code}” không còn khả dụng cho tài khoản của bạn. Vui lòng chọn voucher khác trước khi đặt hàng."
            );
        }

        return $coupon;
    }

    /**
     * Lấy danh sách sản phẩm đang được checkout.
     *
     * Hỗ trợ:
     * - Mua ngay
     * - Sản phẩm được chọn trong giỏ hàng
     */
    private function getCheckoutCart()
    {
        /*
        |--------------------------------------------------------------------------
        | Mua ngay
        |--------------------------------------------------------------------------
        */
        if (session()->has('buy_now_item')) {
            $buyNowData = session('buy_now_item');

            $variant = ProductVariant::with('product')
                ->find(
                    $buyNowData['product_variant_id']
                );

            if (! $variant) {
                session()->forget('buy_now_item');

                return null;
            }

            $fakeItem = new CartItem([
                'product_variant_id' => $variant->id,

                'quantity' => $buyNowData['quantity'],
            ]);

            $fakeItem->setRelation(
                'productVariant',
                $variant
            );

            $cart = new Cart([
                'user_id' => Auth::id(),
            ]);

            $cart->setRelation(
                'items',
                collect([$fakeItem])
            );

            return $cart;
        }

        /*
        |--------------------------------------------------------------------------
        | Checkout từ giỏ hàng
        |--------------------------------------------------------------------------
        */
        $cart = Cart::with(
            'items.productVariant.product'
        )
            ->where(
                'user_id',
                Auth::id()
            )
            ->first();

        if (
            $cart
            && session()->has('selected_items')
        ) {
            $selectedIds =
                session('selected_items');

            $filteredItems =
                $cart->items->whereIn(
                    'id',
                    $selectedIds
                );

            $cart->setRelation(
                'items',
                $filteredItems
            );
        }

        return $cart;
    }
}
