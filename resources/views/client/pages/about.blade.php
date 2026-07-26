@extends('client.layouts.app')

@section('title', 'Về Chúng Tôi - MaxBall')

@section('content')

<!-- Hero Section -->
<section class="relative pt-32 pb-20 lg:pt-40 lg:pb-28 overflow-hidden bg-[#0a1812]">
    <div class="absolute inset-0 bg-black/60 z-10"></div>
    <img src="https://images.unsplash.com/photo-1579952363873-27f3bade9f55?q=80&w=2070&auto=format&fit=crop" alt="Về MaxBall" class="w-full h-full object-cover absolute top-0 left-0">
    <div class="relative z-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center mt-10">
        <span class="px-4 py-1.5 rounded-full bg-[#d92525]/20 text-[#d92525] border border-[#d92525]/30 text-sm font-black uppercase tracking-[0.2em] mb-6 backdrop-blur-md inline-block" data-aos="fade-down">
            Câu Chuyện Của Chúng Tôi
        </span>
        <h1 class="font-heading text-5xl md:text-7xl font-black text-white mb-6 tracking-tight leading-tight" data-aos="fade-up" data-aos-delay="100">
            Hơn Cả Một <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#d92525] to-orange-500">Đam Mê</span>
        </h1>
        <p class="text-lg md:text-xl text-gray-300 max-w-3xl mx-auto font-medium leading-relaxed" data-aos="fade-up" data-aos-delay="200">
            MaxBall không chỉ cung cấp trang phục thi đấu. Chúng tôi trang bị cho bạn sự tự tin, sức mạnh và phong cách để làm chủ mọi khoảnh khắc trên sân cỏ.
        </p>
    </div>
</section>

<!-- Vision & Mission -->
<section class="py-24 bg-white relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-16 items-center">
            <div data-aos="fade-right">
                <div class="relative rounded-3xl overflow-hidden shadow-2xl aspect-square md:aspect-auto md:h-[600px]">
                    <img src="https://tse4.mm.bing.net/th/id/OIP.sm9pH2p4scUHZHLx-JViLQHaGT?r=0&rs=1&pid=ImgDetMain&o=7&rm=3" alt="MaxBall Team" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#10271d]/90 via-[#10271d]/20 to-transparent flex items-end p-10">
                        <div class="text-white">
                            <div class="text-5xl font-black font-heading mb-3 text-[#d92525]">2016</div>
                            <div class="text-xl font-medium opacity-90">Năm khởi nguồn của ngọn lửa đam mê túc cầu</div>
                        </div>
                    </div>
                </div>
            </div>
            <div data-aos="fade-left">
                <p class="text-[#d92525] font-black uppercase tracking-[0.2em] text-sm mb-4">Sứ Mệnh</p>
                <h2 class="font-heading text-4xl md:text-5xl font-black text-[#10271d] mb-8 leading-tight">
                    Nâng Tầm Trải Nghiệm<br>Bóng Đá Việt Nam
                </h2>
                <div class="space-y-6 text-gray-600 text-lg leading-relaxed">
                    <p>
                        Ra đời từ những trận cầu rực lửa phong trào, <strong>MaxBall</strong> thấu hiểu sâu sắc khao khát của những trái tim yêu bóng đá. Chúng tôi tin rằng bất cứ ai bước ra sân cũng xứng đáng được khoác lên mình những bộ trang phục chất lượng nhất, thiết kế đẹp nhất.
                    </p>
                    <p>
                        Từ một cửa hàng nhỏ, MaxBall đã chuyển mình mạnh mẽ, trở thành đối tác tin cậy của hàng ngàn đội bóng phủi, câu lạc bộ và các tổ chức thể thao trên toàn quốc.
                    </p>
                    <div class="pt-6 border-t border-gray-100 flex items-center gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-black text-[#10271d] mb-1">10K+</div>
                            <div class="text-sm font-bold text-gray-400 uppercase">Khách Hàng</div>
                        </div>
                        <div class="w-px h-12 bg-gray-200"></div>
                        <div class="text-center">
                            <div class="text-3xl font-black text-[#10271d] mb-1">500+</div>
                            <div class="text-sm font-bold text-gray-400 uppercase">Đội Bóng</div>
                        </div>
                        <div class="w-px h-12 bg-gray-200"></div>
                        <div class="text-center">
                            <div class="text-3xl font-black text-[#10271d] mb-1">100%</div>
                            <div class="text-sm font-bold text-gray-400 uppercase">Hài Lòng</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Core Values -->
<section class="py-24 bg-[#fcfaf6]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16" data-aos="fade-up">
            <p class="text-[#d92525] font-black uppercase tracking-[0.2em] text-sm mb-4">Core Values</p>
            <h2 class="font-heading text-4xl md:text-5xl font-black text-[#10271d]">
                Giá Trị Cốt Lõi
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <!-- Value 1 -->
            <div class="bg-white p-10 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 hover:-translate-y-2 transition-transform duration-500" data-aos="fade-up" data-aos-delay="100">
                <div class="w-16 h-16 rounded-2xl bg-[#10271d]/5 flex items-center justify-center text-[#d92525] text-2xl mb-8">
                    <i class="fa-solid fa-medal"></i>
                </div>
                <h3 class="text-2xl font-bold text-[#10271d] mb-4">Chất lượng Đỉnh cao</h3>
                <p class="text-gray-600 leading-relaxed">
                    Tuyển chọn khắt khe từ chất liệu vải siêu nhẹ, thấm hút mồ hôi cực tốt đến công nghệ in ấn tiên tiến không bong tróc.
                </p>
            </div>

            <!-- Value 2 -->
            <div class="bg-white p-10 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 hover:-translate-y-2 transition-transform duration-500" data-aos="fade-up" data-aos-delay="200">
                <div class="w-16 h-16 rounded-2xl bg-[#10271d]/5 flex items-center justify-center text-[#d92525] text-2xl mb-8">
                    <i class="fa-solid fa-paintbrush"></i>
                </div>
                <h3 class="text-2xl font-bold text-[#10271d] mb-4">Thiết kế Độc bản</h3>
                <p class="text-gray-600 leading-relaxed">
                    Sở hữu đội ngũ designer sáng tạo, MaxBall mang đến những mẫu áo độc quyền, thể hiện cá tính riêng của từng đội bóng.
                </p>
            </div>

            <!-- Value 3 -->
            <div class="bg-white p-10 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 hover:-translate-y-2 transition-transform duration-500" data-aos="fade-up" data-aos-delay="300">
                <div class="w-16 h-16 rounded-2xl bg-[#10271d]/5 flex items-center justify-center text-[#d92525] text-2xl mb-8">
                    <i class="fa-solid fa-handshake-angle"></i>
                </div>
                <h3 class="text-2xl font-bold text-[#10271d] mb-4">Dịch vụ Tận tâm</h3>
                <p class="text-gray-600 leading-relaxed">
                    Lấy khách hàng làm trung tâm. Đội ngũ tư vấn nhiệt tình, hỗ trợ 24/7 và chính sách bảo hành, đổi trả minh bạch.
                </p>
            </div>
        </div>
    </div>
</section>

@endsection
