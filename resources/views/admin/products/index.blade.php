@extends('admin.layouts.app')

@section('title', 'Quan ly san pham - MaxBall')
@section('page_title', 'Quản lý sản phẩm')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-3 mb-4">
                <form class="row g-2 flex-grow-1" method="GET" action="{{ route('admin.products.index') }}">
                    <div class="col-12 col-md-6 col-xl-5">
                        <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Tìm theo tên, mô tả...">
                    </div>
                    <div class="col-12 col-md-3 col-xl-2">
                        <select name="status" class="form-select">
                            <option value="">Tất cả trạng thái</option>
                            <option value="1" @selected(request('status') === '1')>Đang hiện</option>
                            <option value="0" @selected(request('status') === '0')>Đang ẩn</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-auto">
                        <button class="btn btn-dark" type="submit">Lọc</button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Xóa</a>
                    </div>
                </form>

                <a href="{{ route('admin.products.create') }}" class="btn btn-primary align-self-start">Thêm sản phẩm</a>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 90px;">Ảnh</th>
                            <th>Sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Giá</th>
                            <th>Trạng thái</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
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
                                <td>
                                    @if ($product->status)
                                        <span class="badge text-bg-success">Đang hiện</span>
                                    @else
                                        <span class="badge text-bg-secondary">Đang ẩn</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-primary">Sửa</a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="d-inline" onsubmit="return confirm('Xoa san pham nay?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-5 text-center text-muted">Chưa có sản phẩm nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $products->links() }}
            </div>
        </div>
    </div>
@endsection
