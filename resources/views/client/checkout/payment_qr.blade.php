@extends('client.layouts.app')

@section('content')
<div class="bg-gray-50 py-12">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="md:flex">
                <!-- Left: QR Code -->
                <div class="md:w-1/2 p-8 bg-gradient-to-br from-red-50 to-white flex flex-col items-center justify-center border-r">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2 text-center">Thanh Toán Đơn Hàng</h2>
                    <p class="text-gray-500 mb-6 text-center text-sm">Quét mã QR qua App Ngân Hàng hoặc MoMo</p>
                    
                    <div class="bg-white p-4 rounded-xl shadow-md border mb-4 relative" id="qr-container">
                        <img src="{{ $qrUrl }}" alt="QR Code" class="w-64 h-64 object-contain">
                    </div>
                    
                    <div id="payment-spinner" class="flex flex-col items-center justify-center mb-6 h-16">
                        <svg class="animate-spin h-8 w-8 text-red-600 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm font-semibold text-gray-700 animate-pulse">Đang chờ thanh toán...</span>
                    </div>

                    <div class="flex items-center gap-2 text-sm text-gray-600 bg-blue-50 text-blue-800 px-4 py-2 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Hệ thống tự động nhận biết khi thanh toán thành công</span>
                    </div>
                </div>

                <!-- Right: Order Details -->
                <div class="md:w-1/2 p-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Thông tin chuyển khoản</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Ngân hàng</span>
                            <div class="font-semibold text-gray-900">{{ $bankId }}</div>
                        </div>
                        
                        <div class="relative">
                            <span class="block text-sm text-gray-500 mb-1">Số tài khoản</span>
                            <div class="font-bold text-xl text-red-600 tracking-wider flex items-center justify-between">
                                <span id="account-no">{{ $accountNo }}</span>
                                <button onclick="copyText('account-no')" class="text-sm text-gray-500 hover:text-red-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Chủ tài khoản</span>
                            <div class="font-semibold text-gray-900">{{ $accountName }}</div>
                        </div>

                        <div class="relative">
                            <span class="block text-sm text-gray-500 mb-1">Số tiền</span>
                            <div class="font-bold text-2xl text-gray-900 flex items-center justify-between">
                                <span id="amount">{{ number_format($order->total_amount, 0, ',', '.') }}đ</span>
                                <button onclick="copyText('amount')" class="text-sm text-gray-500 hover:text-red-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                </button>
                            </div>
                        </div>

                        <div class="relative p-4 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                            <span class="block text-sm text-gray-500 mb-1">Nội dung chuyển khoản (Bắt buộc)</span>
                            <div class="font-mono font-bold text-xl text-blue-600 flex items-center justify-between">
                                <span id="transfer-content">{{ $order->order_code }}</span>
                                <button onclick="copyText('transfer-content')" class="text-sm text-gray-500 hover:text-blue-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-8 text-center">
                        <a href="{{ route('client.orders.index') }}" class="text-sm text-gray-500 hover:text-gray-900 underline">Quay lại danh sách đơn hàng</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Copy Toast Notification -->
<div id="toast" class="fixed bottom-5 right-5 transform translate-y-full opacity-0 transition-all duration-300 bg-gray-900 text-white px-4 py-2 rounded shadow-lg flex items-center gap-2 z-50">
    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
    <span>Đã sao chép!</span>
</div>

@endsection

@push('scripts')
<script>
    // Copy to clipboard function
    function copyText(elementId) {
        var text = document.getElementById(elementId).innerText.replace(/đ/g, '').replace(/\./g, '');
        navigator.clipboard.writeText(text).then(function() {
            showToast();
        });
    }

    function showToast() {
        const toast = document.getElementById('toast');
        toast.classList.remove('translate-y-full', 'opacity-0');
        setTimeout(() => {
            toast.classList.add('translate-y-full', 'opacity-0');
        }, 2000);
    }

    // AJAX Polling to check payment status
    const orderCode = '{{ $order->order_code }}';
    const checkStatusUrl = '{{ route("client.checkout.check_status", ["order_code" => ":code"]) }}'.replace(':code', orderCode);
    const successUrl = '{{ route("client.checkout.success", ["order_code" => ":code"]) }}'.replace(':code', orderCode);

    function checkPaymentStatus() {
        fetch(checkStatusUrl)
            .then(response => response.json())
            .then(data => {
                if (data.paid) {
                    // Cập nhật giao diện thành công trước khi redirect
                    document.getElementById('payment-spinner').innerHTML = `
                        <svg class="w-16 h-16 text-green-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-green-600 font-bold text-lg">Thanh toán thành công!</span>
                    `;
                    
                    // Chuyển hướng sau 1.5 giây
                    setTimeout(() => {
                        window.location.href = successUrl;
                    }, 1500);
                } else {
                    // Tiếp tục polling sau 2 giây
                    setTimeout(checkPaymentStatus, 2000);
                }
            })
            .catch(error => {
                console.error('Error checking payment status:', error);
                // Thử lại sau 5 giây nếu lỗi mạng
                setTimeout(checkPaymentStatus, 5000);
            });
    }

    // Bắt đầu polling ngay khi trang tải xong
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(checkPaymentStatus, 2000);
    });
</script>
@endpush
