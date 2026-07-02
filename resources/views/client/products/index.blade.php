@extends('client.layouts.app')

@section('title', 'MaxBall - Football Jersey Shop')

@section('content')
<section class="relative bg-[#0a1812] overflow-hidden">
    <div class="absolute inset-0 opacity-40">
        <img src="https://cdn-media.sforum.vn/storage/app/media/wp-content/uploads/2023/06/hinh-nen-bong-da-thumb.jpg" alt="Hero Background" class="w-full h-full object-cover">
    </div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32 flex flex-col items-center text-center">
        <span class="px-4 py-1.5 rounded-full bg-[#d92525]/10 text-[#d92525] border border-[#d92525]/20 text-sm font-black uppercase tracking-widest mb-6 backdrop-blur-sm">
            Bộ sưu tập 2026
        </span>
        <h1 class="font-heading text-5xl md:text-7xl font-black text-white mb-6 tracking-tight leading-tight">
            Nâng Tầm <br/><span class="text-transparent bg-clip-text bg-gradient-to-r from-[#f5f1e8] to-gray-400">Đam Mê Của Bạn</span>
        </h1>
        <p class="text-lg md:text-xl text-gray-300 max-w-2xl mb-10 font-medium">
            Chất liệu siêu nhẹ, form dáng chuẩn chuyên nghiệp. MaxBall mang đến những mẫu áo đấu đẳng cấp nhất cho mùa giải mới.
        </p>
        <a href="#featured" class="px-8 py-4 rounded-xl bg-white text-[#10271d] font-bold text-lg hover:bg-[#d92525] hover:text-white transition-all duration-300 shadow-[0_0_40px_rgb(255,255,255,0.2)] hover:shadow-[0_0_40px_rgb(217,37,37,0.4)] hover:-translate-y-1">
            Khám phá ngay
        </a>
    </div>
</section>

<section id="featured" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="flex flex-col items-center mb-12 text-center">
        <p class="text-[#d92525] font-black uppercase tracking-widest text-sm mb-2">Lựa chọn hàng đầu</p>
        <h2 class="font-heading text-4xl font-black text-[#10271d]">Sản Phẩm Nổi Bật</h2>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
        @forelse ($products->take(6) as $product)
            @php $price = $product->discount_price ?: $product->base_price; @endphp
            <div class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 border border-gray-100 flex flex-col h-full">
                <div class="relative overflow-hidden bg-gray-50 aspect-[4/5]">
                    <img src="{{ $product->thumbnail_url ?? asset('storage/'.$product->thumbnail) }}" alt="{{ $product->name }}" 
                         class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                </div>
                <div class="p-6 flex flex-col flex-grow text-center">
                    <h3 class="text-lg font-bold text-[#10271d] mb-3 group-hover:text-[#d92525] transition-colors">
                        {{ $product->name }}
                    </h3>
                    <div class="mt-auto">
                        <p class="text-xl font-black text-[#10271d]">{{ number_format($price, 0, ',', '.') }}đ</p>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-10 text-center text-gray-500">Chưa có sản phẩm.</div>
        @endforelse
    </div>
</section>

<section class="bg-[#d92525] text-white py-16">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h2 class="font-heading text-3xl md:text-4xl font-black mb-4">Giảm 15% cho đơn đồng đội</h2>
        <p class="text-white/80 text-lg mb-8 max-w-2xl mx-auto">Đặt áo cho team, lớp học hoặc câu lạc bộ từ 5 áo trở lên. Hỗ trợ in ấn và tư vấn size nhanh chóng trong ngày.</p>
        <button class="px-8 py-3 rounded-xl bg-[#10271d] text-white font-bold hover:bg-white hover:text-[#10271d] transition-colors duration-300">
            Liên hệ đặt đội
        </button>
    </div>
</section>
@endsection