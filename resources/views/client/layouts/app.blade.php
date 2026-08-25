<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MaxBall - Football Jersey Shop')</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#10271d',
                        danger: '#d92525',
                        cream: '#fcfaf6'
                    }
                }
            }
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700;800&family=Fraunces:wght@700;900&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'DM Sans', sans-serif; }
        .font-heading { font-family: 'Fraunces', serif; }
    </style>

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    
    <!-- AOS CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @stack('styles')
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
