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

    foreach ($product->variants as $variant) {
        $parts = collect(preg_split('/\s*-\s*/u', (string) $variant->name))
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

    $totalStock = $product->variants->count() ? $product->variants->sum('stock') : 999999;
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
            <button type="button"
                    id="main-image-button"
                    class="block w-full border rounded-2xl overflow-hidden bg-gray-100 cursor-zoom-in focus:outline-none focus:ring-2 focus:ring-red-500">
                <img src="{{ $galleryImages->first()['url'] }}"
                     alt="{{ $product->name }}"
                     id="main-product-image"
                     class="w-full h-[500px] object-cover">
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

                <span class="text-gray-400 line-through text-xl {{ empty($product->discount_price) ? 'hidden' : '' }}" id="display-old-price">
                    {{ !empty($product->discount_price) ? number_format($oldPrice, 0, ',', '.') . 'đ' : '' }}
                </span>
            </div>

            <div class="text-sm text-gray-500 mb-6 flex items-center gap-2">
                Kho:
                <span id="display-stock" class="font-bold text-gray-900">
                    {{ $totalStock > 0 ? $totalStock : 'Hết hàng' }}
                </span>
            </div>

            <div class="mb-6">
                <h3 class="font-bold mb-2">Mô tả sản phẩm</h3>
                <p class="text-gray-700 leading-7">
                    {{ $product->description ?? 'Chưa có mô tả sản phẩm.' }}
                </p>
            </div>

            @if($variantData->count())
                <div class="mb-6">
                    <h3 class="font-bold mb-3">Phân loại</h3>

                    <div class="space-y-4" id="variant-options">
                        @foreach($orderedAttributeOptions as $attributeName => $values)
                            <div class="variant-option-group" data-attribute="{{ $attributeName }}">
                                <div class="text-sm font-semibold text-gray-700 mb-2">{{ $attributeName }}</div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($values as $value)
                                        <button type="button"
                                                class="option-btn px-4 py-2 border rounded-lg text-sm font-semibold hover:border-red-600 focus:outline-none transition-colors disabled:cursor-not-allowed disabled:opacity-40"
                                                data-attribute="{{ $attributeName }}"
                                                data-value="{{ $value }}">
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
                <input type="hidden" name="product_variant_id" id="selected_variant_id" value="">

                <div class="mb-8">
                    <h3 class="font-bold mb-3">Số lượng</h3>
                    <div class="inline-flex items-center border rounded-lg">
                        <button type="button" id="btn-decrease-qty" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-l-lg transition-colors">-</button>
                        <input type="number" name="quantity" id="quantity-input" value="1" min="1" max="{{ max($totalStock, 1) }}" class="w-16 text-center border-x-0 border-y-0 focus:ring-0 appearance-none m-0 p-2">
                        <button type="button" id="btn-increase-qty" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-r-lg transition-colors">+</button>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 mt-8">
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
            return Object.entries(nextOptions).every(([attribute, value]) => {
                return !value || variant.options[attribute] === value;
            });
        });
    };

    const refreshOptionAvailability = () => {
        optionButtons.forEach((button) => {
            button.disabled = !isOptionAvailable(button.dataset.attribute, button.dataset.value);
        });
    };

    const updateVariantDisplay = () => {
        const selectedCount = Object.keys(selectedOptions).filter((attribute) => selectedOptions[attribute]).length;
        const isComplete = selectedCount === requiredAttributes.length;
        const matchedVariant = isComplete ? findMatchingVariant(selectedOptions) : null;

        selectedVariantInput.value = matchedVariant ? matchedVariant.id : '';

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
@endsection
