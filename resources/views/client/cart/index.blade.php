@extends('client.layouts.app')

@section('title', 'Giỏ Hàng - MaxBall')

@section('content')
<!-- Header Background Spacer -->
<div class="bg-[#10271d] h-[110px] w-full absolute top-0 left-0 z-0"></div>

<div class="relative z-10 max-w-7xl mx-auto px-4 pt-32 pb-12 md:pb-16">

    @if($cart && $cart->items->count() > 0)
        <div class="flex items-center gap-3 mb-8">
            <h1 class="text-3xl md:text-4xl font-black text-[#10271d] tracking-tight">Giỏ hàng của bạn</h1>
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#d92525] text-sm font-bold text-white shadow-md">{{ $cart->items->sum('quantity') }}</span>
        </div>

        <div class="flex flex-col lg:flex-row gap-8 lg:gap-12">
            <div class="lg:w-2/3">
                <div class="bg-white rounded-3xl shadow-xl shadow-[#10271d]/5 border border-gray-100 overflow-hidden">
                    <!-- Header -->
                    <div class="hidden sm:grid grid-cols-12 gap-4 p-6 border-b border-gray-100 bg-gray-50/50 text-xs font-black uppercase tracking-wider text-gray-500">
                        <div class="col-span-5 pl-2">Sản phẩm</div>
                        <div class="col-span-2 text-center">Đơn giá</div>
                        <div class="col-span-2 text-center">Số lượng</div>
                        <div class="col-span-3 text-right pr-2">Thành tiền</div>
                    </div>
                    
                    @php $totalPrice = 0; @endphp
                    <div class="divide-y divide-gray-100">
                        @foreach($cart->items as $item)
                            @php
                                $variant = $item->productVariant;
                                $product = $variant->product;
                                $price = $variant->discount_price ?: $variant->base_price;
                                $subtotal = $price * $item->quantity;
                                $totalPrice += $subtotal;
                                
                                $thumbnail = $product->thumbnail_url ?? null;
                                if (!$thumbnail && !empty($product->thumbnail)) {
                                    $thumbnail = str_starts_with($product->thumbnail, 'http') ? $product->thumbnail : asset('storage/' . $product->thumbnail);
                                }
                                if (!$thumbnail) {
                                    $thumbnail = 'https://via.placeholder.com/150';
                                }
                            @endphp
                            
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-6 p-6 items-center hover:bg-gray-50/50 transition-colors group">
                                <div class="col-span-1 sm:col-span-5 flex gap-5 items-start">
                                    <a href="{{ route('client.products.show', $product->slug) }}" class="shrink-0 overflow-hidden rounded-2xl border border-gray-100 bg-white">
                                        <img src="{{ $thumbnail }}" alt="{{ $product->name }}" class="w-24 h-24 sm:w-28 sm:h-28 object-cover object-center group-hover:scale-105 transition-transform duration-500">
                                    </a>
                                    <div class="flex-1 pt-1">
                                        <a href="{{ route('client.products.show', $product->slug) }}" class="font-bold text-lg text-[#10271d] hover:text-[#d92525] transition-colors line-clamp-2 leading-tight mb-2">
                                            {{ $product->name }}
                                        </a>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-gray-100 text-gray-600 mb-3">
                                            {{ $variant->name }}
                                        </span>
                                        
                                        <!-- Mobile view price and remove -->
                                        <div class="sm:hidden flex justify-between items-center mt-2">
                                            <span class="font-black text-[#d92525] text-lg">{{ number_format($price, 0, ',', '.') }}đ</span>
                                            <form action="{{ route('client.cart.destroy', $item->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm text-gray-400 hover:text-[#d92525] font-bold underline transition-colors">Xóa</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="hidden sm:block col-span-2 text-center font-bold text-gray-900">
                                    {{ number_format($price, 0, ',', '.') }}đ
                                </div>
                                
                                <div class="col-span-1 sm:col-span-2 flex justify-start sm:justify-center">
                                    <form action="{{ route('client.cart.update', $item->id) }}" method="POST" class="inline-flex items-center p-1 border border-gray-200 rounded-xl bg-white shadow-sm hover:border-[#10271d]/20 transition-colors">
                                        @csrf
                                        @method('PUT')
                                        <button type="button" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-[#10271d] hover:bg-gray-100 rounded-lg transition-colors" onclick="updateQuantity(this, 'down')"><i class="fa-solid fa-minus text-xs"></i></button>
                                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $variant->stock }}" class="w-10 text-center border-0 p-0 text-sm font-bold text-[#10271d] focus:ring-0 appearance-none bg-transparent" onchange="checkQuantity(this)">
                                        <button type="button" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-[#10271d] hover:bg-gray-100 rounded-lg transition-colors" onclick="updateQuantity(this, 'up')"><i class="fa-solid fa-plus text-xs"></i></button>
                                    </form>
                                </div>
                                
                                <div class="hidden sm:flex col-span-3 justify-end items-center gap-4">
                                    <span class="font-black text-[#d92525] text-lg">{{ number_format($subtotal, 0, ',', '.') }}đ</span>
                                    <form action="{{ route('client.cart.destroy', $item->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#d92525] hover:bg-red-50 transition-colors" title="Xóa khỏi giỏ hàng">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <div class="mt-6">
                    <a href="{{ route('client.products.index') }}" class="inline-flex items-center text-sm font-bold text-gray-500 hover:text-[#d92525] transition-colors">
                        <i class="fa-solid fa-arrow-left-long mr-2"></i> Tiếp tục mua sắm
                    </a>
                </div>
            </div>
            
            <div class="lg:w-1/3">
                <div class="bg-white rounded-3xl shadow-xl shadow-[#10271d]/5 border border-gray-100 p-6 sm:p-8 sticky top-24">
                    <h2 class="text-xl font-black text-[#10271d] mb-6 tracking-tight">Tóm tắt đơn hàng</h2>
                    
                    <div class="space-y-4 mb-6 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span class="font-medium">Tạm tính ({{ $cart->items->sum('quantity') }} sản phẩm)</span>
                            <span class="font-bold text-gray-900">{{ number_format($totalPrice, 0, ',', '.') }}đ</span>
                        </div>
                        
                        <div class="flex justify-between text-gray-600 pb-6 border-b border-gray-100">
                            <span class="font-medium">Phí giao hàng</span>
                            <span class="text-gray-400 italic">Sẽ tính khi thanh toán</span>
                        </div>
                        
                        <div class="flex justify-between items-center pt-2">
                            <span class="text-lg font-black text-[#10271d]">Tổng cộng</span>
                            <span class="text-3xl font-black text-[#d92525] tracking-tight">{{ number_format($totalPrice, 0, ',', '.') }}đ</span>
                        </div>
                        <p class="text-right text-xs text-gray-400 mt-1">Đã bao gồm VAT (nếu có)</p>
                    </div>
                    
                    <a href="{{ route('client.checkout.index') ?? '#' }}" class="group relative flex w-full items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[#d92525] px-8 py-4 font-black text-white transition-all hover:bg-red-700 shadow-xl shadow-red-500/20 hover:shadow-red-500/40 hover:-translate-y-0.5">
                        <span class="relative z-10">Tiến hành thanh toán</span>
                        <i class="fa-solid fa-arrow-right relative z-10 transition-transform group-hover:translate-x-1"></i>
                    </a>
                    
                    <!-- Trust badges -->
                    <div class="mt-8 pt-6 border-t border-gray-100">
                        <div class="flex items-center justify-center gap-4 text-gray-400">
                            <i class="fa-brands fa-cc-visa text-3xl hover:text-gray-600 transition-colors cursor-pointer"></i>
                            <i class="fa-brands fa-cc-mastercard text-3xl hover:text-gray-600 transition-colors cursor-pointer"></i>
                            <i class="fa-brands fa-cc-paypal text-3xl hover:text-gray-600 transition-colors cursor-pointer"></i>
                            <i class="fa-brands fa-cc-apple-pay text-3xl hover:text-gray-600 transition-colors cursor-pointer"></i>
                        </div>
                        <p class="text-center text-xs text-gray-400 font-medium mt-4 flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-shield-halved text-[#10271d]"></i>
                            Thanh toán bảo mật và an toàn 100%
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-[2rem] shadow-2xl shadow-[#10271d]/5 p-12 lg:p-24 text-center max-w-3xl mx-auto border border-gray-100 relative overflow-hidden">
            <!-- Decorative background elements -->
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-transparent via-red-100 to-transparent"></div>
            <div class="absolute -left-12 -top-12 w-40 h-40 bg-red-50 rounded-full blur-3xl opacity-50"></div>
            <div class="absolute -right-12 -bottom-12 w-40 h-40 bg-green-50 rounded-full blur-3xl opacity-50"></div>
            
            <div class="relative z-10">
                <div class="w-48 h-48 mx-auto mb-10 bg-gray-50/50 rounded-full flex items-center justify-center relative shadow-inner">
                    <div class="absolute top-0 right-4 w-12 h-12 bg-red-100 rounded-full animate-ping opacity-30"></div>
                    <div class="absolute bottom-4 left-4 w-8 h-8 bg-green-100 rounded-full animate-pulse opacity-40"></div>
                    <i class="fa-solid fa-basket-shopping text-7xl text-gray-300 drop-shadow-sm"></i>
                </div>
                <h2 class="text-3xl md:text-4xl font-black text-[#10271d] mb-4 tracking-tight">Giỏ hàng đang trống</h2>
                <p class="text-gray-500 mb-10 text-lg md:text-xl font-medium max-w-md mx-auto">Có vẻ như bạn chưa chọn được sản phẩm nào. Hãy khám phá ngay các bộ sưu tập mới nhất của chúng tôi!</p>
                <a href="{{ route('client.products.index') }}" class="group inline-flex items-center justify-center bg-[#10271d] text-white px-10 py-4 rounded-2xl font-bold hover:bg-[#d92525] transition-all shadow-xl shadow-[#10271d]/20 hover:shadow-red-500/30 hover:-translate-y-1">
                    <span>Khám phá sản phẩm ngay</span>
                    <i class="fa-solid fa-arrow-right-long ml-3 transition-transform group-hover:translate-x-2"></i>
                </a>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function showCartError(msg) {
    const errorHtml = `
        <div id="dynamic-alert-error" class="fixed top-24 right-4 z-[9999] max-w-sm rounded-xl border border-red-200 bg-red-50 px-6 py-4 text-sm font-semibold text-red-800 shadow-2xl flex items-center gap-3 transition-all duration-500 translate-y-[-20px] opacity-0">
            <i class="fa-solid fa-circle-exclamation text-xl"></i>
            ${msg}
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', errorHtml);
    
    // Animate in
    setTimeout(() => {
        const el = document.getElementById('dynamic-alert-error');
        if (el) {
            el.classList.remove('translate-y-[-20px]', 'opacity-0');
            el.classList.add('translate-y-0', 'opacity-100');
        }
    }, 10);

    // Animate out
    setTimeout(() => {
        const el = document.getElementById('dynamic-alert-error');
        if (el) {
            el.classList.remove('translate-y-0', 'opacity-100');
            el.classList.add('translate-y-[-20px]', 'opacity-0');
            setTimeout(() => el.remove(), 500);
        }
    }, 4000);
}

function updateQuantity(btn, action) {
    const input = action === 'up' ? btn.previousElementSibling : btn.nextElementSibling;
    const max = parseInt(input.getAttribute('max'));
    let val = parseInt(input.value);

    if (action === 'up') {
        if (val >= max) {
            showCartError('Số lượng vượt quá tồn kho!');
            input.value = max;
            return;
        }
        input.stepUp();
    } else {
        if (val <= 1) return;
        input.stepDown();
    }
    
    // Thêm hiệu ứng loading nhỏ
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i>';
    btn.form.submit();
}

function checkQuantity(input) {
    const max = parseInt(input.getAttribute('max'));
    let val = parseInt(input.value);
    
    if (val > max) {
        showCartError('Số lượng vượt quá tồn kho!');
        input.value = max;
        return;
    }
    input.form.submit();
}
</script>
@endpush
