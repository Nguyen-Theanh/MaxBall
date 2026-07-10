@extends('client.layouts.app')

@section('title', 'Lịch Sử Đơn Hàng - MaxBall')

@section('content')
<section class="bg-[#10271d] pt-32 pb-12">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-black text-white">Lịch sử đơn hàng</h1>
            <a href="{{ route('account.show') }}" class="text-sm font-bold text-gray-300 hover:text-white">
                <i class="fa-solid fa-arrow-left mr-1"></i> Quay lại tài khoản
            </a>
        </div>
    </div>
</section>

<div class="max-w-7xl mx-auto px-4 py-10">

    @if($orders->count() > 0)
        <div class="space-y-6">
            @foreach($orders as $order)
                <div class="bg-white rounded-xl shadow overflow-hidden border border-gray-100">
                    <div class="flex flex-wrap items-center justify-between bg-gray-50 px-6 py-4 border-b">
                        <div class="flex items-center gap-6">
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-bold mb-1">Mã đơn hàng</p>
                                <p class="font-bold text-gray-900">#{{ $order->order_code }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-bold mb-1">Ngày đặt</p>
                                <p class="font-bold text-gray-900">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-bold mb-1">Tổng tiền</p>
                                <p class="font-black text-red-600">{{ number_format($order->total_amount, 0, ',', '.') }}đ</p>
                            </div>
                        </div>
                        
                        <div class="mt-4 sm:mt-0 text-right">
                            <span class="inline-block px-3 py-1 text-xs font-bold uppercase rounded-full 
                                @if($order->order_status == 'pending') bg-yellow-100 text-yellow-800
                                @elseif($order->order_status == 'shipping') bg-blue-100 text-blue-800
                                @elseif($order->order_status == 'completed') bg-green-100 text-green-800
                                @elseif($order->order_status == 'cancelled') bg-red-100 text-red-800
                                @endif
                            ">
                                @if($order->order_status == 'pending') Chờ xử lý
                                @elseif($order->order_status == 'shipping') Đang giao hàng
                                @elseif($order->order_status == 'completed') Hoàn thành
                                @elseif($order->order_status == 'cancelled') Đã hủy
                                @endif
                            </span>
                        </div>
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
                                <img src="{{ $thumbnail }}" alt="{{ $product->name }}" class="w-16 h-20 object-cover rounded border">
                                <div class="flex-1">
                                    <a href="{{ route('client.products.show', $product->slug) }}" class="font-bold text-gray-900 hover:text-red-600 line-clamp-1">
                                        {{ $product->name }}
                                    </a>
                                    <p class="text-sm text-gray-500 mt-1">Phân loại: {{ $detail->variant->name }}</p>
                                    <p class="text-sm text-gray-500 mt-1">Số lượng: {{ $detail->quantity }}</p>
                                </div>
                                <div class="font-bold text-gray-900">
                                    {{ number_format($detail->price, 0, ',', '.') }}đ
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="bg-gray-50 px-6 py-4 border-t flex justify-between items-center">
                        <div class="text-sm">
                            <span class="text-gray-500">Thanh toán:</span>
                            <span class="font-bold {{ $order->payment_status == 'paid' ? 'text-green-600' : 'text-yellow-600' }}">
                                {{ $order->payment_status == 'paid' ? 'Đã thanh toán (' . strtoupper($order->payment_method) . ')' : 'Chưa thanh toán (COD)' }}
                            </span>
                        </div>
                        
                        <div class="flex gap-2">
                            <a href="{{ route('client.orders.show', $order->id) }}" class="px-4 py-2 bg-gray-900 text-white text-sm font-bold rounded hover:bg-black transition-colors">
                                Xem chi tiết
                            </a>
                            
                            @if($order->order_status == 'pending')
                                <form action="{{ route('client.orders.cancel', $order->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?');">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="px-4 py-2 border border-red-600 text-red-600 text-sm font-bold rounded hover:bg-red-50 transition-colors">
                                        Hủy đơn
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-xl shadow p-10 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-24 h-24 mx-auto text-gray-300 mb-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            <h2 class="text-2xl font-bold mb-2">Chưa có đơn hàng nào</h2>
            <p class="text-gray-500 mb-6">Bạn chưa thực hiện đơn đặt hàng nào.</p>
            <a href="{{ route('client.products.index') }}" class="inline-block bg-red-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-red-700">
                Mua sắm ngay
            </a>
        </div>
    @endif
</div>
@endsection
