<header class="sticky top-0 z-40 border-b border-black/10 bg-[#f5f1e8]/95 backdrop-blur">
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <a href="{{ route('client.products.index') }}" class="font-heading text-3xl font-black !text-[#10271d] !no-underline">
            MaxBall
        </a>

        <div class="hidden items-center gap-8 text-sm font-bold uppercase tracking-[0.18em] md:flex">
            <div class="relative group py-4">
                <a href="{{ route('client.products.index') }}#products" class="transition !text-[#10271d] hover:!text-[#d92525] !no-underline flex items-center gap-1">
                    Sản phẩm
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200 group-hover:rotate-180" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </a>

                <div class="absolute left-1/2 -translate-x-1/2 top-[100%] w-[800px] hidden group-hover:block shadow-xl bg-white border border-gray-100 rounded-lg z-50 p-8 cursor-default">
                    @if(isset($categories) && $categories->count() > 0)
                        <div class="grid grid-cols-3 gap-10">
                            @foreach($categories as $category)
                                <div>
                                    <div class="!text-[14px] font-bold !text-[#10271d] uppercase border-b border-gray-200 pb-3 mb-4">
                                        {{ $category->name }}
                                    </div>
                                    
                                    @if($category->children->count() > 0)
                                        <ul class="list-none p-0 m-0 flex flex-col gap-3">
                                            @foreach($category->children as $child)
                                                <li>
                                                    <a href="#" class="!text-[14px] font-medium !text-gray-500 hover:!text-[#d92525] !no-underline transition-colors block capitalize">
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
                        <div class="text-[14px] text-gray-500 text-center">Chưa có danh mục nào</div>
                    @endif
                </div>
            </div>
            <a href="{{ route('client.products.index') }}#deal" class="transition !text-[#10271d] hover:!text-[#d92525] !no-underline">Khuyến mãi</a>
            <a href="#footer" class="transition !text-[#10271d] hover:!text-[#d92525] !no-underline">Liên hệ</a>
        </div>

        <button type="button" class="relative flex h-11 w-11 items-center justify-center rounded-full bg-[#10271d] text-white shadow-lg shadow-[#10271d]/20 border-0" aria-label="Gio hang">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                <path d="M3 6h18"></path>
                <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>
            <span id="cart-count" class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-[#d92525] px-1 text-xs font-bold text-white">0</span>
        </button>
    </nav>
</header>