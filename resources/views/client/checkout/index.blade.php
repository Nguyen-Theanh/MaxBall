@extends('client.layouts.app')

@section('title', 'Thanh Toán - MaxBall')

@section('content')
<section class="bg-[#10271d] pt-32 pb-12">
    <div class="max-w-7xl mx-auto px-4">
        <h1 class="text-3xl font-black text-white">Thanh toán đơn hàng</h1>
    </div>
</section>

<div class="max-w-7xl mx-auto px-4 py-10">

    <form action="{{ route('client.checkout.store') }}" method="POST">
        @csrf
        <div class="flex flex-col lg:flex-row gap-8">
            <div class="lg:w-2/3">
                <div class="bg-white rounded-xl shadow p-6 mb-6">
                    <h2 class="text-xl font-bold mb-6 border-b pb-2 flex justify-between items-center">
                        <span class="flex items-center gap-2 text-red-600"><i class="fa-solid fa-location-dot"></i> Địa chỉ nhận hàng</span>
                        @if($addresses->count() > 0)
                            <button type="button" onclick="openSelectAddressModal()" class="text-sm font-semibold text-blue-600 hover:text-blue-800">Thay đổi</button>
                        @endif
                    </h2>
                    
                    @if($addresses->count() == 0)
                        <div class="text-center py-6">
                            <p class="text-gray-500 mb-4">Bạn chưa có địa chỉ giao hàng nào.</p>
                            <button type="button" onclick="openNewAddressModal()" class="px-6 py-2 bg-red-600 text-white rounded-lg font-bold hover:bg-red-700 shadow-lg shadow-red-200">+ Thêm địa chỉ mới</button>
                        </div>
                        <input type="hidden" name="user_address_id" value="">
                    @else
                        <div id="selected-address-display">
                            <div class="flex flex-col sm:flex-row sm:items-start gap-2 sm:gap-4">
                                <div class="font-bold text-gray-900 whitespace-nowrap">{{ $defaultAddress->receiver_name }} <span class="text-gray-400 font-normal mx-1">|</span> {{ $defaultAddress->receiver_phone }}</div>
                                <div class="text-gray-700 flex-1">{{ $defaultAddress->address_detail }}</div>
                                @if($defaultAddress->is_default)
                                    <div class="shrink-0"><span class="border border-red-500 text-red-500 text-xs px-2 py-0.5 rounded whitespace-nowrap">Mặc định</span></div>
                                @endif
                            </div>
                        </div>
                        <input type="hidden" name="user_address_id" id="user_address_id" value="{{ $defaultAddress->id }}">
                    @endif

                    @error('user_address_id')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-xl font-bold mb-6 border-b pb-2">Phương thức thanh toán</h2>
                    
                    <div class="space-y-4">
                        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                            <input type="radio" name="payment_method" value="cod" class="w-5 h-5 text-red-600 focus:ring-red-600" checked>
                            <div class="ml-3">
                                <span class="block text-sm font-bold text-gray-900">Thanh toán khi nhận hàng (COD)</span>
                                <span class="block text-xs text-gray-500 mt-1">Khách hàng thanh toán bằng tiền mặt cho nhân viên giao hàng.</span>
                            </div>
                        </label>
                        
                        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                            <input type="radio" name="payment_method" value="vnpay" class="w-5 h-5 text-red-600 focus:ring-red-600">
                            <div class="ml-3 flex items-center gap-2">
                                <span class="block text-sm font-bold text-gray-900">Thanh toán qua VNPAY</span>
                                <img src="https://vnpay.vn/s1/statics.vnpay.vn/2023/9/06ncktiwd6dc1694418196384.png" alt="VNPAY" class="h-6 object-contain">
                            </div>
                        </label>
                        @error('payment_method')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="lg:w-1/3">
                <div class="bg-white rounded-xl shadow p-6 sticky top-24">
                    <h2 class="text-xl font-bold mb-4 border-b pb-2">Đơn hàng của bạn</h2>
                    
                    <div class="space-y-4 mb-6">
                        @php $subTotal = 0; @endphp
                        @foreach($cart->items as $item)
                            @php
                                $price = $item->productVariant->discount_price ?: $item->productVariant->base_price;
                                $itemTotal = $price * $item->quantity;
                                $subTotal += $itemTotal;
                            @endphp
                            <div class="flex justify-between items-start text-sm">
                                <div class="flex gap-2 w-3/4">
                                    <span class="font-medium text-gray-900 flex-1">{{ $item->productVariant->product->name }}</span>
                                    <span class="text-gray-500">({{ $item->productVariant->name }})</span>
                                    <span class="font-bold">x{{ $item->quantity }}</span>
                                </div>
                                <span class="font-bold text-gray-900">{{ number_format($itemTotal, 0, ',', '.') }}đ</span>
                            </div>
                        @endforeach
                    </div>
                    
                    @php
                        $shippingFee = 30000;
                        $total = $subTotal + $shippingFee;
                    @endphp
                    
                    <div class="flex justify-between mb-3 text-gray-600 text-sm">
                        <span>Tạm tính</span>
                        <span class="font-bold text-gray-900">{{ number_format($subTotal, 0, ',', '.') }}đ</span>
                    </div>
                    
                    <div class="flex justify-between mb-6 text-gray-600 text-sm pb-4 border-b">
                        <span>Phí giao hàng</span>
                        <span class="font-bold text-gray-900">{{ number_format($shippingFee, 0, ',', '.') }}đ</span>
                    </div>
                    
                    <div class="flex justify-between mb-6">
                        <span class="font-bold text-lg">Tổng cộng</span>
                        <span class="font-black text-2xl text-red-600">{{ number_format($total, 0, ',', '.') }}đ</span>
                    </div>
                    
                    <button type="submit" class="w-full text-center bg-red-600 text-white py-4 rounded-xl font-bold hover:bg-red-700 transition-colors shadow-lg shadow-red-200">
                        Đặt Hàng
                    </button>
                    
                    <a href="{{ route('client.cart.index') }}" class="block text-center mt-4 text-sm text-gray-500 hover:text-red-600">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Quay lại giỏ hàng
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal Chọn Địa Chỉ -->
<div id="selectAddressModal" class="fixed inset-0 z-[100] hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeSelectAddressModal()"></div>
    <div class="relative z-10 w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl transition-all">
        <div class="mb-5 flex items-center justify-between border-b pb-4">
            <h3 class="text-xl font-black text-gray-900">Địa chỉ của tôi</h3>
            <button type="button" onclick="closeSelectAddressModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        
        <div class="max-h-[50vh] overflow-y-auto space-y-4 mb-4 pr-2" id="address-list">
            @foreach($addresses as $address)
                <label class="flex items-start gap-4 p-4 border rounded-xl cursor-pointer transition-colors {{ $address->id == ($defaultAddress->id ?? 0) ? 'border-red-500 bg-red-50/50' : 'hover:bg-gray-50' }}" onclick="selectAddressUI(this)">
                    <input type="radio" name="modal_selected_address" value="{{ $address->id }}" class="mt-1 w-4 h-4 text-red-600 focus:ring-red-600" {{ $address->id == ($defaultAddress->id ?? 0) ? 'checked' : '' }}>
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-1">
                            <span class="font-bold text-gray-900" id="addr-name-{{ $address->id }}">{{ $address->receiver_name }}</span>
                            <span class="text-gray-400">|</span>
                            <span class="text-gray-600" id="addr-phone-{{ $address->id }}">{{ $address->receiver_phone }}</span>
                        </div>
                        <p class="text-sm text-gray-600" id="addr-detail-{{ $address->id }}">{{ $address->address_detail }}</p>
                        @if($address->is_default)
                            <span class="inline-block mt-2 border border-red-500 text-red-500 text-[10px] uppercase font-bold px-2 py-0.5 rounded" id="addr-default-{{ $address->id }}">Mặc định</span>
                        @endif
                    </div>
                </label>
            @endforeach
        </div>
        
        <button type="button" onclick="openNewAddressModal()" class="w-full py-3 border border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-50 mb-4 transition-colors">
            <i class="fa-solid fa-plus mr-2"></i> Thêm Địa Chỉ Mới
        </button>

        <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" onclick="closeSelectAddressModal()" class="px-6 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-bold hover:bg-gray-50 transition-colors">Hủy</button>
            <button type="button" onclick="confirmAddressSelection()" class="px-6 py-2.5 rounded-xl bg-[#d92525] text-white font-bold hover:bg-red-700 transition-colors shadow-lg shadow-red-500/30">Xác nhận</button>
        </div>
    </div>
</div>

<!-- Modal Thêm Địa Chỉ Mới -->
<div id="newAddressModal" class="fixed inset-0 z-[110] hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeNewAddressModal()"></div>
    <div class="relative z-10 w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl transition-all">
        <div class="mb-5 flex items-center justify-between border-b pb-4">
            <h3 class="text-xl font-black text-[#10271d]">Địa chỉ mới</h3>
            <button type="button" onclick="closeNewAddressModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('account.addresses.store') }}">
            @csrf
            <div class="space-y-5">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-2 block text-sm font-bold text-gray-700">Họ và tên</label>
                        <input type="text" name="receiver_name" class="w-full rounded-xl border border-gray-200 px-4 py-3 outline-none focus:border-[#10271d] focus:ring-4 focus:ring-[#10271d]/10" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold text-gray-700">Số điện thoại</label>
                        <input type="text" name="receiver_phone" class="w-full rounded-xl border border-gray-200 px-4 py-3 outline-none focus:border-[#10271d] focus:ring-4 focus:ring-[#10271d]/10" required>
                    </div>
                </div>
                
                <div>
                    <label class="mb-2 block text-sm font-bold text-gray-700">Địa chỉ cụ thể (Tỉnh/Thành phố, Quận/Huyện, Phường/Xã, Số nhà...)</label>
                    <textarea name="address_detail" rows="3" class="w-full rounded-xl border border-gray-200 px-4 py-3 outline-none focus:border-[#10271d] focus:ring-4 focus:ring-[#10271d]/10" required></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="checkout_is_default" name="is_default" class="h-4 w-4 rounded border-gray-300 text-[#d92525] focus:ring-[#d92525]">
                    <label for="checkout_is_default" class="text-sm text-gray-700">Đặt làm địa chỉ mặc định</label>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <button type="button" onclick="closeNewAddressModal()" class="rounded-xl border border-gray-300 px-5 py-2.5 font-bold text-gray-700 transition hover:bg-gray-50">
                    Trở lại
                </button>
                <button type="submit" class="rounded-xl bg-[#d92525] px-5 py-2.5 font-bold text-white transition hover:bg-red-700 shadow-lg shadow-red-500/30">
                    Hoàn thành
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openSelectAddressModal() {
        const modal = document.getElementById('selectAddressModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeSelectAddressModal() {
        const modal = document.getElementById('selectAddressModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openNewAddressModal() {
        closeSelectAddressModal();
        const modal = document.getElementById('newAddressModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeNewAddressModal() {
        const modal = document.getElementById('newAddressModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function selectAddressUI(labelElement) {
        // Remove border/bg from all
        document.querySelectorAll('#address-list label').forEach(el => {
            el.classList.remove('border-red-500', 'bg-red-50/50');
            el.classList.add('hover:bg-gray-50');
        });
        
        // Add to selected
        labelElement.classList.add('border-red-500', 'bg-red-50/50');
        labelElement.classList.remove('hover:bg-gray-50');
    }

    function confirmAddressSelection() {
        const selectedRadio = document.querySelector('input[name="modal_selected_address"]:checked');
        if (!selectedRadio) return;

        const id = selectedRadio.value;
        const name = document.getElementById(`addr-name-${id}`).textContent;
        const phone = document.getElementById(`addr-phone-${id}`).textContent;
        const detail = document.getElementById(`addr-detail-${id}`).textContent;
        const defaultBadge = document.getElementById(`addr-default-${id}`);

        // Update hidden input
        document.getElementById('user_address_id').value = id;

        // Update display UI
        let defaultBadgeHtml = '';
        if (defaultBadge) {
            defaultBadgeHtml = `<div class="shrink-0"><span class="border border-red-500 text-red-500 text-xs px-2 py-0.5 rounded whitespace-nowrap">Mặc định</span></div>`;
        }

        const displayHtml = `
            <div class="flex flex-col sm:flex-row sm:items-start gap-2 sm:gap-4">
                <div class="font-bold text-gray-900 whitespace-nowrap">${name} <span class="text-gray-400 font-normal mx-1">|</span> ${phone}</div>
                <div class="text-gray-700 flex-1">${detail}</div>
                ${defaultBadgeHtml}
            </div>
        `;

        document.getElementById('selected-address-display').innerHTML = displayHtml;
        closeSelectAddressModal();
    }
</script>
@endpush
