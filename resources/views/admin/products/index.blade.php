@extends('admin.layouts.app')

@section('title', 'Quản lý sản phẩm - MaxBall')
@section('page_title', 'Quản lý sản phẩm')

@section('content')
    @php
        $showOutOfStockVariants = request('stock_status') === 'out_of_stock';
    @endphp

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-3 mb-4">
                <form id="admin-product-filters" class="row g-2 flex-grow-1" method="GET" action="{{ route('admin.products.index') }}">
                    <div class="col-12 col-md-6 col-xl-3">
                        <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Tìm theo tên, mô tả...">
                    </div>
                    <div class="col-12 col-md-3 col-xl-2">
                        <select name="category_id" class="form-select">
                            <option value="">Tất cả danh mục</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>
                                    {{ $cat->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3 col-xl-2">
                        <select name="status" class="form-select">
                            <option value="">Tất cả trạng thái</option>
                            <option value="1" @selected(request('status') === '1')>Đang hiện</option>
                            <option value="0" @selected(request('status') === '0')>Đang ẩn</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 col-xl-2">
                        <select name="stock_status" class="form-select">
                            <option value="">Tất cả tồn kho</option>
                            <option value="in_stock" @selected(request('stock_status') === 'in_stock')>Còn hàng</option>
                            <option value="out_of_stock" @selected(request('stock_status') === 'out_of_stock')>Hết hàng</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2 col-xl-2">
                        <select name="per_page" class="form-select" onchange="this.form.submit()">
                            <option value="10" @selected(request('per_page', 10) == 10)>10 dòng</option>
                            <option value="25" @selected(request('per_page') == 25)>25 dòng</option>
                            <option value="50" @selected(request('per_page') == 50)>50 dòng</option>
                            <option value="100" @selected(request('per_page') == 100)>100 dòng</option>
                        </select>
                    </div>
                </form>

                <div class="d-flex flex-column gap-2 align-self-start mt-2 mt-xl-0">
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-dark" type="submit" form="admin-product-filters">Lọc</button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Xóa</a>
                    </div>
                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary text-nowrap">Thêm sản phẩm</a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 90px;">Ảnh</th>
                            <th>Sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Giá</th>
                            @if ($showOutOfStockVariants)
                                <th>Biến thể hết hàng</th>
                            @else
                                <th class="text-center">Tồn kho</th>
                            @endif
                            <th>Trạng thái</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            @php
                                $availableStock = max(0, (int) $product->variants_sum_stock - (int) $product->variants_sum_reserved_stock);
                            @endphp
                            <tr>
                                <td>
                                    <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" class="product-thumb rounded border">
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $product->name }}</div>
                                    <small class="text-muted">{{ $product->slug }}</small>
                                </td>
                                <td>{{ $product->category_name }}</td>
                                <td>
                                    <div class="fw-bold text-danger">{{ number_format($product->discount_price ?: $product->base_price, 0, ',', '.') }}d</div>
                                    @if ($product->discount_price)
                                        <small class="text-muted text-decoration-line-through">{{ number_format($product->base_price, 0, ',', '.') }}d</small>
                                    @endif
                                </td>
                                @if ($showOutOfStockVariants)
                                    <td>
                                        @forelse ($product->variants as $variant)
                                            <span class="badge text-bg-danger me-1 mb-1">
                                                {{ trim((string) $variant->name) ?: 'Mặc định' }}
                                            </span>
                                        @empty
                                            <span class="text-muted fst-italic">Chưa có biến thể</span>
                                        @endforelse
                                    </td>
                                @else
                                    <td class="text-center">
                                        <span class="fw-bold text-dark">
                                            {{ number_format((int) $product->variants_sum_stock, 0, ',', '.') }}
                                        </span>
                                    </td>
                                @endif
                                <td>
                                    @if ($product->status)
                                        <span class="badge text-bg-success">Đang hiện</span>
                                    @else
                                        <span class="badge text-bg-secondary">Đang ẩn</span>
                                    @endif

                                    @if ((int) $product->variants_sum_reserved_stock > 0)
                                        <div class="text-primary fw-bold mt-2" style="font-size: 0.8rem;">
                                            Đang giữ: {{ (int) $product->variants_sum_reserved_stock }}
                                        </div>
                                    @endif

                                    @if ((int) $product->out_of_stock_variants_count > 0)
                                        <div class="text-danger fw-bold mt-2" style="font-size: 0.8rem;">
                                            {{ (int) $product->out_of_stock_variants_count }} biến thể hết hàng
                                        </div>
                                    @endif

                                    @if ($availableStock > 0 && $availableStock < 5)
                                        <div class="text-warning fw-bold mt-2" style="font-size: 0.8rem;">
                                            Có thể bán: còn {{ $availableStock }}
                                        </div>
                                    @elseif ($availableStock === 0)
                                        <div class="text-danger fw-bold mt-2" style="font-size: 0.8rem;">
                                            Hết hàng khả dụng
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('admin.products.show', $product) }}" class="btn btn-sm btn-outline-info">Xem</a>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-primary">Sửa</a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="d-inline"
                                          data-confirm="Sản phẩm sẽ bị xóa khỏi hệ thống. Bạn có chắc chắn muốn tiếp tục?"
                                          data-confirm-title="Xóa sản phẩm"
                                          data-confirm-label="Xóa sản phẩm"
                                          data-confirm-variant="danger">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-5 text-center text-muted">Chưa có sản phẩm nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap">
                <div class="text-muted small mb-2 mb-md-0">
                    Hiển thị {{ $products->firstItem() ?? 0 }} đến {{ $products->lastItem() ?? 0 }} của {{ $products->total() }} sản phẩm
                </div>
                <div>{{ $products->links() }}</div>
            </div>
        </div>
    </div>
@endsection
