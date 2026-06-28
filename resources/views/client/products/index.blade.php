@extends('client.layouts.app')

@section('title', 'Sản phẩm - MaxBall')

@push('styles')
    <style>
        .product-img {
            height: 320px;
            width: 100%;
            object-position: center;
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-img {
            transform: scale(1.03);
        }

        .btn-cart {
            transition: transform 0.2s ease, opacity 0.2s ease, background-color 0.2s ease;
        }

        .btn-cart:active {
            transform: scale(0.97);
        }
        .hero-section {
            background-image: url('https://cdn-media.sforum.vn/storage/app/media/wp-content/uploads/2023/06/hinh-nen-bong-da-thumb.jpg');
            background-size: cover;
            background-position: center;
            }
    </style>
@endpush

@section('content')
    <section class="hero-section text-white py-5">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <span class="badge bg-warning text-dark mb-3 text-uppercase fw-bold">Football Jersey Shop</span>
                    <h1 class="display-5 fw-bold">Bộ sưu tập áo bóng đá 2026</h1>
                    <p class="lead text-white-75">Chọn nhanh các mẫu áo nổi bật, chất vải thoáng, form đẹp và sẵn sàng giao cho đội bóng của bạn.</p>
                    <a href="#products" class="btn btn-warning btn-lg mt-3">Mua ngay</a>
                </div>
                <div class="col-lg-5 d-none d-lg-block">
                    <div class="ratio ratio-4x3 rounded-4 overflow-hidden shadow-lg">
                        <img src="https://file.hstatic.net/200000722513/article/doi-tuyen-viet-nam-12-1_0c43fc569c8b4a8cb92fb870c0358a3b.jpg" alt="Cầu thủ bóng đá trên sân" class="object-fit-cover">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container py-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row gy-3 align-items-center">
                    <div class="col-12 col-lg-4">
                        <div>
                            <h5 class="fw-bold mb-1">Lọc sản phẩm theo giá</h5>
                            <p class="text-muted mb-0">Nhập khoảng giá và nhấn Áp dụng để xem sản phẩm phù hợp.</p>
                        </div>
                    </div>
                    <div class="col-12 col-lg-8">
                        <form method="GET" action="{{ route('client.products.index') }}" class="row g-2 align-items-end">
                            <div class="col-12 col-sm-4">
                                <label for="price_min" class="form-label mb-1">Giá thấp nhất</label>
                                <input type="number" id="price_min" name="price_min" min="0" step="1000" class="form-control" value="{{ old('price_min', $priceMin ?? '') }}" placeholder="0">
                            </div>
                            <div class="col-12 col-sm-4">
                                <label for="price_max" class="form-label mb-1">Giá cao nhất</label>
                                <input type="number" id="price_max" name="price_max" min="0" step="1000" class="form-control" value="{{ old('price_max', $priceMax ?? '') }}" placeholder="2.000.000">
                            </div>
                            <div class="col-12 col-sm-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">Áp dụng</button>
                                <a href="{{ route('client.products.index') }}" class="btn btn-outline-secondary w-100">Xóa</a>
                            </div>
                        </form>
                    </div>
                </div>
                @if(is_numeric($priceMin) || is_numeric($priceMax))
                    <div class="mt-3 text-muted">
                        <small>
                            Hiển thị sản phẩm theo giá
                            @if(is_numeric($priceMin)) từ <strong>{{ number_format($priceMin, 0, ',', '.') }}đ</strong>@endif
                            @if(is_numeric($priceMax)) đến <strong>{{ number_format($priceMax, 0, ',', '.') }}đ</strong>@endif
                        </small>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section id="products" class="container py-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-4 gap-3">
            <div>
                <p class="text-uppercase fw-bold text-danger mb-2">Bộ sưu tập mới</p>
                <h2 class="display-6 fw-bold">Sản phẩm nổi bật</h2>
            </div>
        </div>

        <div class="row g-4">
            @forelse ($products as $product)
                @php
                    $price = $product->discount_price ?: $product->base_price;
                @endphp

                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card h-100 product-card border-0 shadow-sm">
                        <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" class="card-img-top product-img" loading="lazy">
                        <div class="card-body d-flex flex-column">
                            <span class="badge bg-success mb-3">{{ $product->category_name }}</span>
                            <h3 class="h5 fw-bold">{{ $product->name }}</h3>
                            <p class="text-muted mb-4">{{ \Illuminate\Support\Str::limit($product->description ?: 'Áo đấu thiết kế hiện đại, chất liệu thoáng nhẹ và dễ vận động.', 105) }}</p>
                            <div class="mt-auto">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div>
                                        <p class="h5 text-danger mb-0">{{ number_format($price, 0, ',', '.') }}đ</p>
                                        @if ($product->discount_price)
                                            <small class="text-muted text-decoration-line-through">{{ number_format($product->base_price, 0, ',', '.') }}đ</small>
                                        @endif
                                    </div>
                                </div>
                                <button type="button" class="btn btn-primary btn-cart w-100">Thêm vào giỏ</button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-warning">Chưa có sản phẩm nào trong kho, vui lòng thử lại sau.</div>
                </div>
            @endforelse
        </div>
    </section>

    <section id="deal" class="text-white py-5"
    style="background: url('https://m.media-amazon.com/images/I/611gcLkTitL._AC_UF1000,1000_QL80_.jpg') center/cover no-repeat;">
        <div class="container text-center">
            <h2 class="display-6 fw-bold">Giảm 15% cho đơn đồng đội từ 5 áo</h2>
            <p class="lead text-white-75 mt-3">Đặt áo cho team, lớp học hoặc câu lạc bộ và nhận tư vấn size nhanh trong ngày.</p>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        const cartCount = document.getElementById('cart-count');
        let count = 0;

        document.querySelectorAll('.btn-cart').forEach((button) => {
            button.addEventListener('click', () => {
                count += 1;
                cartCount.textContent = count;
                button.textContent = 'Đã thêm';
                button.style.opacity = '0.75';

                window.setTimeout(() => {
                    button.textContent = 'Thêm vào giỏ';
                    button.style.opacity = '1';
                }, 650);
            });
        });
    </script>
@endpush
