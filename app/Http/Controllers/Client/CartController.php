<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cart = Cart::with(['items.productVariant.product'])->firstOrCreate(
            ['user_id' => Auth::id()]
        );

        return view('client.cart.index', compact('cart'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'action' => 'required|in:add_cart,buy_now'
        ]);

        $variant = ProductVariant::findOrFail($request->product_variant_id);

        if ($variant->stock < $request->quantity) {
            return back()->with('error', 'Số lượng sản phẩm trong kho không đủ!');
        }

        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

        $cartItem = $cart->items()->where('product_variant_id', $variant->id)->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $request->quantity;
            if ($newQuantity > $variant->stock) {
                return back()->with('error', 'Bạn đã thêm quá số lượng tồn kho của sản phẩm này!');
            }
            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            $cart->items()->create([
                'product_variant_id' => $variant->id,
                'quantity' => $request->quantity,
            ]);
        }

        if ($request->action === 'buy_now') {
            return redirect()->route('client.checkout.index');
        }

        return back()->with('success', 'Đã thêm sản phẩm vào giỏ hàng!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cartItem = CartItem::whereHas('cart', function($q) {
            $q->where('user_id', Auth::id());
        })->findOrFail($id);

        if ($cartItem->productVariant->stock < $request->quantity) {
            return back()->with('error', 'Số lượng vượt quá tồn kho!');
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return back()->with('success', 'Đã cập nhật số lượng!');
    }

    public function destroy($id)
    {
        $cartItem = CartItem::whereHas('cart', function($q) {
            $q->where('user_id', Auth::id());
        })->findOrFail($id);

        $cartItem->delete();

        return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng!');
    }
}
