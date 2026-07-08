@extends('client.layouts.app')

@section('title', 'Liên Hệ - MaxBall')

@section('content')

<!-- Hero Section -->
<section class="relative pt-32 pb-20 overflow-hidden bg-[#0a1812]">
    <div class="absolute inset-0 bg-black/60 z-10"></div>
    <img src="https://images.unsplash.com/photo-1518605368461-1eb475968270?q=80&w=2070&auto=format&fit=crop" alt="Liên hệ MaxBall" class="w-full h-full object-cover absolute top-0 left-0">
    <div class="relative z-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center mt-10">
        <h1 class="font-heading text-5xl md:text-6xl font-black text-white mb-4 tracking-tight">
            Liên Hệ Với <span class="text-[#d92525]">MaxBall</span>
        </h1>
        <p class="text-lg text-gray-300 max-w-2xl mx-auto font-medium">Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn 24/7.</p>
    </div>
</section>

<section class="py-20 bg-[#fcfaf6]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
            
            <!-- Contact Info & Map -->
            <div data-aos="fade-right">
                <div class="bg-white p-10 rounded-3xl shadow-xl shadow-gray-200/50 mb-8 border border-gray-100">
                    <h3 class="text-2xl font-bold text-[#10271d] mb-8 font-heading">Thông Tin Trực Tiếp</h3>
                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-[#d92525]/10 flex items-center justify-center text-[#d92525] flex-shrink-0 text-xl"><i class="fa-solid fa-location-dot"></i></div>
                            <div>
                                <h4 class="font-bold text-[#10271d] mb-1">Trụ sở chính</h4>
                                <p class="text-gray-600">123 Đường Bóng Đá, Quận Thể Thao, TP. Hà Nội</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-[#d92525]/10 flex items-center justify-center text-[#d92525] flex-shrink-0 text-xl"><i class="fa-solid fa-phone"></i></div>
                            <div>
                                <h4 class="font-bold text-[#10271d] mb-1">Hotline</h4>
                                <p class="text-gray-600">0987.654.321 (24/7)</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-[#d92525]/10 flex items-center justify-center text-[#d92525] flex-shrink-0 text-xl"><i class="fa-solid fa-envelope"></i></div>
                            <div>
                                <h4 class="font-bold text-[#10271d] mb-1">Email</h4>
                                <p class="text-gray-600">tuankaka554@gmail.com</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="rounded-3xl overflow-hidden shadow-xl shadow-gray-200/50 h-[300px] border border-gray-100">
                    <iframe src="https://maps.google.com/maps?q=Trường%20Cao%20đẳng%20FPT%20Polytechnic%20Hà%20Nội,%20Trịnh%20Văn%20Bô&t=&z=16&ie=UTF8&iwloc=&output=embed" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>

            <!-- Contact Form -->
            <div data-aos="fade-left">
                <div class="bg-white p-10 rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 h-full">
                    <h3 class="text-3xl font-black text-[#10271d] mb-2 font-heading">Gửi Tin Nhắn Cho Chúng Tôi</h3>
                    <p class="text-gray-500 mb-8">Hãy để lại thông tin, đội ngũ MaxBall sẽ liên hệ lại ngay lập tức.</p>

                    @auth
                        @if(!auth()->user()->email || !auth()->user()->phone)
                            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-6 py-4 rounded-2xl mb-8 flex items-start gap-3">
                                <i class="fa-solid fa-triangle-exclamation text-xl mt-0.5"></i>
                                <div>
                                    <p class="font-medium">Tài khoản chưa đầy đủ thông tin</p>
                                    <p class="text-sm mt-1">Vui lòng cập nhật Email và Số điện thoại trong <a href="{{ route('account.show') }}" class="underline font-bold text-[#d92525]">trang cá nhân</a> để gửi liên hệ.</p>
                                </div>
                            </div>
                        @else
                            @if(session('contact_success'))
                                <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-2xl mb-8 flex items-start gap-3">
                                    <i class="fa-solid fa-circle-check text-xl mt-0.5"></i>
                                    <p class="font-medium">{{ session('contact_success') }}</p>
                                </div>
                            @endif

                            @if(session('contact_error'))
                                <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-2xl mb-8 flex items-start gap-3">
                                    <i class="fa-solid fa-circle-exclamation text-xl mt-0.5"></i>
                                    <p class="font-medium">{{ session('contact_error') }}</p>
                                </div>
                            @endif

                            <form action="{{ route('client.contact.submit') }}" method="POST" class="space-y-6">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Họ và Tên *</label>
                                        <input type="text" name="name" value="{{ auth()->user()->name }}" readonly class="w-full rounded-xl border-gray-200 bg-gray-100 px-4 py-3.5 text-sm text-gray-500 cursor-not-allowed outline-none select-none">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Số Điện Thoại *</label>
                                        <input type="text" name="phone" value="{{ auth()->user()->phone }}" readonly class="w-full rounded-xl border-gray-200 bg-gray-100 px-4 py-3.5 text-sm text-gray-500 cursor-not-allowed outline-none select-none">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Email của bạn *</label>
                                    <input type="email" name="email" value="{{ auth()->user()->email }}" readonly class="w-full rounded-xl border-gray-200 bg-gray-100 px-4 py-3.5 text-sm text-gray-500 cursor-not-allowed outline-none select-none">
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Nội Dung Cần Hỗ Trợ *</label>
                                    <textarea name="message" rows="5" required class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3.5 text-sm transition focus:border-[#d92525] focus:bg-white focus:ring-2 focus:ring-[#d92525]/20 outline-none resize-none">{{ old('message') }}</textarea>
                                    @error('message')<span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>@enderror
                                </div>

                                <button type="submit" class="w-full bg-[#10271d] hover:bg-[#d92525] text-white font-bold text-lg py-4 rounded-xl transition-all duration-300 shadow-xl shadow-[#10271d]/20 hover:shadow-[#d92525]/30">
                                    Gửi Tin Nhắn <i class="fa-solid fa-paper-plane ml-2"></i>
                                </button>
                            </form>
                        @endif
                    @else
                        <div class="text-center py-10 bg-gray-50 rounded-2xl border border-gray-100 mt-4">
                            <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400 text-2xl">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <h4 class="text-lg font-bold text-gray-800 mb-2">Đăng nhập để liên hệ</h4>
                            <p class="text-gray-500 mb-6">Bạn cần có tài khoản để gửi tin nhắn hỗ trợ.</p>
                            <a href="{{ route('login') }}" class="inline-block bg-[#10271d] hover:bg-[#d92525] text-white font-bold px-8 py-3 rounded-xl transition-all duration-300">Đăng Nhập Ngay</a>
                        </div>
                    @endauth
                </div>
            </div>

        </div>
    </div>
</section>

@endsection
