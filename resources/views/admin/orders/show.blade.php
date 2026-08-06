@extends('admin.layouts.app')

@section('title', 'Chi tiết Đơn hàng #' . $order->order_code)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-end mb-4">
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
                    <div class="mb-4">
                            <label class="form-label fw-bold">Trạng thái thanh toán</label>
                            <div>
                                    @if($order->payment_status == 'paid')
                                        <span class="badge bg-success px-3 py-2">Đã thanh toán ({{ strtoupper($order->payment_method) }})</span>
                                    @elseif($order->payment_status == 'failed')
                                        <span class="badge bg-danger px-3 py-2">Thất bại</span>
                                    @else
                                        @if($order->payment_method == 'cod' && $order->order_status == 'shipping')
                                            <form action="{{ route('admin.orders.updatePaymentStatus', $order->id) }}" method="POST" class="m-0">
                                                @csrf
                                                @method('PATCH')
                                                <select name="payment_status" class="form-select form-select-sm d-inline-block w-auto" onchange="if(confirm('Xác nhận đã thu tiền COD cho đơn hàng này?')) this.form.submit(); else this.value='pending';">
                                                    <option value="pending" selected>Chưa thanh toán (COD)</option>
                                                    <option value="paid">Đã thanh toán (COD)</option>
                                                </select>
                                            </form>
                                        @else
                                            <span class="badge bg-warning text-dark px-3 py-2">Chưa thanh toán (COD)</span>
                                        @endif
                                    @endif
                            </div>
                        </div>

                    <form id="order-status-form" action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <label class="form-label fw-bold">Trạng thái đơn hàng</label>
                            @if(in_array($order->order_status, ['completed', 'cancelled']))
                                <div>
                                    @if($order->order_status == 'completed')
                                        <span class="badge bg-success px-3 py-2">Hoàn thành</span>
                                    @else
                                        <span class="badge bg-danger px-3 py-2">Đã hủy</span>
                                        @if($order->cancellation_reason)
                                            <div class="mt-3 rounded border border-danger-subtle bg-danger-subtle p-3 small text-danger-emphasis">
                                                <p class="mb-1"><strong>Người hủy:</strong> {{ $order->cancelled_by === 'admin' ? 'Cửa hàng' : 'Khách hàng' }}</p>
                                                <p class="mb-1"><strong>Lý do:</strong> {{ $order->cancellation_reason_label }}</p>
                                                @if($order->cancellation_note)
                                                    <p class="mb-1 whitespace-pre-line"><strong>Ghi chú:</strong> {{ $order->cancellation_note }}</p>
                                                @endif
                                                @if($order->cancelled_at)
                                                    <p class="mb-0"><strong>Thời gian:</strong> {{ $order->cancelled_at->format('d/m/Y H:i') }}</p>
                                                @endif
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            @else
                                <select name="order_status"
                                        class="form-select"
                                        id="orderStatusSelect"
                                        data-admin-cancel
                                        data-order-id="{{ $order->id }}"
                                        data-order-code="{{ $order->order_code }}"
                                        data-current-status="{{ $order->order_status }}"
                                        data-cancel-action="{{ route('admin.orders.updateStatus', $order->id) }}">
                                    @if($order->order_status == 'pending')
                                        <option value="pending" selected>Chờ xác nhận</option>
                                        <option value="processing">Đã xác nhận</option>
                                        <option value="shipping">Đang giao hàng</option>
                                        <option value="cancelled">Hủy đơn hàng</option>
                                    @elseif($order->order_status == 'processing')
                                        <option value="processing" selected>Đã xác nhận</option>
                                        <option value="shipping">Đang giao hàng</option>
                                        <option value="cancelled">Hủy đơn hàng</option>
                                    @elseif($order->order_status == 'shipping')
                                        <option value="shipping" selected>Đang giao hàng</option>
                                        @if($order->payment_status == 'paid')
                                            <option value="completed">Hoàn thành</option>
                                        @endif
                                    @endif
                                </select>
                            @endif
                        </div>
                        
                        @if(!in_array($order->order_status, ['completed', 'cancelled']))
                            <button type="submit" class="btn btn-primary w-100">Cập nhật</button>
                        @endif
                    </form>
                </div>
            </div>

<script>
document.getElementById('order-status-form')?.addEventListener('submit', async (event) => {
    const form = event.currentTarget;
    const status = document.getElementById('orderStatusSelect')?.value;
    let options = null;

    if (status === 'completed') {
        options = {
            title: 'Hoàn thành đơn hàng',
            message: 'Xác nhận đơn hàng đã được giao thành công và chuyển sang trạng thái hoàn thành?',
            confirmLabel: 'Hoàn thành',
            variant: 'primary',
        };
    } else if (status === 'cancelled') {
        event.preventDefault();
        window.openAdminCancelModal(document.getElementById('orderStatusSelect'));

        return;
    }

    if (!options) return;

    event.preventDefault();
    if (await window.AppConfirm.open(options)) {
        HTMLFormElement.prototype.submit.call(form);
    }
});
</script>

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

@include('admin.orders._cancel_modal')
@endsection
