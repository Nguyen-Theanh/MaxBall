@extends('client.layouts.app')

@section('title', $categoryName . ' - MaxBall')

@section('content')

<!-- Dark Hero Banner for Category (fixes Navbar issue) -->
<section class="relative h-[400px] w-full overflow-hidden bg-[#0a1812] flex items-center justify-center">
    <div class="absolute inset-0 bg-black/60 z-10"></div>
    <img src="https://images.unsplash.com/photo-1522778119026-d647f0596c20?q=80&w=2070&auto=format&fit=crop" alt="Category Banner" class="w-full h-full object-cover">
    
    <div class="absolute inset-0 z-20 flex flex-col items-center justify-center text-center px-4 mt-16">
        <span class="px-4 py-1.5 rounded-full bg-[#d92525]/20 text-[#d92525] border border-[#d92525]/30 text-sm font-black uppercase tracking-[0.2em] mb-4 backdrop-blur-md" data-aos="fade-down">
            Bộ Sưu Tập
        </span>
        <h1 class="font-heading text-5xl md:text-6xl font-black text-white capitalize tracking-tight" data-aos="fade-up" data-aos-delay="100">
            {{ $categoryName }}
        </h1>
        <p class="text-gray-300 mt-4 text-lg font-medium" data-aos="fade-up" data-aos-delay="200">
            Khám phá {{ $products->total() }} sản phẩm cao cấp dành riêng cho bạn
        </p>
    </div>
</section>

<div class="bg-[#fcfaf6] py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-10">
            <!-- Sidebar -->
            <aside class="col-span-1" data-aos="fade-right">
                <div class="sticky top-[100px] bg-white p-6 rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100">
                    <h3 class="font-bold text-lg mb-6 text-[#10271d] flex items-center gap-2 uppercase tracking-wider border-b border-gray-100 pb-4">
                        <i class="fa-solid fa-filter text-[#d92525]"></i> Bộ Lọc
                    </h3>

                    <form action="{{ url()->current() }}" method="GET" class="space-y-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tên sản phẩm</label>
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ví dụ: Áo đấu..."
                                       class="w-full rounded-xl border-gray-200 bg-gray-50 pl-11 pr-4 py-3 text-sm transition focus:border-[#d92525] focus:bg-white focus:ring-2 focus:ring-[#d92525]/20 outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Khoảng giá (VNĐ)</label>
                            <div class="flex items-center gap-2">
                                <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Từ"
                                       class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-3 text-sm transition focus:border-[#d92525] focus:bg-white focus:ring-2 focus:ring-[#d92525]/20 outline-none">
                                <span class="text-gray-400">-</span>
                                <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Đến"
                                       class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-3 text-sm transition focus:border-[#d92525] focus:bg-white focus:ring-2 focus:ring-[#d92525]/20 outline-none">
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-[#10271d] hover:bg-[#d92525] text-white font-bold py-3.5 rounded-xl transition-all duration-300 shadow-lg shadow-[#10271d]/20 hover:shadow-[#d92525]/30">
                            Áp dụng bộ lọc
                        </button>

                        @if(request()->anyFilled(['search', 'min_price', 'max_price']))
                            <a href="{{ url()->current() }}" class="block text-center mt-4 text-sm font-semibold text-gray-500 hover:text-[#d92525] transition underline">
                                Khôi phục mặc định
                            </a>
                        @endif
                    </form>
                </div>
            </aside>

            <!-- Product Grid -->
            <main class="col-span-1 lg:col-span-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    @forelse ($products as $index => $product)
                        @php
                            $price = $product->discount_price ?: $product->base_price;
                            $delay = ($index % 3) * 100;
                        @endphp

                        <div data-aos="fade-up" data-aos-delay="{{ $delay }}" class="group relative bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 border border-gray-100 flex flex-col h-full transform hover:-translate-y-2">
                            
                            @if($product->discount_price)
                                <div class="absolute top-4 left-4 z-10 bg-[#d92525] text-white text-xs font-bold px-3 py-1 rounded-full uppercase shadow-md">
                                    Sale
                                </div>
                            @endif

                            <div class="relative overflow-hidden bg-gray-100 aspect-square">
                                @php
                                    $imgSrc = $product->thumbnail_url;
                                    if (!$imgSrc && $product->thumbnail) {
                                        $imgSrc = Str::startsWith($product->thumbnail, ['http://', 'https://']) ? $product->thumbnail : asset('storage/'.$product->thumbnail);
                                    }
                                    if (!$imgSrc) {
                                        $imgSrc = 'https://placehold.co/600x600/f3f4f6/a1a1aa?text=Chua+Co+Anh';
                                    }
                                @endphp
                                <a href="{{ route('client.products.show', $product->slug) }}" class="block w-full h-full">
                                    <img src="{{ $imgSrc }}"
                                         alt="{{ $product->name }}"
                                         class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                                </a>
                                
                                <!-- Overlay Actions -->
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-4">
                                    <button class="w-12 h-12 bg-white rounded-full text-[#10271d] hover:bg-[#d92525] hover:text-white transition-colors flex items-center justify-center shadow-lg" title="Thêm vào giỏ hàng">
                                        <i class="fa-solid fa-cart-plus"></i>
                                    </button>
                                    <a href="{{ route('client.products.show', $product->slug) }}" class="w-12 h-12 bg-white rounded-full text-[#10271d] hover:bg-[#d92525] hover:text-white transition-colors flex items-center justify-center shadow-lg" title="Xem chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="p-6 flex flex-col flex-grow text-center">
                                <a href="{{ route('client.products.show', $product->slug) }}" class="text-lg font-bold text-[#10271d] mb-2 group-hover:text-[#d92525] transition-colors line-clamp-2 no-underline">
                                    {{ $product->name }}
                                </a>
                                <div class="mt-auto pt-4 border-t border-gray-100 flex flex-col items-center justify-center gap-1">
                                    <span class="text-xl font-black text-[#d92525]">
                                        {{ number_format($price, 0, ',', '.') }}đ
                                    </span>
                                    @if($product->discount_price)
                                        <span class="text-sm text-gray-400 line-through">
                                            {{ number_format($product->base_price, 0, ',', '.') }}đ
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-20 text-center bg-white rounded-3xl border border-gray-200 shadow-sm">
                            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-50 text-gray-300 mb-4">
                                <i class="fa-regular fa-folder-open text-4xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Chưa có sản phẩm</h3>
                            <p class="text-gray-500">Danh mục này hiện tại chưa có sản phẩm nào, vui lòng quay lại sau.</p>
                            <a href="{{ route('client.products.index') }}" class="inline-block mt-6 px-6 py-3 bg-[#10271d] text-white rounded-full font-semibold hover:bg-[#d92525] transition-colors">
                                Khám phá danh mục khác
                            </a>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($products->hasPages())
                    <div class="mt-16 flex justify-center">
                        <div class="bg-white p-2 rounded-2xl shadow-sm border border-gray-100">
                            {{ $products->links() }}
                        </div>
                    </div>
                @endif
            </main>
        </div>
    </div>
</div>
@endsection