<header class="fixed top-0 w-full z-50 transition-all duration-300" id="main-header">
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <!-- Logo -->
        <a href="{{ route('client.home') }}" class="font-heading text-4xl font-black !text-white !no-underline flex items-center gap-2 group transition-transform hover:scale-105" id="header-logo">
            <i class="fa-solid fa-futbol text-[#d92525] group-hover:rotate-180 transition-transform duration-700"></i>
            MaxBall
        </a>

        <!-- Menu Links -->
        <div class="hidden items-center gap-10 text-[15px] font-bold uppercase tracking-[0.1em] lg:flex">
            <div class="relative group py-4">
                <a href="{{ route('client.products.index') }}" class="transition !text-white hover:!text-[#d92525] !no-underline flex items-center gap-1 nav-link">
                    Sản phẩm
                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-300 group-hover:rotate-180"></i>
                </a>

                <!-- Mega Menu -->
                <div class="absolute left-1/2 -translate-x-1/2 top-[100%] w-[600px] hidden group-hover:block opacity-0 group-hover:opacity-100 transition-opacity duration-300 shadow-2xl bg-white rounded-xl z-50 p-8 border-t-4 border-[#d92525]">
                    @if(isset($categories) && $categories->count() > 0)
                        <div class="grid grid-cols-2 gap-8">
                            @foreach($categories->take(4) as $category)
                                <div>
                                    <div class="!text-[15px] font-black !text-[#10271d] uppercase border-b-2 border-gray-100 pb-2 mb-4 flex items-center gap-2">
                                        <i class="fa-solid fa-caret-right text-[#d92525]"></i> {{ $category->name }}
                                    </div>
                                    @if($category->children->count() > 0)
                                        <ul class="list-none p-0 m-0 flex flex-col gap-2">
                                            @foreach($category->children as $child)
                                                <li>
                                                    <a href="{{ route('client.category.show', $child->slug) }}"
                                                       class="!text-[14px] font-medium !text-gray-600 hover:!text-[#d92525] hover:pl-2 !no-underline transition-all duration-300 block capitalize">
                                                        {{ strtolower($child->name) }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-[14px] text-gray-500 text-center">Đang cập nhật danh mục...</div>
                    @endif
                </div>
            </div>
            <a href="{{ route('client.home') }}#deal" class="transition !text-white hover:!text-[#d92525] !no-underline nav-link">Khuyến mãi</a>
            <a href="{{ route('client.home') }}#about-us" class="transition !text-white hover:!text-[#d92525] !no-underline nav-link">Về chúng tôi</a>
            <a href="#footer" class="transition !text-white hover:!text-[#d92525] !no-underline nav-link">Liên hệ</a>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-4">
            @auth
                <a href="{{ route('account.show') }}" class="hidden rounded-full border border-white/30 bg-white/10 backdrop-blur-md px-5 py-2.5 text-sm font-bold !text-white !no-underline transition hover:bg-white hover:!text-[#10271d] sm:inline-block">
                    <i class="fa-regular fa-user mr-1"></i> {{ explode(' ', auth()->user()->name)[0] }}
                </a>
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="hidden rounded-full bg-[#d92525] px-5 py-2.5 text-sm font-bold !text-white !no-underline transition hover:bg-white hover:!text-[#d92525] shadow-lg shadow-red-500/30 lg:inline-block">
                        <i class="fa-solid fa-gauge mr-1"></i> Admin
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="rounded-full bg-white/10 hover:bg-white/20 backdrop-blur-md px-4 py-2.5 text-sm font-bold !text-white transition" title="Đăng xuất">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="hidden rounded-full border border-white/30 bg-white/10 backdrop-blur-md px-5 py-2.5 text-sm font-bold !text-white !no-underline transition hover:bg-white hover:!text-[#10271d] sm:inline-block">
                    Đăng nhập
                </a>
                <a href="{{ route('register') }}" class="hidden rounded-full bg-[#d92525] px-5 py-2.5 text-sm font-bold !text-white !no-underline transition hover:bg-white hover:!text-[#d92525] shadow-lg shadow-red-500/30 sm:inline-block">
                    Đăng ký
                </a>
            @endauth

            <button type="button" class="relative flex h-11 w-11 items-center justify-center rounded-full bg-white text-[#10271d] shadow-lg transition-transform hover:scale-110 border-0" aria-label="Giỏ hàng">
                <i class="fa-solid fa-cart-shopping text-lg"></i>
                <span id="cart-count" class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-[#d92525] px-1 text-[11px] font-black text-white shadow-sm ring-2 ring-white">0</span>
            </button>
        </div>
    </nav>
</header>

<script>
    // Header scroll effect
    window.addEventListener('scroll', function() {
        const header = document.getElementById('main-header');
        const logo = document.getElementById('header-logo');
        const navLinks = document.querySelectorAll('.nav-link');
        
        if (window.scrollY > 50) {
            header.classList.add('bg-white', 'shadow-md', 'py-1');
            header.classList.remove('py-4');
            logo.classList.replace('!text-white', '!text-[#10271d]');
            
            navLinks.forEach(link => {
                link.classList.replace('!text-white', '!text-[#10271d]');
            });
            
            // Buttons logic inside could be expanded, but for simplicity we keep it clean.
            // Using a simple style override for auth buttons on scroll
            document.querySelectorAll('#main-header .bg-white\\/10').forEach(btn => {
                btn.classList.replace('bg-white/10', 'bg-[#10271d]');
                btn.classList.replace('!text-white', '!text-white');
                btn.classList.replace('border-white/30', 'border-transparent');
            });
        } else {
            header.classList.remove('bg-white', 'shadow-md', 'py-1');
            header.classList.add('py-4');
            logo.classList.replace('!text-[#10271d]', '!text-white');
            
            navLinks.forEach(link => {
                link.classList.replace('!text-[#10271d]', '!text-white');
            });
            
            document.querySelectorAll('#main-header .bg-\\[\\#10271d\\]').forEach(btn => {
                btn.classList.replace('bg-[#10271d]', 'bg-white/10');
                btn.classList.replace('border-transparent', 'border-white/30');
            });
        }
    });
</script>
