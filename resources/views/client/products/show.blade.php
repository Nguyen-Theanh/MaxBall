@extends('client.layouts.app')

@section('title', $product->name)

@section('content')
@php
    $price = $product->discount_price ?: $product->base_price;
    $oldPrice = $product->base_price;

    $thumbnail = $product->thumbnail_url ?? null;

    if (!$thumbnail && !empty($product->thumbnail)) {
        if (str_starts_with($product->thumbnail, 'http')) {
            $thumbnail = $product->thumbnail;
        } else {
            $thumbnail = asset('storage/' . $product->thumbnail);
        }
    }

    if (!$thumbnail) {
        $thumbnail = 'https://via.placeholder.com/600x750?text=No+Image';
    }
@endphp

<section class="bg-[#10271d] pt-32 pb-12">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-sm text-gray-300">
            <a href="{{ route('client.home') }}" class="hover:text-white transition">Trang chủ</a>
            <span class="mx-2">/</span>
            <a href="{{ route('client.products.index') }}" class="hover:text-white transition">Sản phẩm</a>
            <span class="mx-2">/</span>
            <span class="text-white font-bold">{{ $product->name }}</span>
        </div>
    </div>
</section>

<div class="max-w-7xl mx-auto px-4 py-10">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-10 bg-white rounded-2xl shadow p-6">
        <div>
            <div class="border rounded-2xl overflow-hidden bg-gray-100">
                <img src="{{ $thumbnail }}"
                     alt="{{ $product->name }}"
                     class="w-full h-[500px] object-cover">
            </div>

            @if(isset($product->productImages) && $product->productImages->count())
                <div class="grid grid-cols-4 gap-3 mt-4">
                    @foreach($product->productImages as $image)
                        <img src="{{ $image->url }}"
                             alt="{{ $product->name }}"
                             class="w-full h-24 object-cover rounded-xl border">
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            <p class="text-sm uppercase font-bold text-red-600 mb-2">
                {{ $product->category->name ?? 'Danh mục' }}
            </p>

            <h1 class="text-4xl font-black text-gray-900 mb-4">
                {{ $product->name }}
            </h1>

            <div class="flex items-center gap-4 mb-4">
                <span class="text-3xl font-black text-red-600" id="display-price">
                    {{ number_format($price, 0, ',', '.') }}đ
                </span>

                @if(!empty($product->discount_price))
                    <span class="text-gray-400 line-through text-xl" id="display-old-price">
                        {{ number_format($oldPrice, 0, ',', '.') }}đ
                    </span>
                @else
                    <span class="text-gray-400 line-through text-xl hidden" id="display-old-price"></span>
                @endif
            </div>

            <div class="text-sm text-gray-500 mb-6 flex items-center gap-2">
                Kho: <span id="display-stock" class="font-bold text-gray-900">{{ $product->variants->sum('stock') > 0 ? $product->variants->sum('stock') : 'Hết hàng' }}</span>
            </div>

            <div class="mb-6">
                <h3 class="font-bold mb-2">Mô tả sản phẩm</h3>
                <p class="text-gray-700 leading-7">
                    {{ $product->description ?? 'Chưa có mô tả sản phẩm.' }}
                </p>
            </div>

            @if(isset($product->variants) && $product->variants->count())
                <div class="mb-6">
                    <h3 class="font-bold mb-3">Phân loại</h3>
                    <div class="flex flex-wrap gap-2" id="variant-container">
                        @foreach($product->variants as $variant)
                            <button type="button" 
                                    class="variant-btn px-4 py-2 border rounded-lg hover:border-red-600 focus:outline-none transition-colors"
                                    data-id="{{ $variant->id }}"
                                    data-price="{{ $variant->discount_price ?: $variant->base_price }}"
                                    data-old-price="{{ $variant->discount_price ? $variant->base_price : '' }}"
                                    data-stock="{{ $variant->stock }}">
                                {{ $variant->name ?? $variant->sku ?? 'Biến thể' }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            <form id="add-to-cart-form" action="{{ route('client.cart.store') ?? '#' }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="product_variant_id" id="selected_variant_id" value="">
                
                <div class="mb-8">
                    <h3 class="font-bold mb-3">Số lượng</h3>
                    <div class="inline-flex items-center border rounded-lg">
                        <button type="button" id="btn-decrease-qty" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-l-lg transition-colors">-</button>
                        <input type="number" name="quantity" id="quantity-input" value="1" min="1" max="{{ $product->variants->max('stock') ?: 1 }}" class="w-16 text-center border-x-0 border-y-0 focus:ring-0 appearance-none m-0 p-2">
                        <button type="button" id="btn-increase-qty" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-r-lg transition-colors">+</button>
                    </div>
                </div>

                <div class="flex gap-4 mt-8">
                    <button type="submit" name="action" value="add_cart" class="flex-1 bg-white text-red-600 border border-red-600 py-4 rounded-xl font-bold hover:bg-red-50 transition-colors">
                        Thêm vào giỏ hàng
                    </button>

                    <button type="submit" name="action" value="buy_now" class="flex-1 bg-red-600 text-white py-4 rounded-xl font-bold hover:bg-red-700 transition-colors shadow-lg shadow-red-200">
                        Mua ngay
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($relatedProducts->count())
        <div class="mt-14">
            <h2 class="text-2xl font-black mb-6">Sản phẩm liên quan</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedProducts as $item)
                    @php
                        $itemPrice = $item->discount_price ?: $item->base_price;
                        $itemImage = $item->thumbnail_url ?? null;

                        if (!$itemImage && !empty($item->thumbnail)) {
                            if (str_starts_with($item->thumbnail, 'http')) {
                                $itemImage = $item->thumbnail;
                            } else {
                                $itemImage = asset('storage/' . $item->thumbnail);
                            }
                        }

                        if (!$itemImage) {
                            $itemImage = 'https://via.placeholder.com/400x500?text=No+Image';
                        }
                    @endphp

                    <a href="{{ route('client.products.show', $item->slug) }}"
                       class="block bg-white border rounded-2xl overflow-hidden hover:shadow-lg transition no-underline">
                        <img src="{{ $itemImage }}"
                             alt="{{ $item->name }}"
                             class="w-full h-64 object-cover bg-gray-100">

                        <div class="p-4">
                            <h3 class="font-bold text-gray-900 line-clamp-2">
                                {{ $item->name }}
                            </h3>      

                            <p class="text-red-600 font-black mt-2">
                                {{ number_format($itemPrice, 0, ',', '.') }}đ
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const variantBtns = document.querySelectorAll('.variant-btn');
    const displayPrice = document.getElementById('display-price');
    const displayOldPrice = document.getElementById('display-old-price');
    const displayStock = document.getElementById('display-stock');
    const selectedVariantInput = document.getElementById('selected_variant_id');
    const qtyInput = document.getElementById('quantity-input');
    const btnDecrease = document.getElementById('btn-decrease-qty');
    const btnIncrease = document.getElementById('btn-increase-qty');
    const cartForm = document.getElementById('add-to-cart-form');
    
    let currentMaxStock = {{ $product->variants->sum('stock') }};
    const formatCurrency = (amount) => new Intl.NumberFormat('vi-VN').format(amount) + 'đ';

    variantBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active state from all
            variantBtns.forEach(b => {
                b.classList.remove('border-red-600', 'text-red-600', 'bg-red-50');
            });
            
            // Add active state to clicked
            this.classList.add('border-red-600', 'text-red-600', 'bg-red-50');
            
            // Update data
            const id = this.dataset.id;
            const price = this.dataset.price;
            const oldPrice = this.dataset.oldPrice;
            const stock = parseInt(this.dataset.stock) || 0;
            
            selectedVariantInput.value = id;
            currentMaxStock = stock;
            qtyInput.max = stock;
            
            // Reset quantity if it exceeds new stock
            if (parseInt(qtyInput.value) > stock && stock > 0) {
                qtyInput.value = stock;
            } else if (stock === 0) {
                qtyInput.value = 1; // Or 0 if you want to strictly prevent adding
            }

            // Update UI
            displayPrice.textContent = formatCurrency(price);
            
            if (oldPrice) {
                displayOldPrice.textContent = formatCurrency(oldPrice);
                displayOldPrice.classList.remove('hidden');
            } else {
                displayOldPrice.classList.add('hidden');
            }
            
            displayStock.textContent = stock;
            
            if (stock <= 0) {
                displayStock.textContent = 'Hết hàng';
                displayStock.classList.add('text-red-600');
            } else {
                displayStock.classList.remove('text-red-600');
            }
        });
    });

    btnIncrease.addEventListener('click', () => {
        let val = parseInt(qtyInput.value) || 1;
        if (selectedVariantInput.value === '') {
            alert('Vui lòng chọn phân loại hàng trước!');
            return;
        }
        if (val < currentMaxStock) {
            qtyInput.value = val + 1;
        } else {
            alert('Số lượng bạn chọn đã đạt mức tối đa của sản phẩm này');
        }
    });

    btnDecrease.addEventListener('click', () => {
        let val = parseInt(qtyInput.value) || 1;
        if (val > 1) {
            qtyInput.value = val - 1;
        }
    });

    qtyInput.addEventListener('change', function() {
        let val = parseInt(this.value) || 1;
        if (selectedVariantInput.value === '') {
            alert('Vui lòng chọn phân loại hàng trước!');
            this.value = 1;
            return;
        }
        if (val > currentMaxStock) {
            alert('Số lượng bạn chọn đã đạt mức tối đa của sản phẩm này');
            this.value = currentMaxStock;
        } else if (val < 1) {
            this.value = 1;
        }
    });

    cartForm.addEventListener('submit', function(e) {
        if (!selectedVariantInput.value && variantBtns.length > 0) {
            e.preventDefault();
            alert('Vui lòng chọn phân loại hàng trước khi thêm vào giỏ hàng hoặc mua ngay.');
        } else if (currentMaxStock <= 0) {
            e.preventDefault();
            alert('Sản phẩm này đã hết hàng.');
        }
    });
});
</script>
@endpush
@endsection       