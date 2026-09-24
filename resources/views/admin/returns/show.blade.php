@extends('admin.layouts.app')

@section('title', 'Chi tiết Yêu cầu ' . $orderReturn->type_label)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header pb-0 border-b">
                <div class="d-flex justify-content-between align-items-center">
                    <h6>Chi tiết Yêu cầu {{ $orderReturn->type_label }} #{{ $orderReturn->id }}</h6>
                    <span class="badge 
                        @if($orderReturn->status == 'pending') bg-secondary
                        @elseif($orderReturn->status == 'processing') bg-info
                        @elseif($orderReturn->status == 'resolved') bg-success
                        @else bg-danger @endif">
                        {{ $orderReturn->status_label }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p class="text-sm text-secondary mb-1">Khách hàng</p>
                        <h6 class="mb-0">{{ $orderReturn->user->name }}</h6>
                        <p class="text-sm mb-0">{{ $orderReturn->user->email }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-sm text-secondary mb-1">Đơn hàng liên quan</p>
                        <h6 class="mb-0"><a href="{{ route('admin.orders.show', $orderReturn->order->id) }}" class="text-primary">#{{ $orderReturn->order->order_code }}</a></h6>
                        <p class="text-sm mb-0">Tổng tiền: <span class="text-danger font-weight-bold">{{ number_format($orderReturn->order->total_amount, 0, ',', '.') }}đ</span></p>
                    </div>
                </div>

                <hr class="horizontal dark">

                <div class="mb-4">
                    <h6 class="text-sm mb-2">Lý do yêu cầu:</h6>
                    <p class="text-sm font-weight-bold">{{ $orderReturn->reason }}</p>
                    
                    <h6 class="text-sm mt-3 mb-2">Mô tả chi tiết:</h6>
                    <div class="p-3 bg-gray-100 rounded text-sm">
                        {{ $orderReturn->description }}
                    </div>
                </div>

                @if($orderReturn->images && is_array($orderReturn->images))
                    <hr class="horizontal dark">
                    <h6 class="text-sm mb-3">Hình ảnh bằng chứng:</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($orderReturn->images as $image)
                            <a href="{{ asset('storage/' . $image) }}" target="_blank">
                                <img src="{{ asset('storage/' . $image) }}" class="rounded" style="width: 120px; height: 120px; object-fit: cover; border: 1px solid #dee2e6;">
                            </a>
                        @endforeach
                    </div>
                @endif
                
                @if($orderReturn->refund_amount)
                    <hr class="horizontal dark">
                    <h6 class="text-sm mb-3">Thông tin hoàn tiền:</h6>
                    <div class="p-3 bg-success-soft rounded border border-success">
                        <p class="text-sm mb-0 text-success">Đã hoàn <strong>{{ number_format($orderReturn->refund_amount, 0, ',', '.') }}đ</strong> vào Ví MaxBall của khách hàng.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header pb-0 border-b">
                <h6>Cập nhật trạng thái</h6>
            </div>
            <div class="card-body">
                @if(in_array($orderReturn->status, ['resolved', 'rejected']))
                    <div class="alert {{ $orderReturn->status == 'resolved' ? 'alert-success' : 'alert-danger' }} text-white text-sm" role="alert">
                        Yêu cầu này đã được xử lý xong ({{ $orderReturn->status_label }}).
                    </div>
                    @if($orderReturn->admin_note)
                        <h6 class="text-sm mt-3 mb-2">Ghi chú của Admin:</h6>
                        <div class="p-3 bg-gray-100 rounded text-sm mb-0">
                            {{ $orderReturn->admin_note }}
                        </div>
                    @endif
                @else
                    <form action="{{ route('admin.returns.updateStatus', $orderReturn->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        <div class="mb-3">
                            <label class="form-control-label">Trạng thái mới <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" id="status-select" required>
                                <option value="processing" {{ $orderReturn->status == 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                                <option value="resolved">Đã giải quyết (Đồng ý)</option>
                                <option value="rejected">Từ chối</option>
                            </select>
                        </div>
                        
                        <div class="mb-3 d-none" id="refund-amount-group">
                            <label class="form-control-label">Số tiền hoàn (VND) <span class="text-danger">*</span></label>
                            <input type="number" name="refund_amount" class="form-control" placeholder="Nhập số tiền..." min="0" max="{{ $orderReturn->order->total_amount }}" value="{{ $orderReturn->order->total_amount }}">
                            <small class="text-muted text-xs">Tối đa: {{ number_format($orderReturn->order->total_amount, 0, ',', '.') }}đ</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-control-label">Ghi chú phản hồi khách hàng</label>
                            <textarea name="admin_note" class="form-control" rows="3" placeholder="Nhập ghi chú..."></textarea>
                        </div>

                        <button type="submit" class="btn bg-gradient-primary w-100 mb-0">Cập nhật yêu cầu</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusSelect = document.getElementById('status-select');
        const refundGroup = document.getElementById('refund-amount-group');
        
        if (statusSelect && refundGroup) {
            statusSelect.addEventListener('change', function() {
                if (this.value === 'resolved') {
                    refundGroup.classList.remove('d-none');
                } else {
                    refundGroup.classList.add('d-none');
                }
            });
        }
    });
</script>
@endsection
