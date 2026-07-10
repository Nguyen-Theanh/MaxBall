@extends('admin.layouts.app')

@section('title', 'Chi tiết Đơn hàng #' . $order->order_code)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Chi tiết Đơn hàng #{{ $order->order_code }}</h1>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">
            Quay lại danh sách
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Cập nhật trạng thái -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Cập nhật trạng thái</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Trạng thái thanh toán</label>
                            <select name="payment_status" class="form-select">
                                <option value="pending" {{ $order->payment_status == 'pending' ? 'selected' : '' }}>Chưa thanh toán</option>
                                <option value="paid" {{ $order->payment_status == 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                                <option value="failed" {{ $order->payment_status == 'failed' ? 'selected' : '' }}>Thất bại</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Trạng thái đơn hàng</label>
                            <select name="order_status" class="form-select">
                                <option value="pending" {{ $order->order_status == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                                <option value="shipping" {{ $order->order_status == 'shipping' ? 'selected' : '' }}>Đang giao hàng</option>
                                <option value="completed" {{ $order->order_status == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                                <option value="cancelled" {{ $order->order_status == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Cập nhật</button>
                    </form>
                </div>
            </div>

            <!-- Thông tin khách hàng -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin nhận hàng</h6>
                </div>
                <div class="card-body">
                    <p><strong>Họ tên:</strong> {{ $order->customer_name }}</p>
                    <p><strong>Số điện thoại:</strong> {{ $order->customer_phone }}</p>
                    <p><strong>Email:</strong> {{ $order->customer_email ?? 'Không có' }}</p>
                    <p class="mb-0"><strong>Địa chỉ:</strong> {{ $order->customer_address }}</p>
                </div>
            </div>
        </div>

        <!-- Chi tiết sản phẩm & thanh toán -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Sản phẩm trong đơn</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Phân loại</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-end">Đơn giá</th>
                                    <th class="text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->details as $detail)
                                    @php
                                        $product = $detail->variant->product;
                                        $thumbnail = $product->thumbnail_url ?? null;
                                        if (!$thumbnail && !empty($product->thumbnail)) {
                                            $thumbnail = str_starts_with($product->thumbnail, 'http') ? $product->thumbnail : asset('storage/' . $product->thumbnail);
                                        }
                                        if (!$thumbnail) {
                                            $thumbnail = 'https://via.placeholder.com/150';
                                        }
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="{{ $thumbnail }}" alt="{{ $product->name }}" class="rounded" width="50" height="60" style="object-fit: cover;">
                                                <a href="{{ route('client.products.show', $product->slug) }}" target="_blank" class="text-decoration-none fw-bold text-dark">
                                                    {{ $product->name }}
                                                </a>
                                            </div>
                                        </td>
                                        <td>{{ $detail->variant->name }}</td>
                                        <td class="text-center">{{ $detail->quantity }}</td>
                                        <td class="text-end">{{ number_format($detail->price, 0, ',', '.') }}đ</td>
                                        <td class="text-end fw-bold text-danger">{{ number_format($detail->price * $detail->quantity, 0, ',', '.') }}đ</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="row justify-content-end">
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <td>Tạm tính:</td>
                                        <td class="text-end fw-bold">{{ number_format($order->sub_total, 0, ',', '.') }}đ</td>
                                    </tr>
                                    <tr>
                                        <td>Phí giao hàng:</td>
                                        <td class="text-end fw-bold">{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</td>
                                    </tr>
                                    @if($order->discount_amount > 0)
                                        <tr>
                                            <td>Giảm giá:</td>
                                            <td class="text-end fw-bold text-success">-{{ number_format($order->discount_amount, 0, ',', '.') }}đ</td>
                                        </tr>
                                    @endif
                                    <tr class="border-top">
                                        <td class="fs-5 fw-bold">Tổng cộng:</td>
                                        <td class="text-end fs-4 fw-black text-danger">{{ number_format($order->total_amount, 0, ',', '.') }}đ</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
