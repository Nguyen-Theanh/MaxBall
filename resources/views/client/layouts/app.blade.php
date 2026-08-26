<!doctype html>
<html lang="vi">
<head>
    @php
        $defaultSeoTitle = 'MaxBall - Trang phục và phụ kiện bóng đá';
        $defaultSeoDescription = 'Khám phá trang phục, giày và phụ kiện bóng đá tại MaxBall với thông tin sản phẩm được cập nhật theo danh mục hiện có.';
        $normalizeSeoText = static function ($value, ?int $limit = null): string {
            $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $text = preg_replace('/\s+/u', ' ', trim($text)) ?? trim($text);

            if (! $limit || mb_strlen($text, 'UTF-8') <= $limit) {
                return $text;
            }

            $slice = mb_substr($text, 0, $limit + 1, 'UTF-8');
            $lastSpace = mb_strrpos($slice, ' ', 0, 'UTF-8');

            return rtrim(mb_substr($slice, 0, $lastSpace === false ? $limit : $lastSpace, 'UTF-8'));
        };

        $seoTitle = $normalizeSeoText($__env->yieldContent('title', $defaultSeoTitle)) ?: $defaultSeoTitle;
        $seoDescription = $normalizeSeoText($__env->yieldContent('meta_description', $defaultSeoDescription), 160) ?: $defaultSeoDescription;
        $canonicalUrl = trim($__env->yieldContent('canonical_url', url()->current())) ?: url()->current();
        $openGraphTitle = $normalizeSeoText($__env->yieldContent('og_title', $seoTitle)) ?: $seoTitle;
        $openGraphDescription = $normalizeSeoText($__env->yieldContent('og_description', $seoDescription), 160) ?: $seoDescription;
        $openGraphImage = trim($__env->yieldContent('og_image', asset('favicon.ico'))) ?: asset('favicon.ico');
        $openGraphType = trim($__env->yieldContent('og_type', 'website')) ?: 'website';
    @endphp

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">

    <meta property="og:title" content="{{ $openGraphTitle }}">
    <meta property="og:description" content="{{ $openGraphDescription }}">
    <meta property="og:image" content="{{ $openGraphImage }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:type" content="{{ $openGraphType }}">

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#10271d',
                        danger: '#d92525',
                        cream: '#fcfaf6'
                    },
                    fontFamily: {
                        sans: ['Be Vietnam Pro', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        heading: ['Be Vietnam Pro', 'ui-sans-serif', 'system-ui', 'sans-serif']
                    }
                }
            }
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --maxball-desktop-scale: 0.9;
        }

        @media (min-width: 1024px) {
            html {
                zoom: var(--maxball-desktop-scale);
            }

            .h-screen {
                height: 111.111111vh !important;
            }

            .min-h-screen {
                min-height: 111.111111vh !important;
            }
        }

        body,
        button,
        input,
        select,
        textarea {
            font-family: 'Be Vietnam Pro', sans-serif;
        }

        .font-heading { font-family: 'Be Vietnam Pro', sans-serif; }
    </style>

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    
    <!-- AOS CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @stack('styles')
    @stack('head')
</head>
<body class="flex flex-col min-h-screen bg-cream text-gray-900">
    @include('client.partials.header')

    <main class="flex-1">
        @if (session('success'))
            <div id="global-alert-success" class="fixed top-24 right-4 z-[9999] max-w-sm rounded-xl border border-green-200 bg-green-50 px-6 py-4 text-sm font-semibold text-green-800 shadow-2xl flex items-center gap-3 transition-opacity duration-500">
                <i class="fa-solid fa-circle-check text-xl"></i>
                {{ session('success') }}
            </div>
            <script>
                setTimeout(() => {
                    const el = document.getElementById('global-alert-success');
                    if (el) {
                        el.style.opacity = '0';
                        setTimeout(() => el.remove(), 500);
                    }
                }, 4000);
            </script>
        @endif

        @if ($errors->any())
            <div id="global-alert-error" class="fixed top-24 right-4 z-[9999] max-w-sm rounded-xl border border-red-200 bg-red-50 px-6 py-4 text-sm font-semibold text-red-800 shadow-2xl flex items-center gap-3 transition-opacity duration-500">
                <i class="fa-solid fa-circle-exclamation text-xl"></i>
                {{ $errors->first() }}
            </div>
            <script>
                setTimeout(() => {
                    const el = document.getElementById('global-alert-error');
                    if (el) {
                        el.style.opacity = '0';
                        setTimeout(() => el.remove(), 500);
                    }
                }, 4000);
            </script>
        @endif

        @if (session('error'))
            <div id="global-session-error" class="fixed top-24 right-4 z-[9999] max-w-sm rounded-xl border border-red-200 bg-red-50 px-6 py-4 text-sm font-semibold text-red-800 shadow-2xl flex items-center gap-3 transition-opacity duration-500">
                <i class="fa-solid fa-circle-exclamation text-xl"></i>
                {{ session('error') }}
            </div>
            <script>
                setTimeout(() => {
                    const el = document.getElementById('global-session-error');
                    if (el) {
                        el.style.opacity = '0';
                        setTimeout(() => el.remove(), 500);
                    }
                }, 4000);
            </script>
        @endif

        @yield('content')
    </main>

    @include('client.partials.footer')

    @include('shared.confirm-dialog')

    @include('client.partials.floating-promotions')

    @include('client.partials.chatbot')

    @stack('scripts')
    
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
    <!-- AOS JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: false,
            offset: 100,
        });
    </script>
</body>
</html>
