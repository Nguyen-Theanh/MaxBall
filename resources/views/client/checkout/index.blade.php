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
                            <span class="flex items-center gap-4">
                                <button type="button" onclick="editSelectedCheckoutAddress()" class="text-sm font-semibold text-gray-600 hover:text-[#d92525]">Chỉnh sửa</button>
                                <button type="button" onclick="openSelectAddressModal()" class="text-sm font-semibold text-blue-600 hover:text-blue-800">Thay đổi</button>
                            </span>
                        @endif
                    </h2>
                    
                    @if($addresses->count() == 0)
                        <div class="text-center py-6">
                            <p class="text-gray-500 mb-4">Bạn chưa có địa chỉ giao hàng nào.</p>
                            <button type="button" onclick="openCheckoutAddressModal()" class="px-6 py-2 bg-red-600 text-white rounded-lg font-bold hover:bg-red-700 shadow-lg shadow-red-200">+ Thêm địa chỉ mới</button>
                        </div>
                        <input type="hidden" name="user_address_id" value="">
                    @else
                        <div id="selected-address-display">
                            <div class="flex flex-col sm:flex-row sm:items-start gap-2 sm:gap-4">
                                <div class="font-bold text-gray-900 whitespace-nowrap">{{ $selectedAddress->receiver_name }} <span class="text-gray-400 font-normal mx-1">|</span> {{ $selectedAddress->receiver_phone }}</div>
                                <div class="text-sm text-gray-500">{{ $selectedAddress->receiver_email ?? Auth::user()->email }}</div>
                                <div class="text-gray-700 flex-1">{{ $selectedAddress->address_detail }}</div>
                                @if($selectedAddress->is_default)
                                    <div class="shrink-0"><span class="border border-red-500 text-red-500 text-xs px-2 py-0.5 rounded whitespace-nowrap">Mặc định</span></div>
                                @endif
                            </div>
                        </div>
                        <input type="hidden" name="user_address_id" id="user_address_id" value="{{ $selectedAddress->id }}">
                    @endif

                    @error('user_address_id')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-xl font-bold mb-6 border-b pb-2">Phương thức thanh toán</h2>
                    
                    @php
                        $subTotal = 0;
                        foreach ($cart->items as $item) {
                            $price = $item->productVariant->discount_price ?: $item->productVariant->base_price;
                            $subTotal += $price * $item->quantity;
                        }
                        $shippingFee = $subTotal >= 500000 ? 0 : 30000;
                        $total = $subTotal + $shippingFee;
                        $walletBalance = Auth::user()->wallet_balance ?? 0;
                        $canPayWithWallet = $walletBalance >= $total;
                    @endphp

                    <div class="space-y-4">
                        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                            <input type="radio" name="payment_method" value="cod" class="w-5 h-5 text-red-600 focus:ring-red-600" checked>
                            <div class="ml-3">
                                <span class="block text-sm font-bold text-gray-900">Thanh toán khi nhận hàng (COD)</span>
                                <span class="block text-xs text-gray-500 mt-1">Hàng được giữ tối đa 24 giờ để cửa hàng xác nhận; nếu bị từ chối hoặc quá hạn, đơn sẽ tự hủy và hàng được trả lại.</span>
                            </div>
                        </label>
                        
                        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                            <input type="radio" name="payment_method" value="vietqr" class="w-5 h-5 text-red-600 focus:ring-red-600">
                            <div class="ml-3 flex items-center gap-2">
                                <span class="block text-sm font-bold text-gray-900">Chuyển khoản (VietQR / App Ngân Hàng / MoMo)</span>
                            </div>
                        </label>

                        <label class="flex items-center p-4 border rounded-lg {{ $canPayWithWallet ? 'cursor-pointer hover:bg-gray-50' : 'opacity-60 cursor-not-allowed bg-gray-50' }} transition-colors">
                            <input type="radio" name="payment_method" value="wallet" class="w-5 h-5 text-red-600 focus:ring-red-600" {{ !$canPayWithWallet ? 'disabled' : '' }}>
                            <div class="ml-3">
                                <span class="block text-sm font-bold text-gray-900">Thanh toán bằng Ví MaxBall</span>
                                <span class="block text-xs text-gray-500 mt-1">Số dư hiện tại: <strong class="{{ $canPayWithWallet ? 'text-green-600' : 'text-red-500' }}">{{ number_format($walletBalance, 0, ',', '.') }}đ</strong></span>
                                @if(!$canPayWithWallet)
                                    <span class="block text-xs text-red-500 mt-1">Số dư không đủ để thanh toán đơn hàng này.</span>
                                @endif
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
                        $shippingFee = $subTotal >= 500000 ? 0 : 30000;
                        $total = $subTotal + $shippingFee;
                    @endphp
                    
                    <!-- Voucher Section -->
                    <div class="mb-6 border-t border-gray-100 pt-6">
                        <div class="flex items-center justify-between mb-3">
                            <span class="font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                                MaxBall Voucher
                            </span>
                            <button type="button" onclick="openCheckoutVoucherModal()" class="text-sm font-bold text-blue-600 hover:text-blue-700">Chọn Voucher</button>
                        </div>
                        
                        <!-- Applied Vouchers Container -->
                        <div id="appliedVouchersContainer" class="flex flex-col gap-2 mb-3">
                            <!-- JS will populate tags here -->
                        </div>

                        <div class="flex gap-2 relative">
                            <input type="text" id="voucherCodeInput" placeholder="Nhập mã giảm giá" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none uppercase">
                            <button type="button" onclick="applyVoucher()" id="applyVoucherBtn" class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-900 transition-colors shrink-0">Áp dụng</button>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Có thể dùng đồng thời một mã freeship và một mã giảm giá sản phẩm.</p>
                        <div id="voucherMessage" class="mt-2 text-sm hidden"></div>
                        
                        <!-- Hidden inputs for form submission -->
                        <input type="hidden" name="coupon_code" id="appliedDiscountCode" value="">
                        <input type="hidden" name="freeship_coupon_code" id="appliedFreeshipCode" value="">
                    </div>

                    <div class="flex justify-between mb-3 text-gray-600 text-sm">
                        <span>Tạm tính</span>
                        <span class="font-bold text-gray-900">{{ number_format($subTotal, 0, ',', '.') }}đ</span>
                    </div>

                    <div id="discountRow" class="flex justify-between mb-3 text-sm hidden">
                        <span class="text-gray-600">Giảm giá voucher</span>
                        <span class="font-bold text-red-600" id="discountAmountDisplay">-0đ</span>
                    </div>
                    
                    <div class="flex justify-between mb-6 text-gray-600 text-sm pb-4 border-b">
                        <span class="flex items-center gap-1">Phí giao hàng</span>
                        <span class="font-bold text-gray-900" id="shippingFeeDisplay" data-base-shipping="{{ $shippingFee }}">{{ $shippingFee == 0 ? 'Miễn phí' : number_format($shippingFee, 0, ',', '.') . 'đ' }}</span>
                    </div>
                    
                    <div class="flex justify-between mb-6">
                        <span class="font-bold text-lg">Tổng cộng</span>
                        <span class="font-black text-2xl text-red-600" id="totalAmountDisplay" data-base-total="{{ $total }}">{{ number_format($total, 0, ',', '.') }}đ</span>
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
                <label class="flex items-start gap-4 p-4 border rounded-xl cursor-pointer transition-colors {{ $address->id == ($selectedAddress->id ?? 0) ? 'border-red-500 bg-red-50/50' : 'hover:bg-gray-50' }}" onclick="selectAddressUI(this)">
                    <input type="radio" name="modal_selected_address" value="{{ $address->id }}" class="mt-1 w-4 h-4 text-red-600 focus:ring-red-600" {{ $address->id == ($selectedAddress->id ?? 0) ? 'checked' : '' }}>
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-1">
                            <span class="font-bold text-gray-900" id="addr-name-{{ $address->id }}">{{ $address->receiver_name }}</span>
                            <span class="text-gray-400">|</span>
                            <span class="text-gray-600" id="addr-phone-{{ $address->id }}">{{ $address->receiver_phone }}</span>
                        </div>
                        <p class="text-sm text-gray-500" id="addr-email-{{ $address->id }}">{{ $address->receiver_email ?? Auth::user()->email }}</p>
                        <p class="text-sm text-gray-600" id="addr-detail-{{ $address->id }}">{{ $address->address_detail }}</p>
                        @if($address->is_default)
                            <span class="inline-block mt-2 border border-red-500 text-red-500 text-[10px] uppercase font-bold px-2 py-0.5 rounded" id="addr-default-{{ $address->id }}">Mặc định</span>
                        @endif
                    </div>
                </label>
            @endforeach
        </div>
        
        <button type="button" onclick="openCheckoutAddressModal()" class="w-full py-3 border border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-50 mb-4 transition-colors">
            <i class="fa-solid fa-plus mr-2"></i> Thêm Địa Chỉ Mới
        </button>

        <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" onclick="closeSelectAddressModal()" class="px-6 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-bold hover:bg-gray-50 transition-colors">Hủy</button>
            <button type="button" onclick="confirmAddressSelection()" class="px-6 py-2.5 rounded-xl bg-[#d92525] text-white font-bold hover:bg-red-700 transition-colors shadow-lg shadow-red-500/30">Xác nhận</button>
        </div>
    </div>
</div>

<!-- Modal thêm/sửa địa chỉ -->
<div id="newAddressModal" class="fixed inset-0 z-[110] hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeNewAddressModal()"></div>
    <div class="relative z-10 max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl transition-all">
        <div class="mb-5 flex items-center justify-between border-b pb-4">
            <h3 id="checkoutAddressModalTitle" class="text-xl font-black text-[#10271d]">Địa chỉ mới</h3>
            <button type="button" onclick="closeNewAddressModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <form id="checkoutAddressForm" method="POST" action="{{ route('account.addresses.store') }}">
            @csrf
            <input type="hidden" name="_method" id="checkoutAddressMethod" value="POST">
            <input type="hidden" name="address_id" id="checkout_address_id" value="{{ old('address_id') }}">
            <input type="hidden" name="form_context" value="checkout_address">
            <div class="space-y-5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-bold text-gray-700">Họ và tên</label>
                        <input type="text" id="checkout_receiver_name" name="receiver_name" value="{{ old('receiver_name') }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 outline-none focus:border-[#10271d] focus:ring-4 focus:ring-[#10271d]/10" required>
                        @error('receiver_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold text-gray-700">Số điện thoại</label>
                        <input type="text" id="checkout_receiver_phone" name="receiver_phone" value="{{ old('receiver_phone') }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 outline-none focus:border-[#10271d] focus:ring-4 focus:ring-[#10271d]/10" required>
                        @error('receiver_phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold text-gray-700">Email nhận xác nhận đơn hàng</label>
                    <input type="email" id="checkout_receiver_email" name="receiver_email" value="{{ old('receiver_email', Auth::user()->email) }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 outline-none focus:border-[#10271d] focus:ring-4 focus:ring-[#10271d]/10" required>
                    @error('receiver_email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <x-vietnam-address-fields
                    prefix="checkout-address"
                    input-class="w-full rounded-xl border border-gray-200 px-4 py-3 outline-none focus:border-[#10271d] focus:ring-4 focus:ring-[#10271d]/10"
                    label-class="mb-2 block text-sm font-bold text-gray-700"
                />

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="checkout_is_default" name="is_default" class="h-4 w-4 rounded border-gray-300 text-[#d92525] focus:ring-[#d92525]" @checked(old('is_default'))>
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
@include('client.partials.vietnam-address-script')
<script>
    const checkoutAddresses = @json($addresses->keyBy('id'));
    const checkoutAddressBaseUrl = @json(url('/account/addresses'));

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

    async function openCheckoutAddressModal(address = null, preserveValues = false) {
        closeSelectAddressModal();
        const modal = document.getElementById('newAddressModal');
        const form = document.getElementById('checkoutAddressForm');
        const title = document.getElementById('checkoutAddressModalTitle');
        const methodInput = document.getElementById('checkoutAddressMethod');
        const addressIdInput = document.getElementById('checkout_address_id');
        const addressLineInput = document.getElementById('checkout-address_address_line');
        const selector = window.VietnamAddress?.get('checkout-address');

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        if (address) {
            title.textContent = 'Chỉnh sửa địa chỉ';
            form.action = `${checkoutAddressBaseUrl}/${address.id}`;
            methodInput.value = 'PUT';
            addressIdInput.value = address.id;

            if (!preserveValues) {
                document.getElementById('checkout_receiver_name').value = address.receiver_name;
                document.getElementById('checkout_receiver_phone').value = address.receiver_phone;
                document.getElementById('checkout_receiver_email').value = address.receiver_email || @json(Auth::user()->email);
                addressLineInput.value = address.address_line || address.address_detail;
                document.getElementById('checkout_is_default').checked = address.is_default == 1;
                if (selector) {
                    await selector.setSelection(address.province_code, address.ward_code);
                }
            }
        } else {
            title.textContent = 'Địa chỉ mới';
            form.action = '{{ route('account.addresses.store') }}';
            methodInput.value = 'POST';
            addressIdInput.value = '';

            if (!preserveValues) {
                form.reset();
                addressIdInput.value = '';
                selector?.reset();
            }
        }
    }

    function editSelectedCheckoutAddress() {
        const selectedId = document.getElementById('user_address_id')?.value;
        const address = checkoutAddresses[String(selectedId)];

        if (address) {
            openCheckoutAddressModal(address);
        }
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
        const email = document.getElementById(`addr-email-${id}`).textContent;
        const detail = document.getElementById(`addr-detail-${id}`).textContent;
        const defaultBadge = document.getElementById(`addr-default-${id}`);

        // Update hidden input
        document.getElementById('user_address_id').value = id;

        // Update display UI
        let defaultBadgeHtml = '';
        if (defaultBadge) {
            defaultBadgeHtml = `<div class="shrink-0"><span class="border border-red-500 text-red-500 text-xs px-2 py-0.5 rounded whitespace-nowrap">Mặc định</span></div>`;
        }

        const escapeHtml = (value) => {
            const element = document.createElement('div');
            element.textContent = value;
            return element.innerHTML;
        };

        const displayHtml = `
            <div class="flex flex-col sm:flex-row sm:items-start gap-2 sm:gap-4">
                <div class="font-bold text-gray-900 whitespace-nowrap">${escapeHtml(name)} <span class="text-gray-400 font-normal mx-1">|</span> ${escapeHtml(phone)}</div>
                <div class="text-sm text-gray-500">${escapeHtml(email)}</div>
                <div class="text-gray-700 flex-1">${escapeHtml(detail)}</div>
                ${defaultBadgeHtml}
            </div>
        `;

        document.getElementById('selected-address-display').innerHTML = displayHtml;
        closeSelectAddressModal();
    }

    document.addEventListener('DOMContentLoaded', () => {
        @if(old('form_context') === 'checkout_address' && $errors->hasAny(['receiver_name', 'receiver_phone', 'receiver_email', 'address_line', 'province_code', 'ward_code']))
            const restoredAddressId = @json(old('address_id'));
            const restoredAddress = restoredAddressId
                ? checkoutAddresses[String(restoredAddressId)]
                : null;
            openCheckoutAddressModal(restoredAddress, true);
        @endif

        const checkoutForm = document.querySelector('form[action="{{ route('client.checkout.store') }}"]');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', function(e) {
                const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
                if (selectedPayment && selectedPayment.value === 'wallet') {
                    if (!confirm('Bạn có chắc chắn muốn thanh toán đơn hàng này bằng số dư trong Ví MaxBall không?')) {
                        e.preventDefault();
                    }
                }
            });
        }
    });
    // Calculate base wallet sufficient check
    function updateWalletSufficientCheck(finalTotal) {
        let walletRadio = document.getElementById('payment_wallet');
        if(walletRadio) {
            let userBalance = {{ Auth::user()->wallet_balance }};
            if(userBalance < finalTotal) {
                walletRadio.disabled = true;
                if(walletRadio.checked) {
                    document.getElementById('payment_cod').checked = true;
                }
                let span = walletRadio.parentElement.querySelector('span.text-xs.text-red-500');
                if(!span) {
                    let container = walletRadio.parentElement.querySelector('.flex-col');
                    if(container) {
                        container.innerHTML += `<span class="text-xs text-red-500 block mt-1">(Số dư không đủ)</span>`;
                    }
                }
            } else {
                walletRadio.disabled = false;
                let span = walletRadio.parentElement.querySelector('span.text-xs.text-red-500');
                if(span) span.remove();
            }
        }
    }
</script>

<!-- Checkout Voucher Modal -->
<div id="checkoutVoucherModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeCheckoutVoucherModal()"></div>
    
    <div class="absolute inset-x-0 bottom-0 sm:inset-auto sm:top-1/2 sm:left-1/2 sm:-translate-x-1/2 sm:-translate-y-1/2 bg-[#f6f6f6] sm:rounded-2xl shadow-2xl w-full sm:max-w-lg transition-all transform flex flex-col max-h-[85vh]">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-white sm:rounded-t-2xl shrink-0">
            <h3 class="text-xl font-bold text-gray-900">Voucher của bạn</h3>
            <button onclick="closeCheckoutVoucherModal()" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2 rounded-full transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="p-6 overflow-y-auto" id="checkoutVoucherModalBody">
            <div class="flex justify-center py-8">
                <svg class="animate-spin h-8 w-8 text-[#d92525]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            </div>
        </div>
    </div>
</div>

<script>
    let subTotal = {{ $subTotal }};
    let baseShippingFee = subTotal >= 500000 ? 0 : 30000;
    let baseTotal = subTotal + baseShippingFee;
    
    let appliedVouchers = {
        discount: null, // {code, amount, rawCoupon}
        freeship: null  // {code, amount, rawCoupon}
    };

    function openCheckoutVoucherModal() {
        document.getElementById('checkoutVoucherModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        fetchSavedVouchers();
    }

    function closeCheckoutVoucherModal() {
        document.getElementById('checkoutVoucherModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function fetchSavedVouchers() {
        fetch('{{ route('vouchers.active') }}')
            .then(res => res.json())
            .then(data => {
                const body = document.getElementById('checkoutVoucherModalBody');
                // Chỉ hiển thị voucher khách đã lưu; voucher hết lượt vẫn hiển thị nhưng không thể chọn.
                let myVouchers = data.vouchers.filter(v => v.is_saved);

                if (myVouchers.length === 0) {
                    body.innerHTML = '<div class="text-center text-gray-500 py-8">Bạn chưa lưu voucher nào hoặc không có voucher khả dụng.</div>';
                    return;
                }

                let html = '<div class="space-y-4">';
                myVouchers.forEach(v => {
                    let isFreeship = v.discount_type === 'freeship';
                    let bgColor = isFreeship ? 'bg-[#10b981]' : 'bg-[#d92525]';
                    let textIconColor = isFreeship ? 'text-[#10b981]' : 'text-[#d92525]';
                    let btnColor = isFreeship ? 'bg-[#10b981] hover:bg-emerald-600' : 'bg-[#d92525] hover:bg-red-700';

                    let discountText = isFreeship
                        ? 'Miễn phí vận chuyển'
                        : (v.discount_type === 'fixed' 
                            ? 'Giảm ' + new Intl.NumberFormat('vi-VN').format(v.discount_value) + 'đ'
                            : 'Giảm ' + v.discount_value + '%');
                    
                    let minOrderHtml = v.min_order_value 
                        ? `<div class="text-xs ${subTotal < v.min_order_value ? 'text-red-500' : 'text-gray-500'} mt-1">Đơn Tối Thiểu ${new Intl.NumberFormat('vi-VN').format(v.min_order_value)}đ</div>`
                        : '';
                    let maxDiscountHtml = v.discount_type === 'percent' && v.max_discount_amount
                        ? `<div class="text-xs text-gray-500 mt-1">Giảm tối đa ${new Intl.NumberFormat('vi-VN').format(v.max_discount_amount)}đ</div>`
                        : '';

                    let meetsMinimum = subTotal >= (v.min_order_value || 0);
                    let isValid = v.is_available && meetsMinimum;
                    let isApplied = (isFreeship && appliedVouchers.freeship?.code === v.code) || (!isFreeship && appliedVouchers.discount?.code === v.code);
                    
                    let actionBtn = '';
                    if (v.is_exhausted) {
                        actionBtn = `<button disabled class="px-4 py-1.5 text-sm font-bold text-gray-400 border border-gray-300 rounded cursor-not-allowed">Hết lượt</button>`;
                    } else if (v.is_used) {
                        actionBtn = `<button disabled class="px-4 py-1.5 text-sm font-bold text-gray-400 border border-gray-300 rounded cursor-not-allowed">Đã dùng</button>`;
                    } else if (isApplied) {
                        actionBtn = `<button onclick="removeVoucher('${isFreeship ? 'freeship' : 'discount'}'); fetchSavedVouchers();" class="px-4 py-1.5 text-sm font-bold text-gray-500 border border-gray-300 hover:bg-gray-50 rounded transition-colors">Bỏ chọn</button>`;
                    } else if (isValid) {
                        actionBtn = `<button onclick="applyVoucherCode('${v.code}')" class="px-4 py-1.5 text-sm font-bold text-white ${btnColor} rounded transition-colors">Dùng ngay</button>`;
                    } else {
                        actionBtn = `<button disabled class="px-4 py-1.5 text-sm font-bold text-gray-400 border border-gray-300 rounded cursor-not-allowed" title="Chưa đạt giá trị đơn tối thiểu">Chưa đạt</button>`;
                    }

                    html += `
                        <div class="bg-white rounded border ${isApplied ? (isFreeship ? 'border-emerald-500 shadow-md ring-1 ring-emerald-500' : 'border-red-500 shadow-md ring-1 ring-red-500') : 'border-gray-200 shadow-sm'} overflow-hidden flex ${(!v.is_available || !meetsMinimum) && !isApplied ? 'opacity-50 grayscale' : ''}">
                            <div class="w-28 ${bgColor} flex flex-col justify-center items-center text-white p-2 shrink-0 border-r border-dashed border-gray-300 relative">
                                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mb-1">
                                    <span class="${textIconColor} font-black text-xl">M</span>
                                </div>
                                <span class="text-[10px] font-bold uppercase tracking-wider bg-white/20 px-2 py-0.5 rounded-full">Mall</span>
                                <div class="absolute -left-1.5 top-0 bottom-0 flex flex-col justify-between py-1">
                                    ${Array(6).fill('<div class="w-3 h-3 bg-[#f6f6f6] rounded-full"></div>').join('')}
                                </div>
                            </div>
                            <div class="flex-1 p-3 flex flex-col justify-between">
                                <div>
                                    <div class="font-bold text-gray-900 text-base leading-tight">${discountText}</div>
                                    ${minOrderHtml}
                                    ${maxDiscountHtml}
                                    <div class="text-[10px] text-gray-400 mt-1">HSD: ${v.expires_at}</div>
                                </div>
                                <div class="flex justify-end mt-2">
                                    ${actionBtn}
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                body.innerHTML = html;
            })
            .catch(err => {
                document.getElementById('checkoutVoucherModalBody').innerHTML = '<div class="text-center text-red-500 py-8">Lỗi tải dữ liệu.</div>';
            });
    }

    function applyVoucherCode(code) {
        document.getElementById('voucherCodeInput').value = code;
        closeCheckoutVoucherModal();
        applyVoucher();
    }

    function applyVoucher() {
        let code = document.getElementById('voucherCodeInput').value.trim();
        let msgEl = document.getElementById('voucherMessage');
        let btn = document.getElementById('applyVoucherBtn');
        
        if (!code) {
            msgEl.textContent = 'Vui lòng nhập mã giảm giá';
            msgEl.className = 'mt-2 text-sm text-red-500 block';
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

        fetch('{{ route('vouchers.validate') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ code: code })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = 'Áp dụng';
            document.getElementById('voucherCodeInput').value = '';

            if (data.success) {
                let coupon = data.coupon;
                if (coupon.min_order_value && subTotal < coupon.min_order_value) {
                    msgEl.textContent = 'Đơn hàng chưa đạt giá trị tối thiểu ' + new Intl.NumberFormat('vi-VN').format(coupon.min_order_value) + 'đ để dùng mã này.';
                    msgEl.className = 'mt-2 text-sm text-red-500 block';
                    return;
                }

                let isFreeship = coupon.discount_type === 'freeship';
                
                // Calculate discount
                let amount = 0;
                if (isFreeship) {
                    amount = baseShippingFee;
                } else if (coupon.discount_type === 'fixed') {
                    amount = parseFloat(coupon.discount_value);
                } else {
                    amount = (subTotal * parseFloat(coupon.discount_value)) / 100;
                    amount = Math.min(amount, parseFloat(coupon.max_discount_amount));
                }

                let voucherObj = {
                    code: coupon.code,
                    amount: amount,
                    rawCoupon: coupon
                };

                if (isFreeship) {
                    appliedVouchers.freeship = voucherObj;
                } else {
                    appliedVouchers.discount = voucherObj;
                }

                msgEl.textContent = 'Đã áp dụng mã giảm giá thành công!';
                msgEl.className = 'mt-2 text-sm text-green-600 block font-medium';
                setTimeout(() => { msgEl.className = 'hidden'; }, 3000);

                renderAppliedVouchers();
                calculateTotal();
            } else {
                msgEl.textContent = data.message;
                msgEl.className = 'mt-2 text-sm text-red-500 block';
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = 'Áp dụng';
            msgEl.textContent = 'Có lỗi xảy ra.';
            msgEl.className = 'mt-2 text-sm text-red-500 block';
        });
    }

    function removeVoucher(type) {
        if (type === 'freeship') {
            appliedVouchers.freeship = null;
        } else {
            appliedVouchers.discount = null;
        }
        renderAppliedVouchers();
        calculateTotal();
    }

    function renderAppliedVouchers() {
        let container = document.getElementById('appliedVouchersContainer');
        container.innerHTML = '';
        
        let discountInput = document.getElementById('appliedDiscountCode');
        let freeshipInput = document.getElementById('appliedFreeshipCode');
        discountInput.value = '';
        freeshipInput.value = '';

        if (appliedVouchers.freeship) {
            freeshipInput.value = appliedVouchers.freeship.code;
            container.innerHTML += `
                <div class="flex items-center justify-between bg-emerald-50 border border-emerald-200 rounded px-3 py-2">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span class="text-sm font-medium text-emerald-800">Miễn phí vận chuyển: ${appliedVouchers.freeship.code}</span>
                    </div>
                    <button type="button" onclick="removeVoucher('freeship')" class="text-emerald-500 hover:text-emerald-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            `;
        }

        if (appliedVouchers.discount) {
            discountInput.value = appliedVouchers.discount.code;
            let displayDiscount = new Intl.NumberFormat('vi-VN').format(appliedVouchers.discount.amount) + 'đ';
            container.innerHTML += `
                <div class="flex items-center justify-between bg-red-50 border border-red-200 rounded px-3 py-2">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span class="text-sm font-medium text-red-800">Giảm ${displayDiscount}: ${appliedVouchers.discount.code}</span>
                    </div>
                    <button type="button" onclick="removeVoucher('discount')" class="text-red-500 hover:text-red-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            `;
        }
    }

    function calculateTotal() {
        let shippingFee = baseShippingFee;
        let discount = 0;

        if (appliedVouchers.freeship) {
            shippingFee = 0;
        }

        if (appliedVouchers.discount) {
            discount = appliedVouchers.discount.amount;
            if (discount > subTotal) {
                discount = subTotal;
            }
        }

        let finalTotal = subTotal + shippingFee - discount;

        document.getElementById('shippingFeeDisplay').textContent = shippingFee === 0 ? 'Miễn phí' : new Intl.NumberFormat('vi-VN').format(shippingFee) + 'đ';
        document.getElementById('shippingFeeDisplay').className = shippingFee === 0 ? 'font-bold text-green-600' : 'font-bold text-gray-900';

        if (discount > 0) {
            document.getElementById('discountAmountDisplay').textContent = '-' + new Intl.NumberFormat('vi-VN').format(discount) + 'đ';
            document.getElementById('discountRow').classList.remove('hidden');
        } else {
            document.getElementById('discountRow').classList.add('hidden');
        }

        document.getElementById('totalAmountDisplay').textContent = new Intl.NumberFormat('vi-VN').format(finalTotal) + 'đ';
        updateWalletSufficientCheck(finalTotal);
    }
</script>
@endpush
