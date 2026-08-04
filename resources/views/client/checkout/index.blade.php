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
                    
                    <div class="space-y-4">
                        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                            <input type="radio" name="payment_method" value="cod" class="w-5 h-5 text-red-600 focus:ring-red-600" checked>
                            <div class="ml-3">
                                <span class="block text-sm font-bold text-gray-900">Thanh toán khi nhận hàng (COD)</span>
                                <span class="block text-xs text-gray-500 mt-1">Khách hàng thanh toán bằng tiền mặt cho nhân viên giao hàng.</span>
                            </div>
                        </label>
                        
                        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                            <input type="radio" name="payment_method" value="vietqr" class="w-5 h-5 text-red-600 focus:ring-red-600">
                            <div class="ml-3 flex items-center gap-2">
                                <span class="block text-sm font-bold text-gray-900">Chuyển khoản (VietQR / App Ngân Hàng / MoMo)</span>
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
    });
</script>
@endpush
