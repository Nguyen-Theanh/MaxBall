<header class="sticky top-0 z-40 border-b border-black/10 bg-[#f5f1e8]/95 backdrop-blur">
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <a href="{{ route('client.products.index') }}" class="font-heading text-3xl font-black text-[#10271d]">
            MaxBall
        </a>

        <div class="hidden items-center gap-8 text-sm font-bold uppercase tracking-[0.18em] text-[#10271d] md:flex">
            <a href="{{ route('client.products.index') }}#products" class="transition hover:text-[#d92525]">Sản phẩm</a>
            <a href="{{ route('client.products.index') }}#deal" class="transition hover:text-[#d92525]">Khuyến mãi</a>
            <a href="#footer" class="transition hover:text-[#d92525]">Liên hệ</a>
        </div>

        <button type="button" class="relative flex h-11 w-11 items-center justify-center rounded-full bg-[#10271d] text-white shadow-lg shadow-[#10271d]/20" aria-label="Gio hang">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                <path d="M3 6h18"></path>
                <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>
            <span id="cart-count" class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-[#d92525] px-1 text-xs font-bold text-white">0</span>
        </button>
    </nav>
</header>
