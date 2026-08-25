@extends('client.layouts.app')

@section('title', 'Chi Tiết Đơn Hàng #' . $order->order_code . ' - MaxBall')

@section('content')
<section class="bg-[#10271d] pt-32 pb-12">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-black text-white">Chi tiết đơn hàng #{{ $order->order_code }}</h1>
            <a href="{{ route('client.orders.index') }}" class="text-sm font-bold text-gray-300 hover:text-white">
                <i class="fa-solid fa-arrow-left mr-1"></i> Trở về danh sách
            </a>
        </div>
    </div>
</section>

<div class="max-w-7xl mx-auto px-4 py-10">

    <div class="flex flex-col lg:flex-row gap-8">
        <div class="lg:w-2/3">
            <div class="bg-white rounded-xl shadow overflow-hidden mb-6">
                <div class="bg-gray-50 px-6 py-4 border-b">
                    <h2 class="font-bold text-lg">Danh sách sản phẩm</h2>
                </div>
                <div class="p-6">
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
                        <div class="flex gap-4 {{ !$loop->last ? 'mb-4 pb-4 border-b border-gray-100' : '' }}">
                            <img src="{{ $thumbnail }}" alt="{{ $product->name }}" class="w-20 h-24 object-cover rounded border">
                            <div class="flex-1">
                                <a href="{{ route('client.products.show', $product->slug) }}" class="font-bold text-gray-900 hover:text-red-600 line-clamp-1">
                                    {{ $product->name }}
                                </a>
                                <p class="text-sm text-gray-500 mt-1">Phân loại: {{ $detail->variant->name }}</p>
                                <p class="text-sm text-gray-500 mt-1">Đơn giá: {{ number_format($detail->price, 0, ',', '.') }}đ</p>
                                @include('client.orders._review_status', ['order' => $order, 'detail' => $detail])
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500 mb-1">Số lượng: {{ $detail->quantity }}</p>
                                <p class="font-bold text-red-600">
                                    {{ number_format($detail->price * $detail->quantity, 0, ',', '.') }}đ
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b">
                    <h2 class="font-bold text-lg">Thông tin thanh toán</h2>
                </div>
                <div class="p-6 space-y-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tạm tính</span>
                        <span class="font-medium text-gray-900">{{ number_format($order->sub_total, 0, ',', '.') }}đ</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Phí giao hàng</span>
                        <span class="font-medium text-gray-900">{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</span>
                    </div>
                    @if($order->discount_amount > 0)
                        <div class="flex justify-between text-green-600">
                            <span>Giảm giá</span>
                            <span class="font-medium">-{{ number_format($order->discount_amount, 0, ',', '.') }}đ</span>
                        </div>
                    @endif
                    <div class="flex justify-between pt-4 border-t">
                        <span class="font-bold text-lg">Tổng cộng</span>
                        <span class="font-black text-2xl text-red-600">{{ number_format($order->total_amount, 0, ',', '.') }}đ</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:w-1/3 space-y-6">
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b">
                    <h2 class="font-bold text-lg">Trạng thái đơn hàng</h2>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <p class="text-xs text-gray-500 uppercase font-bold mb-1">Trạng thái giao hàng</p>
                        <span class="inline-block px-3 py-1 text-sm font-bold uppercase rounded-full 
                            @if($order->order_status == 'pending') bg-yellow-100 text-yellow-800
                            @elseif(in_array($order->order_status, ['confirmed', 'processing'])) bg-blue-100 text-blue-800
                            @elseif($order->order_status == 'shipping') bg-indigo-100 text-indigo-800
                            @elseif($order->order_status == 'completed') bg-green-100 text-green-800
                            @elseif($order->order_status == 'cancelled') bg-red-100 text-red-800
                            @endif
                        ">
                            @if($order->order_status == 'pending') Chờ xác nhận
                            @elseif(in_array($order->order_status, ['confirmed', 'processing'])) Đã xác nhận
                            @elseif($order->order_status == 'shipping') Đang giao hàng
                            @elseif($order->order_status == 'completed') Hoàn thành
                            @elseif($order->order_status == 'cancelled') Đã hủy
                            @endif
                        </span>
                        @if($order->hasActiveReservation() && $order->reservation_expires_at)
                            <p class="mt-2 text-sm text-amber-700">
                                Hàng đang được giữ đến {{ $order->reservation_expires_at->format('H:i d/m/Y') }} để cửa hàng xác nhận.
                            </p>
                        @endif
                    </div>
                    
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold mb-1">Trạng thái thanh toán</p>
                        <span class="inline-block px-3 py-1 text-sm font-bold uppercase rounded-full
                            {{ $order->payment_status == 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}
                        ">
                            {{ $order->payment_status == 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                        </span>
                        <p class="text-sm mt-2 font-medium text-gray-600">
                            Phương thức: {{ strtoupper($order->payment_method) }}
                        </p>
                    </div>
                </div>
            </div>

            @if($order->order_status === 'cancelled' && $order->cancellation_reason)
                <div class="rounded-xl border border-red-100 bg-red-50 p-5 text-sm text-red-900">
                    <p class="font-bold">Thông tin hủy đơn</p>
                    <p class="mt-2"><span class="font-medium">Người hủy:</span> {{ match($order->cancelled_by) { 'admin' => 'Cửa hàng', 'system' => 'Hệ thống', default => 'Khách hàng' } }}</p>
                    <p class="mt-1"><span class="font-medium">Lý do:</span> {{ $order->cancellation_reason_label }}</p>
                    @if($order->cancellation_note)
                        <p class="mt-1 whitespace-pre-line"><span class="font-medium">Ghi chú:</span> {{ $order->cancellation_note }}</p>
                    @endif
                    @if($order->cancelled_at)
                        <p class="mt-1"><span class="font-medium">Thời gian:</span> {{ $order->cancelled_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
            @endif

            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b">
                    <h2 class="font-bold text-lg">Thông tin nhận hàng</h2>
                </div>
                <div class="p-6 text-sm text-gray-700 space-y-3">
                    <p><span class="text-gray-500 w-24 inline-block">Họ Tên:</span> <span class="font-bold">{{ $order->customer_name }}</span></p>
                    <p><span class="text-gray-500 w-24 inline-block">SĐT:</span> <span class="font-bold">{{ $order->customer_phone }}</span></p>
                    <p><span class="text-gray-500 w-24 inline-block align-top">Địa chỉ:</span> <span class="font-bold flex-1">{{ $order->customer_address }}</span></p>
                </div>
            </div>
            
@if(in_array($order->order_status, ['pending', 'confirmed', 'processing']))
                <div class="flex gap-3">
                    @if($order->payment_method === 'vietqr' && $order->payment_status === 'pending')
                        <a href="{{ route('client.checkout.payment_qr', $order->order_code) }}" class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition-colors shadow-sm text-center">
                            Thanh toán lại
                        </a>
                    @endif
                    <button type="button"
                            data-customer-cancel
                            data-order-id="{{ $order->id }}"
                            data-order-code="{{ $order->order_code }}"
                            data-cancel-action="{{ route('client.orders.cancel', $order->id) }}"
                            class="w-full sm:w-auto px-6 py-2.5 border-2 border-red-500 text-red-500 font-bold rounded-lg hover:bg-red-50 transition-colors">
                        Hủy đơn hàng
                    </button>
                </div>
            @elseif($order->order_status == 'shipping')
                <form action="{{ route('client.orders.confirmReceipt', $order->id) }}" method="POST"
                      data-confirm="Xác nhận bạn đã nhận được hàng và thanh toán đủ tiền?"
                      data-confirm-title="Xác nhận đã nhận hàng"
                      data-confirm-label="Đã nhận hàng"
                      data-confirm-variant="success">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 transition-colors shadow-sm">
                        Đã nhận được hàng
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

@include('client.orders._cancel_modal')
@include('client.orders._review_modal')
@endsection
