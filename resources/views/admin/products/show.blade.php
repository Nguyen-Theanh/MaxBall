@extends('admin.layouts.app')

@section('title', 'Chi tiết Sản phẩm: ' . $product->name)
@section('page_title', 'Chi tiết Sản phẩm')

@section('content')
<div class="d-flex justify-content-end mb-4">
    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm">
        Quay lại danh sách
    </a>
</div>

<div class="row">
    <!-- Left Column: Product Info -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-primary">Thông tin chung</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr>
                            <th style="width: 150px;">Tên sản phẩm</th>
                            <td class="fw-bold">{{ $product->name }}</td>
                        </tr>
                        <tr>
                            <th>Đường dẫn (Slug)</th>
                            <td>{{ $product->slug }}</td>
                        </tr>
                        <tr>
                            <th>Danh mục</th>
                            <td>{{ $product->category->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Trạng thái</th>
                            <td>
                                @if ($product->status)
                                    <span class="badge text-bg-success">Đang hiện</span>
                                @else
                                    <span class="badge text-bg-secondary">Đang ẩn</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Giá đại diện</th>
                            <td>
                                <span class="fw-bold text-danger">{{ number_format($product->discount_price ?: $product->base_price, 0, ',', '.') }}đ</span>
                                @if ($product->discount_price)
                                    <small class="text-muted text-decoration-line-through ms-2">{{ number_format($product->base_price, 0, ',', '.') }}đ</small>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary">Biến thể sản phẩm ({{ $product->variants->count() }})</h6>
                <span class="badge bg-primary">Tổng tồn: {{ number_format($product->variants->sum('stock'), 0, ',', '.') }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ảnh</th>
                                <th>Tên biến thể</th>
                                <th>SKU</th>
                                <th>Giá bán</th>
                                <th>Tồn kho</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($product->variants as $variant)
                                <tr>
                                    <td class="ps-3">
                                        @if($variant->image_url)
                                            <img src="{{ $variant->image_url }}" alt="" class="rounded border" style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded border d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="fw-bold">{{ $variant->name }}</td>
                                    <td><code>{{ $variant->sku }}</code></td>
                                    <td>
                                        <div class="text-danger fw-bold">{{ number_format($variant->discount_price ?: $variant->base_price, 0, ',', '.') }}đ</div>
                                        @if($variant->discount_price)
                                            <small class="text-muted text-decoration-line-through">{{ number_format($variant->base_price, 0, ',', '.') }}đ</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($variant->stock > 0)
                                            <span class="badge bg-success">{{ number_format($variant->stock, 0, ',', '.') }}</span>
                                        @else
                                            <span class="badge bg-danger">Hết hàng</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Sản phẩm này không có biến thể nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-primary">Mô tả chi tiết</h6>
            </div>
            <div class="card-body">
                @if($product->description)
                    <div>
                        {!! $product->description !!}
                    </div>
                @else
                    <p class="text-muted fst-italic mb-0">Chưa có mô tả.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column: Images -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-primary">Ảnh đại diện</h6>
            </div>
            <div class="card-body text-center">
                @if($product->thumbnail_url)
                    <img src="{{ $product->thumbnail_url }}" alt="Thumbnail" class="img-fluid rounded border shadow-sm" style="max-height: 300px; object-fit: cover;">
                @else
                    <div class="bg-light p-5 rounded border text-muted">
                        Không có ảnh đại diện
                    </div>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-primary">Thư viện ảnh ({{ $product->productImages->count() }})</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    @forelse($product->productImages as $image)
                        <div class="col-4">
                            <img src="{{ Storage::url($image->image_url) }}" alt="Gallery" class="img-fluid rounded border" style="height: 100px; width: 100%; object-fit: cover;">
                        </div>
                    @empty
                        <div class="col-12 text-center text-muted fst-italic">
                            Chưa có ảnh thư viện.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
