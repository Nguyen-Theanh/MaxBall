@extends('admin.layouts.app')

@section('title', 'Quản lý Đơn hàng')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý Đơn hàng</h1>
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

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách Đơn hàng</h6>
            
            <form action="{{ route('admin.orders.index') }}" method="GET" class="d-flex gap-2">
                <select name="status" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                    <option value="shipping" {{ request('status') == 'shipping' ? 'selected' : '' }}>Đang giao hàng</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                </select>
                <div class="input-group input-group-sm w-auto">
                    <input type="text" name="search" class="form-control" placeholder="Mã ĐH, Tên, SĐT..." value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit">Tìm</button>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã ĐH</th>
                            <th>Khách hàng</th>
                            <th>Tổng tiền</th>
                            <th>Thanh toán</th>
                            <th>Trạng thái</th>
                            <th>Ngày đặt</th>
                            <th class="text-center" width="120">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td class="font-weight-bold">#{{ $order->order_code }}</td>
                                <td>
                                    <div class="fw-bold">{{ $order->customer_name }}</div>
                                    <div class="small text-muted">{{ $order->customer_phone }}</div>
                                </td>
                                <td class="text-danger fw-bold">{{ number_format($order->total_amount, 0, ',', '.') }}đ</td>
                                <td>
                                    @if($order->payment_status == 'paid')
                                        <span class="badge bg-success">Đã thanh toán ({{ strtoupper($order->payment_method) }})</span>
                                    @elseif($order->payment_status == 'failed')
                                        <span class="badge bg-danger">Thất bại</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Chưa thanh toán (COD)</span>
                                    @endif
                                </td>
                                <td>
                                    @if($order->order_status == 'pending')
                                        <span class="badge bg-warning text-dark">Chờ xử lý</span>
                                    @elseif($order->order_status == 'shipping')
                                        <span class="badge bg-info text-dark">Đang giao hàng</span>
                                    @elseif($order->order_status == 'completed')
                                        <span class="badge bg-success">Hoàn thành</span>
                                    @elseif($order->order_status == 'cancelled')
                                        <span class="badge bg-danger">Đã hủy</span>
                                    @endif
                                </td>
                                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-primary btn-sm" title="Chi tiết">
                                        Chi tiết
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">Chưa có đơn hàng nào!</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
