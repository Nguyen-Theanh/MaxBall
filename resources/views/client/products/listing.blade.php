@extends('client.layouts.app')

@section('title', $categoryName . ' - MaxBall')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-10 border-b border-gray-200 pb-6">
        <h1 class="font-heading text-4xl md:text-5xl font-black text-[#10271d] capitalize tracking-tight">{{ $categoryName }}</h1>
        <p class="text-gray-500 mt-3 text-lg font-medium">Khám phá bộ sưu tập {{ $products->total() }} sản phẩm cao cấp</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-10">
        <aside class="col-span-1">
            <div class="sticky top-[100px] bg-white p-6 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100">
                <h3 class="font-bold text-lg mb-5 text-[#10271d] flex items-center gap-2 uppercase tracking-wider">
                    Bộ lọc
                </h3>

                <form action="{{ url()->current() }}" method="GET" class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Tên sản phẩm</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="VD: Áo sân khách..."
                               class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#10271d] focus:bg-white focus:ring-2 focus:ring-[#10271d]/20 outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Khoảng giá (VNĐ)</label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Từ"
                                   class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-3 text-sm transition focus:border-[#10271d] focus:bg-white focus:ring-2 focus:ring-[#10271d]/20 outline-none">
                            <span class="text-gray-400">-</span>
                            <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Đến"
                                   class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-3 text-sm transition focus:border-[#10271d] focus:bg-white focus:ring-2 focus:ring-[#10271d]/20 outline-none">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-[#10271d] hover:bg-[#1a4030] text-white font-bold py-3.5 rounded-xl transition-all duration-300 shadow-lg shadow-[#10271d]/20 active:scale-95">
                        Áp dụng
                    </button>

                    @if(request()->anyFilled(['search', 'min_price', 'max_price']))
                        <a href="{{ url()->current() }}" class="block text-center mt-4 text-sm font-semibold text-gray-500 hover:text-[#d92525] transition underline">
                            Xóa bộ lọc
                        </a>
                    @endif
                </form>
            </div>
        </aside>

        <main class="col-span-1 lg:col-span-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                @forelse ($products as $product)
                    @php
                        $price = $product->discount_price ?: $product->base_price;
                    @endphp

                    <a href="{{ route('client.products.show', $product->slug) }}"
                       class="group bg-white rounded-2xl overflow-hidden shadow-[0_2px_10px_rgb(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgb(0,0,0,0.08)] transition-all duration-500 border border-gray-100 flex flex-col h-full no-underline">

                        <div class="relative overflow-hidden bg-gray-50 aspect-[4/5]">
                            <img src="{{ $product->thumbnail_url ?? asset('storage/'.$product->thumbnail) }}"
                                 alt="{{ $product->name }}"
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                 loading="lazy">

                            @if ($product->discount_price)
                                <div class="absolute top-4 right-4 bg-[#d92525] text-white text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-full z-10 shadow-lg shadow-[#d92525]/40">
                                    Sale
                                </div>
                            @endif
                        </div>

                        <div class="p-5 flex flex-col flex-grow">
                            <span class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">{{ $categoryName }}</span>

                            <h3 class="text-lg font-bold text-[#10271d] leading-snug line-clamp-2 group-hover:text-[#d92525] transition-colors">
                                {{ $product->name }}
                            </h3>

                            <div class="mt-auto pt-5 flex items-end justify-between">
                                <div>
                                    <p class="text-lg font-black text-[#10271d]">{{ number_format($price, 0, ',', '.') }}đ</p>

                                    @if ($product->discount_price)
                                        <p class="text-sm text-gray-400 line-through mt-[-4px]">{{ number_format($product->base_price, 0, ',', '.') }}đ</p>
                                    @endif
                                </div>

                                <span class="h-10 px-4 rounded-xl bg-[#f5f1e8] text-[#10271d] font-bold text-sm hover:bg-[#10271d] hover:text-white transition-all duration-300 flex items-center">
                                    Mua
                                </span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-20 text-center bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                        <p class="text-lg text-gray-500 font-medium">Không tìm thấy sản phẩm nào.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-12 flex justify-center">
                {{ $products->links() }}
            </div>
        </main>
    </div>
</div>
@endsection