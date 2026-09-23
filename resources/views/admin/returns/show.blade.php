@extends('admin.layouts.app')

@section('title', 'Chi ti?t Yêu c?u ' . ->type_label)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header pb-0 border-b">
                <div class="d-flex justify-content-between align-items-center">
                    <h6>Chi ti?t Yêu c?u {{ ->type_label }} #{{ ->id }}</h6>
                    <span class="badge badge-sm 
                        @if(->status == 'pending') bg-gradient-secondary
                        @elseif(->status == 'processing') bg-gradient-info
                        @elseif(->status == 'resolved') bg-gradient-success
                        @else bg-gradient-danger @endif">
                        {{ ->status_label }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p class="text-sm text-secondary mb-1">Khách hàng</p>
                        <h6 class="mb-0">{{ ->user->name }}</h6>
                        <p class="text-sm mb-0">{{ ->user->email }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-sm text-secondary mb-1">Ðõn hàng liên quan</p>
                        <h6 class="mb-0"><a href="{{ route('admin.orders.show', ->order->id) }}" class="text-primary">#{{ ->order->order_code }}</a></h6>
                        <p class="text-sm mb-0">T?ng ti?n: <span class="text-danger font-weight-bold">{{ number_format(->order->total_amount, 0, ',', '.') }}ð</span></p>
                    </div>
                </div>

                <hr class="horizontal dark">

                <div class="mb-4">
                    <h6 class="text-sm mb-2">L? do yêu c?u:</h6>
                    <p class="text-sm font-weight-bold">{{ ->reason }}</p>
                    
                    <h6 class="text-sm mt-3 mb-2">Mô t? chi ti?t:</h6>
                    <div class="p-3 bg-gray-100 rounded text-sm">
                        {{ ->description }}
                    </div>
                </div>

                @if(->images && is_array(->images))
                    <hr class="horizontal dark">
                    <h6 class="text-sm mb-3">H?nh ?nh b?ng ch?ng:</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach(->images as )
                            <a href="{{ asset('storage/' . ) }}" target="_blank">
                                <img src="{{ asset('storage/' . ) }}" class="rounded" style="width: 120px; height: 120px; object-fit: cover; border: 1px solid #dee2e6;">
                            </a>
                        @endforeach
                    </div>
                @endif
                
                @if(->refund_amount)
                    <hr class="horizontal dark">
                    <h6 class="text-sm mb-3">Thông tin hoàn ti?n:</h6>
                    <div class="p-3 bg-success-soft rounded border border-success">
                        <p class="text-sm mb-0 text-success">Ð? hoàn <strong>{{ number_format(->refund_amount, 0, ',', '.') }}ð</strong> vào Ví MaxBall c?a khách hàng.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header pb-0 border-b">
                <h6>C?p nh?t tr?ng thái</h6>
            </div>
            <div class="card-body">
                @if(in_array(->status, ['resolved', 'rejected']))
                    <div class="alert alert-secondary text-white text-sm" role="alert">
                        Yêu c?u này ð? ðý?c x? l? xong.
                    </div>
                    @if(->admin_note)
                        <h6 class="text-sm mt-3 mb-2">Ghi chú c?a Admin:</h6>
                        <div class="p-3 bg-gray-100 rounded text-sm mb-0">
                            {{ ->admin_note }}
                        </div>
                    @endif
                @else
                    <form action="{{ route('admin.returns.updateStatus', ->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        <div class="mb-3">
                            <label class="form-control-label">Tr?ng thái m?i <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" id="status-select" required>
                                <option value="processing" {{ ->status == 'processing' ? 'selected' : '' }}>Ðang x? l?</option>
                                <option value="resolved">Ð? gi?i quy?t (Ð?ng ?)</option>
                                <option value="rejected">T? ch?i</option>
                            </select>
                        </div>
                        
                        <div class="mb-3 d-none" id="refund-amount-group">
                            <label class="form-control-label">S? ti?n hoàn (VND) <span class="text-danger">*</span></label>
                            <input type="number" name="refund_amount" class="form-control" placeholder="Nh?p s? ti?n..." min="0" max="{{ ->order->total_amount }}" value="{{ ->order->total_amount }}">
                            <small class="text-muted text-xs">T?i ða: {{ number_format(->order->total_amount, 0, ',', '.') }}ð</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-control-label">Ghi chú ph?n h?i khách hàng</label>
                            <textarea name="admin_note" class="form-control" rows="3" placeholder="Nh?p ghi chú..."></textarea>
                        </div>

                        <button type="submit" class="btn bg-gradient-primary w-100 mb-0">C?p nh?t yêu c?u</button>
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
