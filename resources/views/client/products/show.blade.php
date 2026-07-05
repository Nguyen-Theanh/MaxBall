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

<div class="max-w-7xl mx-auto px-4 py-10">
    <div class="mb-6 text-sm text-gray-500">
        <a href="{{ route('client.products.index') }}" class="hover:text-red-600">Sản phẩm</a>
        <span class="mx-2">/</span>
        <span>{{ $product->name }}</span>
    </div>

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

            <div class="flex items-center gap-4 mb-6">
                <span class="text-3xl font-black text-red-600">
                    {{ number_format($price, 0, ',', '.') }}đ
                </span>

                @if(!empty($product->discount_price))
                    <span class="text-gray-400 line-through text-xl">
                        {{ number_format($oldPrice, 0, ',', '.') }}đ
                    </span>
                @endif
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
                    <div class="flex flex-wrap gap-2">
                        @foreach($product->variants as $variant)
                            <button type="button" class="px-4 py-2 border rounded-lg hover:border-red-600">
                                {{ $variant->name ?? $variant->sku ?? 'Biến thể' }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex gap-4 mt-8">
                <button class="flex-1 bg-gray-900 text-white py-4 rounded-xl font-bold hover:bg-black">
                    Thêm vào giỏ hàng
                </button>

                <button class="flex-1 bg-red-600 text-white py-4 rounded-xl font-bold hover:bg-red-700">
                    Mua ngay
                </button>
            </div>
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
@endsection       