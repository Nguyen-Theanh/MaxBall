@extends('client.layouts.app')

@section('title', 'MaxBall - Đỉnh Cao Phong Cách Thể Thao')

@section('content')

<!-- Hero Slider Section -->
<section class="relative h-screen w-full overflow-hidden bg-[#0a1812]">
    <div class="swiper heroSwiper w-full h-full">
        <div class="swiper-wrapper">
            <!-- Slide 1 -->
            <div class="swiper-slide relative">
                <div class="absolute inset-0 bg-black/40 z-10"></div>
                <img src="https://images.unsplash.com/photo-1579952363873-27f3bade9f55?q=80&w=2070&auto=format&fit=crop" alt="Football Passion" class="w-full h-full object-cover">
                <div class="absolute inset-0 z-20 flex flex-col items-center justify-center text-center px-4">
                    <span class="px-4 py-1.5 rounded-full bg-white/20 text-white border border-white/30 text-sm font-black uppercase tracking-[0.2em] mb-6 backdrop-blur-md" data-swiper-parallax="-300">
                        Bộ Sưu Tập Đỉnh Cao 2026
                    </span>
                    <h1 class="font-heading text-6xl md:text-8xl font-black text-white mb-6 tracking-tight leading-tight" data-swiper-parallax="-500">
                        Nâng Tầm <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#d92525] to-orange-500">
                            Đam Mê Của Bạn
                        </span>
                    </h1>
                    <p class="text-lg md:text-xl text-gray-200 max-w-2xl mb-10 font-medium" data-swiper-parallax="-400">
                        Chất liệu siêu nhẹ, form dáng chuẩn chuyên nghiệp. MaxBall mang đến những mẫu áo đấu đẳng cấp nhất cho mùa giải mới.
                    </p>
                    <a href="#featured" data-swiper-parallax="-200" class="px-10 py-4 rounded-full bg-[#d92525] text-white font-bold text-lg hover:bg-white hover:text-[#d92525] transition-all duration-300 shadow-[0_0_30px_rgba(217,37,37,0.5)] hover:shadow-[0_0_30px_rgba(255,255,255,0.5)]">
                        Khám phá ngay <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="swiper-slide relative">
                <div class="absolute inset-0 bg-black/20 z-10"></div>
                <img src="https://images.unsplash.com/photo-1511886929837-354d827aae26?q=80&w=2000&auto=format&fit=crop" alt="Pro Gear" class="w-full h-full object-cover">
                <div class="absolute inset-0 z-20 flex flex-col items-center justify-center text-center px-4">
                    <span class="px-4 py-1.5 rounded-full bg-[#d92525]/20 text-[#d92525] border border-[#d92525]/30 text-sm font-black uppercase tracking-[0.2em] mb-6 backdrop-blur-md" data-swiper-parallax="-300">
                        Trang Bị Chuyên Nghiệp
                    </span>
                    <h1 class="font-heading text-6xl md:text-8xl font-black text-white mb-6 tracking-tight leading-tight" data-swiper-parallax="-500">
                        Sẵn Sàng <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-emerald-400">
                            Tỏa Sáng
                        </span>
                    </h1>
                    <p class="text-lg md:text-xl text-gray-200 max-w-2xl mb-10 font-medium" data-swiper-parallax="-400">
                        Giày đá bóng chính hãng, phụ kiện chất lượng cao. Trang bị hoàn hảo cho mọi vị trí trên sân.
                    </p>
                    <a href="#featured" data-swiper-parallax="-200" class="px-10 py-4 rounded-full bg-white text-[#10271d] font-bold text-lg hover:bg-[#10271d] hover:text-white transition-all duration-300">
                        Xem trang bị <i class="fa-solid fa-cart-shopping ml-2"></i>
                    </a>
                </div>
            </div>
        </div>
        <!-- Add Pagination -->
        <div class="swiper-pagination"></div>
        <!-- Add Navigation -->
        <div class="swiper-button-next text-white hover:text-[#d92525]"></div>
        <div class="swiper-button-prev text-white hover:text-[#d92525]"></div>
    </div>
</section>

<!-- Brand Intro Section -->
<section id="about-us" class="py-24 bg-white relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-16 items-center">
            <div data-aos="fade-right">
                <div class="relative rounded-3xl overflow-hidden shadow-2xl aspect-square md:aspect-auto md:h-[600px]">
                    <img src="https://images.unsplash.com/photo-1551280857-22eb74db89ce?q=80&w=1974&auto=format&fit=crop" alt="MaxBall Team" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#10271d]/80 to-transparent flex items-end p-8">
                        <div class="text-white">
                            <div class="text-4xl font-black font-heading mb-2">10+</div>
                            <div class="text-lg opacity-80">Năm kinh nghiệm phục vụ tín đồ túc cầu</div>
                        </div>
                    </div>
                </div>
            </div>
            <div data-aos="fade-left">
                <p class="text-[#d92525] font-black uppercase tracking-[0.2em] text-sm mb-4">Về Thương Hiệu</p>
                <h2 class="font-heading text-4xl md:text-5xl font-black text-[#10271d] mb-6 leading-tight">
                    MaxBall - Linh Hồn Của Trận Đấu
                </h2>
                <p class="text-gray-600 text-lg mb-6 leading-relaxed">
                    Được sáng lập bởi những người đam mê bóng đá cuồng nhiệt, <strong>MaxBall</strong> không chỉ là một cửa hàng, mà là một điểm đến của phong cách và hiệu suất. Chúng tôi hiểu rằng, trên sân cỏ, mỗi chi tiết đều làm nên sự khác biệt.
                </p>
                <p class="text-gray-600 text-lg mb-8 leading-relaxed">
                    Từ những bộ áo đấu siêu nhẹ, thấm hút mồ hôi tối đa, đến những đôi giày đinh bám sân tuyệt đối, mọi sản phẩm tại MaxBall đều được tuyển chọn khắt khe để giúp bạn phát huy tối đa kỹ năng và tận hưởng trọn vẹn từng khoảnh khắc của trận đấu.
                </p>
                
                <div class="grid grid-cols-2 gap-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-full bg-[#10271d]/5 flex items-center justify-center text-[#d92525] flex-shrink-0 text-xl">
                            <i class="fa-solid fa-shirt"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-[#10271d] mb-1">Chất liệu cao cấp</h4>
                            <p class="text-sm text-gray-500">Thoáng mát, co giãn tốt.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-full bg-[#10271d]/5 flex items-center justify-center text-[#d92525] flex-shrink-0 text-xl">
                            <i class="fa-solid fa-truck-fast"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-[#10271d] mb-1">Giao hàng tốc hành</h4>
                            <p class="text-sm text-gray-500">Nhận hàng toàn quốc nhanh chóng.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section id="featured" class="bg-[#fcfaf6] py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col items-center mb-16 text-center" data-aos="fade-up">
            <p class="text-[#d92525] font-black uppercase tracking-[0.2em] text-sm mb-3">Lựa chọn hàng đầu</p>
            <h2 class="font-heading text-4xl md:text-5xl font-black text-[#10271d]">
                Sản Phẩm Nổi Bật
            </h2>
            <div class="w-24 h-1 bg-[#d92525] mt-6 rounded-full"></div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @forelse ($products->take(8) as $index => $product)
                @php
                    $price = $product->discount_price ?: $product->base_price;
                    $delay = ($index % 4) * 100;
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
                        <img src="{{ $imgSrc }}"
                             alt="{{ $product->name }}"
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        
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
                                {{ number_format($price,0,',','.') }}đ
                            </span>
                            @if($product->discount_price)
                                <span class="text-sm text-gray-400 line-through">
                                    {{ number_format($product->base_price,0,',','.') }}đ
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center text-gray-500 bg-white rounded-2xl border border-gray-200">
                    <i class="fa-regular fa-folder-open text-4xl mb-3 text-gray-300"></i>
                    <p>Hiện chưa có sản phẩm nào.</p>
                </div>
            @endforelse
        </div>
        
        <div class="text-center mt-12">
            <a href="{{ route('client.products.index') }}" class="inline-block px-8 py-3 rounded-full border-2 border-[#10271d] text-[#10271d] font-bold hover:bg-[#10271d] hover:text-white transition-colors duration-300">
                Xem tất cả sản phẩm
            </a>
        </div>
    </div>
</section>   

<!-- Promotion Section -->
<section id="deal" class="relative bg-[#10271d] py-24 overflow-hidden">
    <!-- Decorative Elements -->
    <div class="absolute top-0 right-0 w-96 h-96 bg-[#d92525] rounded-full mix-blend-multiply filter blur-[100px] opacity-40"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-multiply filter blur-[100px] opacity-20"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"> 
        <div class="bg-white/10 backdrop-blur-lg border border-white/20 rounded-3xl p-10 md:p-16 flex flex-col md:flex-row items-center justify-between gap-10" data-aos="zoom-in">
            <div class="text-left max-w-xl">
                <div class="inline-block px-4 py-1.5 rounded-full bg-[#d92525] text-white text-xs font-bold uppercase tracking-widest mb-6">
                    Ưu đãi độc quyền
                </div>
                <h2 class="font-heading text-4xl md:text-5xl font-black text-white mb-6 leading-tight">
                    Giảm 15% Cho Đơn Hàng Đồng Đội
                </h2>
                <p class="text-white/80 text-lg mb-8 leading-relaxed">
                    Đồng phục chất, gắn kết tinh thần. Đặt áo cho team, lớp học hoặc câu lạc bộ từ 5 áo trở lên để nhận ưu đãi đặc biệt cùng dịch vụ in ấn tên số miễn phí.
                </p>
                <button class="px-8 py-4 rounded-full bg-white text-[#10271d] font-bold hover:bg-[#d92525] hover:text-white transition-colors duration-300 shadow-xl shadow-black/20 flex items-center gap-3">
                    <i class="fa-solid fa-users"></i> Đăng ký đặt đội ngay
                </button>
            </div>
            <div class="relative w-full md:w-1/2 flex justify-center">
                <!-- Example image of a team or jersey stack -->
                <img src="https://images.unsplash.com/photo-1600250601004-95a947476839?q=80&w=2069&auto=format&fit=crop" alt="Team Jerseys" class="rounded-2xl shadow-2xl rotate-3 hover:rotate-0 transition-transform duration-500 border-4 border-white/10">
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize Swiper for Hero
        const swiper = new Swiper('.heroSwiper', {
            speed: 1000,
            parallax: true,
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            }
        });
    });
</script>
@endpush