@extends('client.layouts.app')

@section('title', $product->name)

@section('content')
@php
    $price = $product->discount_price ?: $product->base_price;
    $oldPrice = $product->base_price;
    $thumbnail = $product->thumbnail_url ?: 'https://via.placeholder.com/600x750?text=No+Image';

    $galleryImages = collect([[
        'url' => $thumbnail,
        'label' => $product->name,
    ]]);

    foreach ($product->productImages as $image) {
        if ($image->url) {
            $galleryImages->push([
                'url' => $image->url,
                'label' => $product->name,
            ]);
        }
    }

    foreach ($product->variants as $variant) {
        if ($variant->variant_image_url) {
            $galleryImages->push([
                'url' => $variant->variant_image_url,
                'label' => $variant->name ?: $product->name,
            ]);
        }
    }

    $galleryImages = $galleryImages->unique('url')->values();

    $normalize = function ($value) {
        $value = trim((string) $value);
        return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    };

    $valueLookup = [];
    $attributeOrder = [];

    foreach ($attributes as $attribute) {
        $attributeOrder[] = $attribute->name;

        foreach ($attribute->values as $value) {
            $valueLookup[$normalize($value->value)] = [
                'attribute' => $attribute->name,
                'value' => $value->value,
            ];
        }
    }

    $variantData = collect();
    $attributeOptions = [];
    $availableAttributeValues = [];

    foreach ($product->variants as $variant) {
        $parts = collect(explode(' - ', (string) $variant->name))
            ->map(fn ($part) => trim($part))
            ->filter()
            ->values();

        $options = [];

        foreach ($parts as $part) {
            $matched = $valueLookup[$normalize($part)] ?? null;

            if ($matched) {
                $options[$matched['attribute']] = $matched['value'];
            } else {
                $options['Phân loại'] = $part;
            }
        }

        if (empty($options)) {
            $options['Phân loại'] = $variant->name ?: ($variant->sku ?: 'Mặc định');
        }

        foreach ($options as $attributeName => $valueName) {
            $attributeOptions[$attributeName] ??= [];
            $attributeOptions[$attributeName][$valueName] = $valueName;

            if ((int) $variant->stock > 0) {
                $availableAttributeValues[$attributeName][$valueName] = true;
            }
        }

        $variantData->push([
            'id' => $variant->id,
            'name' => $variant->name ?: ($variant->sku ?: 'Biến thể'),
            'sku' => $variant->sku,
            'price' => $variant->discount_price ?: $variant->base_price,
            'old_price' => $variant->discount_price ? $variant->base_price : null,
            'stock' => $variant->stock,
            'image' => $variant->variant_image_url,
            'options' => $options,
        ]);
    }

    $orderedAttributeOptions = [];

    foreach ($attributeOrder as $attributeName) {
        if (!empty($attributeOptions[$attributeName])) {
            $orderedAttributeOptions[$attributeName] = array_values($attributeOptions[$attributeName]);
        }
    }

    foreach ($attributeOptions as $attributeName => $values) {
        if (!isset($orderedAttributeOptions[$attributeName])) {
            $orderedAttributeOptions[$attributeName] = array_values($values);
        }
    }

    $totalStock = (int) $product->variants->sum('stock');
    $averageRating = (float) ($product->reviews_avg_rating ?? 0);
    $isDefaultVariantOnly = $variantData->count() === 1 && $variantData->first()['name'] === 'Mặc định';
@endphp

<div class="max-w-7xl mx-auto px-4 pt-32 pb-4">
    <!-- Breadcrumbs -->
    <div class="text-sm text-gray-500 mb-4 flex items-center gap-2">
        <a href="{{ route('client.home') }}" class="hover:text-red-600 transition">Trang chủ</a>
        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        <a href="{{ route('client.products.index') }}" class="hover:text-red-600 transition">Sản phẩm</a>
        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        <span class="text-gray-900 truncate">{{ $product->name }}</span>
    </div>

    <!-- Product Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 bg-white rounded-2xl shadow p-6">
        <div class="lg:col-span-5">
            <button type="button"
                    id="main-image-button"
                    class="block w-full border rounded-2xl overflow-hidden bg-gray-100 cursor-zoom-in focus:outline-none focus:ring-2 focus:ring-red-500">
                <img src="{{ $galleryImages->first()['url'] }}"
                     alt="{{ $product->name }}"
                     id="main-product-image"
                     class="w-full aspect-[4/5] max-h-[460px] object-cover">
            </button>

            @if($galleryImages->count() > 1)
                <div class="grid grid-cols-4 sm:grid-cols-5 gap-3 mt-4" id="gallery-thumbnails">
                    @foreach($galleryImages as $index => $image)
                        <button type="button"
                                class="gallery-thumb border rounded-xl overflow-hidden bg-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 {{ $index === 0 ? 'ring-2 ring-red-500 border-red-500' : 'hover:border-red-500' }}"
                                data-image="{{ $image['url'] }}"
                                data-label="{{ $image['label'] }}">
                            <img src="{{ $image['url'] }}"
                                 alt="{{ $image['label'] }}"
                                 class="w-full h-24 object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="lg:col-span-7">
            <p class="text-sm uppercase font-bold text-red-600 mb-2">
                {{ $product->category->name ?? 'Danh mục' }}
            </p>

            <h1 class="text-4xl font-black text-gray-900 mb-4">
                {{ $product->name }}
            </h1>

            <a href="#product-reviews" class="mb-4 flex w-fit items-center gap-2 text-sm no-underline">
                <span class="text-lg tracking-tight text-yellow-400">
                    @for($star = 1; $star <= 5; $star++)
                        <span class="{{ $star <= round($averageRating) ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                    @endfor
                </span>
                @if($product->reviews_count > 0)
                    <span class="font-bold text-gray-800">{{ number_format($averageRating, 1) }}/5</span>
                    <span class="text-gray-500">({{ $product->reviews_count }} đánh giá)</span>
                @else
                    <span class="text-gray-500">Chưa có đánh giá</span>
                @endif
            </a>

            <div class="flex items-center gap-4 mb-4">
                <span class="text-3xl font-black text-red-600" id="display-price">
                    {{ number_format($price, 0, ',', '.') }}đ
                </span>

                <span class="text-gray-400 line-through text-xl {{ empty($product->discount_price) ? 'hidden' : '' }}" id="display-old-price">
                    {{ !empty($product->discount_price) ? number_format($oldPrice, 0, ',', '.') . 'đ' : '' }}
                </span>
            </div>

            <!-- Shopee Style Extra Info -->
            <div class="flex flex-col gap-5 mb-6 bg-gray-50/50 p-4 rounded-xl border border-gray-100">
                <!-- Mã Giảm Giá -->
                <div class="flex flex-col sm:flex-row sm:items-start gap-2 sm:gap-4">
                    <span class="text-sm text-gray-500 sm:w-28 shrink-0 mt-1">Mã Giảm Giá</span>
                    <div class="flex items-center flex-wrap gap-3">
                        <button type="button" onclick="openVoucherModal()" class="text-[#d92525] font-bold text-sm hover:underline flex items-center gap-1">
                            Chọn Voucher <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- An Tâm Mua Sắm -->
                <div class="flex flex-col sm:flex-row sm:items-start gap-2 sm:gap-4 relative group cursor-pointer">
                    <span class="text-sm text-gray-500 sm:w-28 shrink-0 mt-0.5">An Tâm Mua Sắm</span>
                    <div class="flex items-center flex-wrap gap-1.5 text-sm text-gray-900">
                        <svg class="w-4 h-4 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        <span>Trả hàng miễn phí 15 ngày <span class="text-gray-300 mx-1">•</span> Chính hãng 100% <span class="text-gray-300 mx-1">•</span> Miễn phí vận chuyển</span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>

                    <!-- Tooltip / Dropdown on hover -->
                    <div class="absolute top-full left-0 sm:left-32 mt-2 w-80 max-w-full bg-white shadow-xl border border-gray-100 rounded-xl p-4 z-50 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                        <h4 class="font-bold text-gray-900 mb-3 border-b pb-2">An tâm mua sắm cùng MaxBall</h4>
                        <ul class="space-y-3">
                            <li class="flex gap-3">
                                <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                <div>
                                    <div class="font-semibold text-sm text-gray-900">Trả hàng miễn phí 15 ngày</div>
                                    <div class="text-xs text-gray-500 mt-0.5">Miễn phí Trả hàng trong 15 ngày nếu sản phẩm lỗi.</div>
                                </div>
                            </li>
                            <li class="flex gap-3">
                                <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <div>
                                    <div class="font-semibold text-sm text-gray-900">Chính hãng 100%</div>
                                    <div class="text-xs text-gray-500 mt-0.5">Cam kết đền bù nếu phát hiện hàng giả, hàng nhái.</div>
                                </div>
                            </li>
                            <li class="flex gap-3">
                                <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                                <div>
                                    <div class="font-semibold text-sm text-gray-900">Miễn phí vận chuyển</div>
                                    <div class="text-xs text-gray-500 mt-0.5">Ưu đãi miễn phí vận chuyển cho đơn hàng trên 500k.</div>
                                </div>
                            </li>
                            <li class="flex gap-3">
                                <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                <div>
                                    <div class="font-semibold text-sm text-gray-900">Bảo hiểm MaxBall</div>
                                    <div class="text-xs text-gray-500 mt-0.5">Sản phẩm được bảo hành uy tín từ cửa hàng.</div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="text-sm mb-6 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                <span class="text-gray-500 sm:w-28 shrink-0">Kho hàng</span>
                <span id="display-stock" class="font-bold text-gray-900">
                    {{ $totalStock > 0 ? $totalStock : 'Hết hàng' }}
                </span>
            </div>



            @if($variantData->count() > 0 && !$isDefaultVariantOnly)
                <div class="mb-6">
                    <h3 class="font-bold mb-3">Phân loại</h3>

                    <div class="space-y-4" id="variant-options">
                        @foreach($orderedAttributeOptions as $attributeName => $values)
                            <div class="variant-option-group" data-attribute="{{ $attributeName }}">
                                <div class="text-sm font-semibold text-gray-700 mb-2">{{ $attributeName }}</div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($values as $value)
                                        @php
                                            $isOptionInStock = isset($availableAttributeValues[$attributeName][$value]);
                                        @endphp
                                        <button type="button"
                                                class="option-btn px-4 py-2 border rounded-lg text-sm font-semibold hover:border-red-600 focus:outline-none transition-colors disabled:cursor-not-allowed disabled:border-gray-200 disabled:bg-gray-100 disabled:text-gray-400 disabled:opacity-50 disabled:line-through disabled:hover:border-gray-200"
                                                data-attribute="{{ $attributeName }}"
                                                data-value="{{ $value }}"
                                                @disabled(! $isOptionInStock)
                                                aria-disabled="{{ $isOptionInStock ? 'false' : 'true' }}"
                                                title="{{ $isOptionInStock ? '' : 'Phân loại này đã hết hàng' }}">
                                            {{ $value }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <p class="text-sm text-gray-500 mt-3" id="variant-helper">
                        Vui lòng chọn đủ phân loại để xem đúng giá và tồn kho.
                    </p>
                </div>
            @endif

            <form id="add-to-cart-form" action="{{ route('client.cart.store') ?? '#' }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="product_variant_id" id="selected_variant_id" value="{{ $isDefaultVariantOnly ? $variantData->first()['id'] : '' }}">

                <div class="mb-6">
                    <h3 class="font-bold mb-3">Số lượng</h3>
                    <div class="inline-flex items-center border rounded-lg">
                        <button type="button" id="btn-decrease-qty" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-l-lg transition-colors">-</button>
                        <input type="number" name="quantity" id="quantity-input" value="1" min="1" max="{{ max($totalStock, 1) }}" class="w-16 text-center border-x-0 border-y-0 focus:ring-0 appearance-none m-0 p-2">
                        <button type="button" id="btn-increase-qty" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-r-lg transition-colors">+</button>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 mt-6">
                    <button type="submit" name="action" value="add_cart" class="flex-1 bg-white text-red-600 border border-red-600 py-3.5 rounded-xl font-bold hover:bg-red-50 transition-colors">
                        Thêm vào giỏ hàng
                    </button>

                    <button type="submit" name="action" value="buy_now" class="flex-1 bg-red-600 text-white py-3.5 rounded-xl font-bold hover:bg-red-700 transition-colors shadow-lg shadow-red-200">
                        Mua ngay
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Section Mô tả sản phẩm -->
    <section class="mt-8 rounded-2xl bg-white p-6 shadow border border-gray-100">
        <h2 class="text-2xl font-black text-gray-900 mb-4 border-b pb-4">Chi tiết & Mô tả sản phẩm</h2>
        <div class="prose max-w-none text-gray-700 leading-8">
            {!! nl2br(e($product->description ?? 'Chưa có mô tả chi tiết cho sản phẩm này.')) !!}
        </div>
    </section>

    <section id="product-reviews" class="mt-8 rounded-2xl bg-white p-6 shadow">
        <div class="flex flex-col gap-5 border-b pb-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-black text-gray-900">Đánh giá sản phẩm</h2>
                <p class="mt-1 text-sm text-gray-500">Đánh giá từ khách hàng đã mua sản phẩm tại MaxBall.</p>
            </div>
            <div class="rounded-xl bg-yellow-50 px-6 py-4 text-center">
                <div class="text-3xl font-black text-gray-900">
                    {{ $product->reviews_count > 0 ? number_format($averageRating, 1) : '0.0' }}
                    <span class="text-base font-medium text-gray-500">/ 5</span>
                </div>
                <div class="mt-1 text-lg text-yellow-400">
                    @for($star = 1; $star <= 5; $star++)
                        <span class="{{ $star <= round($averageRating) ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                    @endfor
                </div>
                <p class="mt-1 text-xs text-gray-500">{{ $product->reviews_count }} đánh giá</p>
            </div>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($reviews as $review)
                <article class="py-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-bold text-gray-900">
                                    {{ $review->is_admin_review ? ($review->public_name ?: 'Khách hàng MaxBall') : ($review->user?->name ?? 'Khách hàng MaxBall') }}
                                </span>
                                @if(! $review->is_admin_review && $review->order_detail_id)
                                    <span class="rounded-full bg-green-50 px-2 py-1 text-[11px] font-bold text-green-700">Đã mua hàng</span>
                                @endif
                            </div>
                            @if($review->orderDetail?->variant)
                                <p class="mt-1 text-xs text-gray-500">Phân loại: {{ $review->orderDetail->variant->name }}</p>
                            @endif
                        </div>
                        <time class="text-xs text-gray-400">{{ $review->created_at->format('d/m/Y H:i') }}</time>
                    </div>

                    <div class="mt-3 text-lg text-yellow-400" aria-label="{{ $review->rating }} trên 5 sao">
                        @for($star = 1; $star <= 5; $star++)
                            <span class="{{ $star <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                        @endfor
                    </div>

                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-gray-700">
                        {{ $review->content ?: 'Khách hàng đã đánh giá sản phẩm này.' }}
                    </p>

                    @if($review->media->isNotEmpty())
                        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5">
                            @foreach($review->media as $media)
                                @if($media->type === 'video')
                                    <video
                                        src="{{ $media->url }}"
                                        class="h-32 w-full rounded-xl border border-gray-200 bg-black object-cover"
                                        controls
                                        preload="metadata"
                                    ></video>
                                @else
                                    <a href="{{ $media->url }}" target="_blank" rel="noopener noreferrer" class="block overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                                        <img
                                            src="{{ $media->url }}"
                                            alt="Ảnh đánh giá sản phẩm"
                                            class="h-32 w-full object-cover transition hover:scale-105"
                                            loading="lazy"
                                        >
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </article>
            @empty
                <div class="py-12 text-center">
                    <div class="text-5xl text-gray-200">★</div>
                    <p class="mt-3 font-bold text-gray-700">Sản phẩm chưa có đánh giá</p>
                    <p class="mt-1 text-sm text-gray-500">Hãy là khách hàng đầu tiên chia sẻ trải nghiệm sau khi mua hàng.</p>
                </div>
            @endforelse
        </div>

        @if($reviews->hasPages())
            <div class="border-t border-gray-100 pt-5">
                {{ $reviews->onEachSide(1)->links('pagination::tailwind') }}
            </div>
        @endif
    </section>

    @if($relatedProducts->count())
        <div class="mt-14">
            <h2 class="text-2xl font-black mb-6">Sản phẩm liên quan</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedProducts as $item)
                    @php
                        $itemPrice = $item->discount_price ?: $item->base_price;
                        $itemImage = $item->thumbnail_url ?: 'https://via.placeholder.com/400x500?text=No+Image';
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

<div id="image-lightbox" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 px-4">
    <button type="button"
            id="close-lightbox"
            class="absolute right-4 top-4 rounded-full bg-white px-4 py-2 text-sm font-bold text-gray-900 hover:bg-gray-100">
        Đóng
    </button>
    <img src="{{ $galleryImages->first()['url'] }}"
         alt="{{ $product->name }}"
         id="lightbox-image"
         class="max-h-[88vh] max-w-full rounded-xl object-contain bg-white">
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const variants = @json($variantData->values());
    const requiredAttributes = @json(array_keys($orderedAttributeOptions));
    const hasVariants = variants.length > 0;
    const productName = @json($product->name);

    const displayPrice = document.getElementById('display-price');
    const displayOldPrice = document.getElementById('display-old-price');
    const displayStock = document.getElementById('display-stock');
    const selectedVariantInput = document.getElementById('selected_variant_id');
    const qtyInput = document.getElementById('quantity-input');
    const btnDecrease = document.getElementById('btn-decrease-qty');
    const btnIncrease = document.getElementById('btn-increase-qty');
    const cartForm = document.getElementById('add-to-cart-form');
    const optionButtons = document.querySelectorAll('.option-btn');
    const variantHelper = document.getElementById('variant-helper');
    const mainImage = document.getElementById('main-product-image');
    const mainImageButton = document.getElementById('main-image-button');
    const galleryThumbs = document.querySelectorAll('.gallery-thumb');
    const lightbox = document.getElementById('image-lightbox');
    const lightboxImage = document.getElementById('lightbox-image');
    const closeLightbox = document.getElementById('close-lightbox');

    let selectedOptions = {};
    let currentMaxStock = {{ $totalStock }};

    const formatCurrency = (amount) => new Intl.NumberFormat('vi-VN').format(Number(amount) || 0) + 'đ';

    const setMainImage = (src, label = '') => {
        if (!src) {
            return;
        }

        mainImage.src = src;
        mainImage.alt = label || productName;
        lightboxImage.src = src;
        lightboxImage.alt = label || productName;

        galleryThumbs.forEach((thumb) => {
            thumb.classList.toggle('ring-2', thumb.dataset.image === src);
            thumb.classList.toggle('ring-red-500', thumb.dataset.image === src);
            thumb.classList.toggle('border-red-500', thumb.dataset.image === src);
        });
    };

    const findMatchingVariant = (options) => {
        return variants.find((variant) => {
            return requiredAttributes.every((attribute) => variant.options[attribute] === options[attribute]);
        });
    };

    const isOptionAvailable = (attributeName, valueName) => {
        const nextOptions = { ...selectedOptions, [attributeName]: valueName };

        return variants.some((variant) => {
            return Number(variant.stock) > 0 && Object.entries(nextOptions).every(([attribute, value]) => {
                return !value || variant.options[attribute] === value;
            });
        });
    };

    const refreshOptionAvailability = () => {
        optionButtons.forEach((button) => {
            const isAvailable = isOptionAvailable(button.dataset.attribute, button.dataset.value);

            button.disabled = !isAvailable;
            button.setAttribute('aria-disabled', isAvailable ? 'false' : 'true');
            button.title = isAvailable ? '' : 'Phân loại này đã hết hàng';
        });
    };

    const updateVariantDisplay = () => {
        const selectedCount = Object.keys(selectedOptions).filter((attribute) => selectedOptions[attribute]).length;
        const isComplete = selectedCount === requiredAttributes.length;
        const matchedVariant = isComplete ? findMatchingVariant(selectedOptions) : null;
        const matchedVariantInStock = matchedVariant && Number(matchedVariant.stock) > 0;

        selectedVariantInput.value = matchedVariantInStock ? matchedVariant.id : '';

        if (!isComplete) {
            variantHelper.textContent = 'Vui lòng chọn đủ phân loại để xem đúng giá và tồn kho.';
            refreshOptionAvailability();
            return;
        }

        if (!matchedVariant) {
            variantHelper.textContent = 'Phân loại này hiện chưa có biến thể.';
            currentMaxStock = 0;
            qtyInput.max = 1;
            displayStock.textContent = 'Hết hàng';
            displayStock.classList.add('text-red-600');
            refreshOptionAvailability();
            return;
        }

        currentMaxStock = Number(matchedVariant.stock) || 0;
        qtyInput.max = Math.max(currentMaxStock, 1);

        if (Number(qtyInput.value) > currentMaxStock && currentMaxStock > 0) {
            qtyInput.value = currentMaxStock;
        }

        displayPrice.textContent = formatCurrency(matchedVariant.price);

        if (matchedVariant.old_price) {
            displayOldPrice.textContent = formatCurrency(matchedVariant.old_price);
            displayOldPrice.classList.remove('hidden');
        } else {
            displayOldPrice.textContent = '';
            displayOldPrice.classList.add('hidden');
        }

        if (currentMaxStock <= 0) {
            displayStock.textContent = 'Hết hàng';
            displayStock.classList.add('text-red-600');
            variantHelper.textContent = 'Phân loại này hiện đã hết hàng.';
        } else {
            displayStock.textContent = currentMaxStock;
            displayStock.classList.remove('text-red-600');
            variantHelper.textContent = `Đã chọn: ${matchedVariant.name}`;
        }

        if (matchedVariant.image) {
            setMainImage(matchedVariant.image, matchedVariant.name);
        }

        refreshOptionAvailability();
    };

    galleryThumbs.forEach((thumb) => {
        thumb.addEventListener('click', () => {
            setMainImage(thumb.dataset.image, thumb.dataset.label);
        });
    });

    mainImageButton.addEventListener('click', () => {
        lightbox.classList.remove('hidden');
        lightbox.classList.add('flex');
    });

    closeLightbox.addEventListener('click', () => {
        lightbox.classList.add('hidden');
        lightbox.classList.remove('flex');
    });

    lightbox.addEventListener('click', (event) => {
        if (event.target === lightbox) {
            lightbox.classList.add('hidden');
            lightbox.classList.remove('flex');
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            lightbox.classList.add('hidden');
            lightbox.classList.remove('flex');
        }
    });

    optionButtons.forEach((button) => {
        button.addEventListener('click', function() {
            const attributeName = this.dataset.attribute;
            const valueName = this.dataset.value;

            selectedOptions[attributeName] = selectedOptions[attributeName] === valueName ? '' : valueName;

            optionButtons.forEach((item) => {
                if (item.dataset.attribute !== attributeName) {
                    return;
                }

                const isActive = selectedOptions[attributeName] && item.dataset.value === selectedOptions[attributeName];
                item.classList.toggle('border-red-600', isActive);
                item.classList.toggle('text-red-600', isActive);
                item.classList.toggle('bg-red-50', isActive);
            });

            updateVariantDisplay();
        });
    });

    btnIncrease.addEventListener('click', () => {
        const val = Number(qtyInput.value) || 1;

        if (hasVariants && !selectedVariantInput.value) {
            window.AppConfirm.alert({
                title: 'Chưa chọn phân loại',
                message: 'Vui lòng chọn đầy đủ phân loại hàng trước khi thay đổi số lượng.',
            });
            return;
        }

        if (val < currentMaxStock) {
            qtyInput.value = val + 1;
        } else {
            window.AppConfirm.alert({
                title: 'Đã đạt số lượng tối đa',
                message: 'Số lượng bạn chọn đã đạt mức tồn kho hiện tại của sản phẩm.',
            });
        }
    });

    btnDecrease.addEventListener('click', () => {
        const val = Number(qtyInput.value) || 1;

        if (val > 1) {
            qtyInput.value = val - 1;
        }
    });

    qtyInput.addEventListener('change', function() {
        let val = Number(this.value) || 1;

        if (hasVariants && !selectedVariantInput.value) {
            window.AppConfirm.alert({
                title: 'Chưa chọn phân loại',
                message: 'Vui lòng chọn đầy đủ phân loại hàng trước khi nhập số lượng.',
            });
            this.value = 1;
            return;
        }

        if (val > currentMaxStock) {
            window.AppConfirm.alert({
                title: 'Số lượng không hợp lệ',
                message: 'Số lượng bạn chọn vượt quá tồn kho hiện tại của sản phẩm.',
            });
            this.value = Math.max(currentMaxStock, 1);
        } else if (val < 1) {
            this.value = 1;
        }
    });

    cartForm.addEventListener('submit', function(e) {
        if (hasVariants && !selectedVariantInput.value) {
            e.preventDefault();
            window.AppConfirm.alert({
                title: 'Chưa chọn phân loại',
                message: 'Vui lòng chọn đầy đủ phân loại sản phẩm trước khi thêm vào giỏ hàng hoặc mua ngay.',
            });
            return;
        }

        if (currentMaxStock <= 0) {
            e.preventDefault();
            window.AppConfirm.alert({
                title: 'Sản phẩm đã hết hàng',
                message: 'Phân loại bạn chọn hiện đã hết hàng. Vui lòng chọn phân loại khác.',
            });
        }
    });

    refreshOptionAvailability();
});
    </script>
@endpush

<!-- Voucher Modal -->
<div id="voucherModal" class="fixed inset-0 z-[100] hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeVoucherModal()"></div>
    
    <!-- Modal Content -->
    <div class="absolute inset-x-0 bottom-0 sm:inset-auto sm:top-1/2 sm:left-1/2 sm:-translate-x-1/2 sm:-translate-y-1/2 bg-[#f6f6f6] sm:rounded-2xl shadow-2xl w-full sm:max-w-lg transition-all transform flex flex-col max-h-[85vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-white sm:rounded-t-2xl shrink-0">
            <h3 class="text-xl font-bold text-gray-900">Voucher của Shop</h3>
            <button onclick="closeVoucherModal()" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2 rounded-full transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 overflow-y-auto" id="voucherModalBody">
            <div class="flex justify-center py-8">
                <svg class="animate-spin h-8 w-8 text-[#d92525]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    function openVoucherModal() {
        document.getElementById('voucherModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        fetchVouchers();
    }

    function closeVoucherModal() {
        document.getElementById('voucherModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function fetchVouchers() {
        fetch('{{ route('vouchers.active') }}')
            .then(res => res.json())
            .then(data => {
                const body = document.getElementById('voucherModalBody');
                if (data.vouchers.length === 0) {
                    body.innerHTML = '<div class="text-center text-gray-500 py-8">Hiện tại không có voucher nào khả dụng.</div>';
                    return;
                }

                let html = '<div class="space-y-4">';
                data.vouchers.forEach(v => {
                    let discountText = v.discount_type === 'freeship'
                        ? 'Miễn phí vận chuyển'
                        : (v.discount_type === 'fixed' 
                            ? 'Giảm ' + new Intl.NumberFormat('vi-VN').format(v.discount_value) + 'đ'
                            : 'Giảm ' + v.discount_value + '%');
                    
                    let minOrderHtml = v.min_order_value 
                        ? `<div class="text-xs text-gray-500 mt-1">Đơn Tối Thiểu ${new Intl.NumberFormat('vi-VN').format(v.min_order_value)}đ</div>`
                        : '';

                    let isFreeship = v.discount_type === 'freeship';
                    let bgColor = isFreeship ? 'bg-[#10b981]' : 'bg-[#d92525]';
                    let textIconColor = isFreeship ? 'text-[#10b981]' : 'text-[#d92525]';
                    let btnColor = isFreeship ? 'bg-[#10b981] hover:bg-emerald-600' : 'bg-[#d92525] hover:bg-red-700';

                    let actionBtn = v.is_saved
                        ? `<button class="px-4 py-1.5 text-sm font-bold text-gray-400 border border-gray-300 rounded cursor-not-allowed">Đã lưu</button>`
                        : `<button onclick="saveVoucher(${v.id}, this)" class="px-4 py-1.5 text-sm font-bold text-white ${btnColor} rounded transition-colors">Lưu</button>`;

                    html += `
                        <div class="bg-white rounded border border-gray-200 overflow-hidden shadow-sm flex">
                            <!-- Left: Icon -->
                            <div class="w-28 ${bgColor} flex flex-col justify-center items-center text-white p-2 shrink-0 border-r border-dashed border-gray-300 relative">
                                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mb-1">
                                    <span class="${textIconColor} font-black text-xl">M</span>
                                </div>
                                <span class="text-[10px] font-bold uppercase tracking-wider bg-white/20 px-2 py-0.5 rounded-full">Mall</span>
                                <!-- Jagged edge simulation using circles -->
                                <div class="absolute -left-1.5 top-0 bottom-0 flex flex-col justify-between py-1">
                                    ${Array(6).fill('<div class="w-3 h-3 bg-[#f6f6f6] rounded-full"></div>').join('')}
                                </div>
                            </div>
                            <!-- Right: Content -->
                            <div class="flex-1 p-3 flex flex-col justify-between">
                                <div>
                                    <div class="font-bold text-gray-900 text-base leading-tight">${discountText}</div>
                                    ${minOrderHtml}
                                    <div class="text-[10px] text-gray-400 mt-1">HSD: ${v.expires_at}</div>
                                </div>
                                <div class="flex justify-end mt-2">
                                    ${actionBtn}
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                body.innerHTML = html;
            })
            .catch(err => {
                document.getElementById('voucherModalBody').innerHTML = '<div class="text-center text-red-500 py-8">Có lỗi xảy ra khi tải voucher.</div>';
            });
    }

    function saveVoucher(id, btn) {
        btn.innerHTML = '<svg class="animate-spin h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
        btn.disabled = true;

        fetch('{{ route('vouchers.save') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ coupon_id: id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                btn.outerHTML = `<button class="px-4 py-1.5 text-sm font-bold text-gray-400 border border-gray-300 rounded cursor-not-allowed">Đã lưu</button>`;
                Toastify({
                    text: data.message,
                    duration: 3000,
                    gravity: "bottom",
                    position: "right",
                    style: { background: "#10b981" }
                }).showToast();
            } else {
                btn.innerHTML = 'Lưu';
                btn.disabled = false;
                if(data.message.includes('đăng nhập')) {
                    window.location.href = '{{ route('login') }}';
                } else {
                    Toastify({
                        text: data.message,
                        duration: 3000,
                        gravity: "bottom",
                        position: "right",
                        style: { background: "#ef4444" }
                    }).showToast();
                }
            }
        })
        .catch(err => {
            btn.innerHTML = 'Lưu';
            btn.disabled = false;
        });
    }
</script>
@endpush
@endsection
