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
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xác nhận</option>
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
                                    @if(in_array($order->order_status, ['completed', 'cancelled']))
                                        @if($order->order_status == 'completed')
                                            <span class="badge bg-success px-2 py-2">Hoàn thành</span>
                                        @else
                                            <span class="badge bg-danger px-2 py-2">Đã hủy</span>
                                        @endif
                                    @else
                                        <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" class="m-0">
                                            @csrf
                                            @method('PATCH')
                                            <select name="order_status" class="form-select form-select-sm" style="min-width: 140px;" onchange="confirmAndSubmit(this)">
                                                @if($order->order_status == 'pending')
                                                    <option value="pending" selected>Chờ xác nhận</option>
                                                    <option value="shipping">Đang giao hàng</option>
                                                    <option value="cancelled">Hủy đơn hàng</option>
                                                @elseif($order->order_status == 'shipping')
                                                    <option value="shipping" selected>Đang giao hàng</option>
                                                    <option value="completed">Hoàn thành</option>
                                                    <option value="cancelled">Hủy đơn hàng</option>
                                                @endif
                                            </select>
                                        </form>
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

<script>
async function confirmAndSubmit(selectElement) {
    const form = selectElement.closest('form');
    const status = selectElement.value;
    let options = null;

    if (status === 'completed') {
        options = {
            title: 'Hoàn thành đơn hàng',
            message: 'Xác nhận đơn hàng đã được giao thành công và chuyển sang trạng thái hoàn thành?',
            confirmLabel: 'Hoàn thành',
            variant: 'primary',
        };
    } else if (status === 'cancelled') {
        options = {
            title: 'Hủy đơn hàng',
            message: 'Đơn hàng sẽ bị hủy và số lượng sản phẩm sẽ được hoàn lại kho. Thao tác này không thể hoàn tác.',
            confirmLabel: 'Hủy đơn',
            variant: 'warning',
        };
    }

    if (!options || await window.AppConfirm.open(options)) {
        HTMLFormElement.prototype.submit.call(form);
    } else {
        form.reset();
    }
}
</script>
@endsection
