<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - MaxBall')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <style>
        body {
            background: #f5f6f8;
        }

        .admin-sidebar {
            min-height: 100vh;
            background: #111827;
        }

        .admin-sidebar .nav-link {
            color: rgba(255, 255, 255, 0.72);
            border-radius: 10px;
        }

        .admin-sidebar .nav-link.active,
        .admin-sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.12);
        }

        .product-thumb {
            width: 72px;
            height: 72px;
            object-fit: cover;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="d-lg-flex">
        <aside class="admin-sidebar p-4 text-white">
            <a href="{{ route('admin.dashboard') }}" class="d-block mb-4 text-decoration-none text-white">
                <span class="fs-4 fw-bold">MaxBall Admin</span>
            </a>

            <nav class="nav flex-column gap-2">
                <a class="nav-link active" href="{{ route('admin.products.index') }}">Quản lý sản phẩm</a>
                <a class="nav-link active" href="{{ route('admin.categories.index') }}">Quản lý danh mục</a>
                <a class="nav-link" href="{{ route('client.products.index') }}" target="_blank">Xem trang client</a>
            </nav>
        </aside>

        <div class="flex-grow-1">
            <header class="border-bottom bg-white">
                <div class="container-fluid px-4 py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-0">@yield('page_title', 'Admin')</h1>
                    </div>
                </div>
            </header>

            <main class="container-fluid p-4">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Dong"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    @stack('scripts')
</body>
</html>
