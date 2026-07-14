@extends('client.layouts.app')

@section('content')
<div class="bg-gray-50 py-16 min-h-[70vh] flex items-center justify-center">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-2xl text-center">
        <div class="bg-white rounded-3xl shadow-xl p-10 transform transition-all hover:scale-105 duration-300">
            <!-- Success Animation Circle -->
            <div class="mx-auto w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mb-6 animate-bounce">
                <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Thanh Toán Thành Công!</h1>
            <p class="text-gray-500 text-lg mb-8">Cảm ơn bạn đã mua sắm. Đơn hàng <span class="font-bold text-gray-900">#{{ $order->order_code }}</span> của bạn đã được xác nhận.</p>
            
            <div class="bg-gray-50 rounded-xl p-6 mb-8 text-left border border-gray-100">
                <div class="flex justify-between items-center mb-3">
                    <span class="text-gray-500">Mã đơn hàng:</span>
                    <span class="font-bold text-gray-900">#{{ $order->order_code }}</span>
                </div>
                <div class="flex justify-between items-center mb-3">
                    <span class="text-gray-500">Ngày đặt:</span>
                    <span class="font-medium text-gray-900">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between items-center mb-3">
                    <span class="text-gray-500">Thanh toán:</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Đã thanh toán (VietQR)
                    </span>
                </div>
                <div class="flex justify-between items-center pt-3 border-t mt-3 border-gray-200">
                    <span class="text-gray-700 font-bold">Tổng tiền:</span>
                    <span class="font-black text-xl text-red-600">{{ number_format($order->total_amount, 0, ',', '.') }}đ</span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ route('client.orders.index') }}" class="inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-full text-white bg-red-600 hover:bg-red-700 transition-colors shadow-lg hover:shadow-xl">
                    Xem Đơn Hàng
                </a>
                <a href="{{ route('client.products.index') }}" class="inline-flex justify-center items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-full text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    Tiếp Tục Mua Sắm
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
