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

    @stack('styles')
</head>
<body class="flex flex-col min-h-screen bg-cream text-gray-900">
    @include('client.partials.header')

    <main class="flex-1">
        @yield('content')
    </main>

    @include('client.partials.footer')

    @stack('scripts')
</body>
</html>