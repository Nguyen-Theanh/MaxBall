<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - MaxBall')</title>
    <!-- Bootstrap CSS (for legacy pages) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <!-- TailwindCSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e40af', // blue-800
                        'primary-light': '#3b82f6', // blue-500
                        background: '#f4f5f7',
                        surface: '#ffffff',
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js for interactive components -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* Prevent Tailwind's preflight from breaking Bootstrap components entirely */
        a { text-decoration: none; }

        body {
            background-color: #f4f5f7;
            font-family: 'Inter', sans-serif;
            color: #1f2937;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Core Animations */
        @keyframes slideUpFade {
            0% {
                opacity: 0;
                transform: translateY(30px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        .animate-slide-up {
            animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
            opacity: 0;
        }

        /* Animation Delays for Staggered Effect */
        .delay-100 { animation-delay: 100ms; }
        .delay-200 { animation-delay: 200ms; }
        .delay-300 { animation-delay: 300ms; }
        .delay-400 { animation-delay: 400ms; }
        .delay-500 { animation-delay: 500ms; }

        /* Soft Shadows for Cards */
        .card-shadow {
            box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.05);
        }

        /* Sidebar Nav Styles */
        .sidebar-link {
            transition: all 0.3s ease;
        }
        .sidebar-link:hover {
            background-color: #f3f4f6;
            transform: translateX(5px);
        }
        .sidebar-link.active {
            background-color: #2563eb; /* blue-600 */
            color: #ffffff !important;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
        }
        .sidebar-link.active svg {
            color: #ffffff;
        }
    </style>
    @stack('styles')
</head>
<body class="antialiased h-screen flex overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-100 flex-shrink-0 flex flex-col h-full shadow-sm z-20">
        <!-- Logo -->
        <div class="h-20 flex items-center px-8 border-b border-gray-50">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 text-decoration-none">
                <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                <span class="text-2xl font-bold text-blue-600 tracking-tight">Admin</span>
            </a>
        </div>

        <!-- Navigation -->
        <div class="flex-1 overflow-y-auto py-6 px-4 space-y-1">
            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Quản lý chính</p>

            <a href="{{ route('admin.dashboard') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 font-medium {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                Thống kê
            </a>

            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mt-6 mb-2">Cửa hàng</p>

            <a href="{{ route('admin.products.index') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 font-medium {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                Sản phẩm
            </a>

            <a href="{{ route('admin.orders.index') }}" class="sidebar-link flex items-center justify-between px-4 py-3 rounded-xl text-gray-600 font-medium {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    Đơn hàng
                </div>
                @php $pendingOrdersCount = \App\Models\Order::where('order_status', 'pending')->count(); @endphp
                @if($pendingOrdersCount > 0)
                    <span class="inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold text-white bg-red-500 rounded-full">{{ $pendingOrdersCount }}</span>
                @endif
            </a>

            <a href="{{ route('admin.reviews.index') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 font-medium {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l2.036 6.264h6.586c.969 0 1.371 1.24.588 1.81l-5.328 3.87 2.035 6.264c.3.921-.755 1.688-1.539 1.118L12 18.382l-5.329 3.871c-.783.57-1.838-.197-1.539-1.118l2.036-6.264-5.329-3.87c-.783-.57-.38-1.81.588-1.81h6.586l2.036-6.264z"></path></svg>
                Đánh giá
            </a>

            <a href="{{ route('admin.coupons.index') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 font-medium {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                Mã giảm giá
            </a>

            <a href="{{ route('admin.categories.index') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 font-medium {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                Danh mục
            </a>

            <a href="{{ route('admin.attributes.index') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 font-medium {{ request()->routeIs('admin.attributes.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                Thuộc tính
            </a>

            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mt-6 mb-2">Hệ thống</p>

            <a href="{{ route('admin.users.index') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 font-medium {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Người dùng
            </a>

            <a href="{{ route('admin.contacts.index') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 font-medium {{ request()->routeIs('admin.contacts.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                Liên hệ
            </a>

        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden bg-background">

        <!-- Header -->
        @php
            $adminPageTitle = match (true) {
                request()->routeIs('admin.dashboard*') => 'Thống kê kinh doanh',
                request()->routeIs('admin.products.*') => 'Quản lý sản phẩm',
                request()->routeIs('admin.orders.*') => 'Quản lý đơn hàng',
                request()->routeIs('admin.reviews.*') => 'Quản lý đánh giá',
                request()->routeIs('admin.categories.*') => 'Quản lý danh mục',
                request()->routeIs('admin.attributes.*') => 'Quản lý thuộc tính',
                request()->routeIs('admin.users.*') => 'Quản lý người dùng',
                request()->routeIs('admin.contacts.*') => 'Quản lý liên hệ',
                default => 'Quản trị MaxBall',
            };
        @endphp
        <header class="h-20 bg-white/90 backdrop-blur-md px-8 flex items-center justify-between sticky top-0 z-10 animate-fade-in border-b border-gray-200/70 shadow-sm shadow-gray-200/30">
            <div class="min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-blue-600">Khu vực quản trị</p>
                <h2 class="mt-1 truncate text-xl font-extrabold text-gray-900">{{ $adminPageTitle }}</h2>
            </div>

            <!-- Right Nav -->
            <div class="ml-4 flex items-center gap-3">
                <a href="{{ route('client.products.index') }}" target="_blank" class="hidden sm:flex items-center gap-2 rounded-xl border border-blue-100 bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-700 transition-colors hover:border-blue-200 hover:bg-blue-100">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 11.5 12 4l9 7.5M5.5 9.5V20h13V9.5M9 20v-6h6v6"></path></svg>
                    Xem cửa hàng
                </a>

                <!-- Date -->
                <div class="hidden md:flex items-center gap-2 text-sm text-gray-500 font-medium bg-gray-50 px-4 py-2.5 rounded-xl border border-gray-200">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    {{ now()->format('d/m/Y') }}
                </div>

                <!-- Profile Dropdown (Alpine.js) -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.away="open = false" class="flex items-center gap-3 rounded-xl border border-transparent px-2 py-1.5 text-left transition hover:border-gray-200 hover:bg-gray-50 focus:outline-none">
                        <div class="hidden lg:block max-w-40 text-right">
                            <p class="truncate text-sm font-bold text-gray-800">{{ auth()->user()->name ?? 'Admin' }}</p>
                            <p class="text-[11px] text-gray-400">Quản trị viên</p>
                        </div>
                        <img class="h-10 w-10 rounded-xl object-cover ring-2 ring-blue-100" src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Admin') }}&background=2563eb&color=fff" alt="Avatar quản trị">
                        <svg class="hidden h-4 w-4 text-gray-400 sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                    </button>

                    <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="origin-top-right absolute right-0 mt-2 w-56 rounded-xl shadow-xl bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none z-50" style="display: none;">
                        <div class="px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Đang đăng nhập với</p>
                            <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->email ?? 'admin@maxball.com' }}</p>
                        </div>
                        <div class="py-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-gray-50 transition-colors">Đăng xuất</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Scrollable Area -->
        <main class="flex-1 overflow-y-auto p-8">

            <!-- Alerts -->
            @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg flex items-center justify-between animate-slide-up">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-green-700 font-medium">{{ session('success') }}</p>
                    </div>
                    <button onclick="this.parentElement.style.display='none'" class="text-green-500 hover:text-green-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg flex items-center justify-between animate-slide-up">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-red-700 font-medium">{{ $errors->first() }}</p>
                    </div>
                    <button onclick="this.parentElement.style.display='none'" class="text-red-500 hover:text-red-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            @endif

            @yield('content')

        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    @include('shared.confirm-dialog')

    @stack('scripts')
</body>
</html>
