<footer id="footer" class="bg-[#07130e] pt-20 pb-10 text-white/70 relative overflow-hidden">
    <!-- Decorative -->
    <div class="absolute top-0 right-10 w-64 h-64 bg-[#d92525]/10 rounded-full blur-[80px]"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
            <!-- Col 1: Brand -->
            <div>
                <a href="{{ route('client.products.index') }}" class="font-heading text-3xl font-black text-white mb-6 flex items-center gap-2 !no-underline">
                    <i class="fa-solid fa-futbol text-[#d92525]"></i> MaxBall
                </a>
                <p class="text-sm leading-relaxed mb-6">
                    MaxBall là điểm đến hàng đầu dành cho những người đam mê bóng đá. Chúng tôi cung cấp các sản phẩm thể thao chuyên nghiệp với chất lượng cao nhất, giúp bạn tự tin tỏa sáng trên mọi mặt sân.
                </p>
                <div class="flex gap-4">
                    <a href="#" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-white hover:bg-[#1877F2] transition-colors">
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-white hover:bg-gradient-to-tr hover:from-[#f9ce34] hover:via-[#ee2a7b] hover:to-[#6228d7] transition-all duration-300">
                        <i class="fa-brands fa-instagram"></i>
                    </a>
                    <a href="#" class="group w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-white hover:bg-black transition-colors duration-300">
                        <i class="fa-brands fa-tiktok group-hover:[text-shadow:1.5px_1.5px_0_#fe2c55,-1.5px_-1.5px_0_#24f6f0] transition-all"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-white hover:bg-[#FF0000] transition-colors">
                        <i class="fa-brands fa-youtube"></i>
                    </a>
                </div>
            </div>

            <!-- Col 2: Quick Links -->
            <div>
                <h4 class="text-white font-bold text-lg mb-6 uppercase tracking-wider">Khám Phá</h4>
                <ul class="space-y-3">
                    <li><a href="{{ route('client.products.index') }}#products" class="hover:text-[#d92525] transition-colors flex items-center gap-2"><i class="fa-solid fa-angle-right text-xs"></i> Tất cả sản phẩm</a></li>
                    <li><a href="{{ route('client.products.index') }}#deal" class="hover:text-[#d92525] transition-colors flex items-center gap-2"><i class="fa-solid fa-angle-right text-xs"></i> Khuyến mãi & Ưu đãi đội</a></li>
                    <li><a href="#about-us" class="hover:text-[#d92525] transition-colors flex items-center gap-2"><i class="fa-solid fa-angle-right text-xs"></i> Về MaxBall</a></li>
                    <li><a href="#" class="hover:text-[#d92525] transition-colors flex items-center gap-2"><i class="fa-solid fa-angle-right text-xs"></i> Hướng dẫn chọn size</a></li>
                    <li><a href="#" class="hover:text-[#d92525] transition-colors flex items-center gap-2"><i class="fa-solid fa-angle-right text-xs"></i> Chính sách đổi trả</a></li>
                </ul>
            </div>

            <!-- Col 3: Contact Info -->
            <div>
                <h4 class="text-white font-bold text-lg mb-6 uppercase tracking-wider">Liên Hệ</h4>
                <ul class="space-y-4">
                    <li class="flex items-start gap-3">
                        <i class="fa-solid fa-location-dot mt-1 text-[#d92525]"></i>
                        <span>123 Đường Sân Cỏ, Phường Bóng Đá, Quận Vô Địch, Hà Nội</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <i class="fa-solid fa-phone text-[#d92525]"></i>
                        <span>0123 456 789</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <i class="fa-solid fa-envelope text-[#d92525]"></i>
                        <span>support@maxball.vn</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <i class="fa-solid fa-clock text-[#d92525]"></i>
                        <span>Mở cửa: 08:00 - 22:00 (Mỗi ngày)</span>
                    </li>
                </ul>
            </div>

            <!-- Col 4: Newsletter -->
            <div>
                <h4 class="text-white font-bold text-lg mb-6 uppercase tracking-wider">Bản Tin</h4>
                <p class="text-sm mb-4">
                    Đăng ký để nhận thông tin về bộ sưu tập mới và ưu đãi độc quyền dành riêng cho bạn.
                </p>
                <form class="flex">
                    <input type="email" placeholder="Email của bạn..." class="w-full px-4 py-3 rounded-l-lg bg-white/5 border border-white/10 focus:outline-none focus:border-[#d92525] text-white">
                    <button type="button" class="px-5 py-3 bg-[#d92525] text-white rounded-r-lg hover:bg-white hover:text-[#d92525] transition-colors">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="border-t border-white/10 pt-8 flex flex-col md:flex-row items-center justify-between text-xs text-white/50">
            <p>&copy; {{ date('Y') }} MaxBall. Bản quyền thuộc về MaxBall Store.</p>
            <div class="flex gap-4 mt-4 md:mt-0">
                <a href="#" class="hover:text-white transition-colors">Điều khoản dịch vụ</a>
                <a href="#" class="hover:text-white transition-colors">Chính sách bảo mật</a>
            </div>
        </div>
    </div>
</footer>
