@extends('admin.layouts.app')

@section('content')
<div class="px-8 py-6">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Sửa Voucher</h2>
            <p class="text-sm text-gray-500 mt-1">Chỉnh sửa thông tin mã giảm giá: <span class="font-bold text-blue-600">{{ $coupon->code }}</span></p>
        </div>
        <a href="{{ route('admin.coupons.index') }}" class="text-gray-500 hover:text-gray-700 font-medium flex items-center gap-2 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Quay lại
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.coupons.update', $coupon->id) }}" method="POST" class="p-8">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Mã Voucher -->
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Mã Voucher (Code) <span class="text-red-500">*</span></label>
                    <input type="text" id="coupon_code" name="code" value="{{ old('code', $coupon->code) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors uppercase">
                    <p id="code_message" class="text-xs mt-1 hidden"></p>
                    @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Mô tả -->
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Mô tả Voucher</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">{{ old('description', $coupon->description) }}</textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Loại giảm giá -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Loại Giảm Giá <span class="text-red-500">*</span></label>
                    <select name="discount_type" id="discount_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value="fixed" {{ old('discount_type', $coupon->discount_type) == 'fixed' ? 'selected' : '' }}>Giảm số tiền cố định (VNĐ)</option>
                        <option value="percent" {{ old('discount_type', $coupon->discount_type) == 'percent' ? 'selected' : '' }}>Giảm theo phần trăm (%)</option>
                        <option value="freeship" {{ old('discount_type', $coupon->discount_type) == 'freeship' ? 'selected' : '' }}>Miễn phí vận chuyển (FreeShip)</option>
                    </select>
                    @error('discount_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Giá trị giảm -->
                <div id="discount_value_container">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Giá trị giảm <span class="text-red-500">*</span></label>
                    <input type="number" name="discount_value" id="discount_value" value="{{ old('discount_value', $coupon->discount_value) }}" min="1000" step="1000" inputmode="numeric" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    @error('discount_value') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Mức giảm tối đa cho voucher phần trăm -->
                <div id="max_discount_amount_container" style="display: none;">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Số tiền giảm tối đa (VNĐ) <span class="text-red-500">*</span></label>
                    <input type="number" name="max_discount_amount" id="max_discount_amount" value="{{ old('max_discount_amount', $coupon->max_discount_amount) }}" min="1000" step="1000" inputmode="numeric" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="VD: 100000">
                    <p class="text-xs text-gray-500 mt-1">Nhập số nguyên theo bội số 1.000đ. Khách sẽ không được giảm quá số tiền này.</p>
                    @error('max_discount_amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Đơn tối thiểu -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Giá trị đơn tối thiểu (VNĐ)</label>
                    <input type="number" name="min_order_value" value="{{ old('min_order_value', $coupon->min_order_value > 0 ? $coupon->min_order_value : '') }}" min="1000" step="1000" inputmode="numeric" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Để trống nếu không yêu cầu">
                    @error('min_order_value') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Giới hạn sử dụng -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Số lượng Voucher (Giới hạn sử dụng)</label>
                    <input type="number" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}" min="1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    @error('usage_limit') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Thời gian bắt đầu -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Ngày bắt đầu</label>
                    <input type="datetime-local" name="start_date" value="{{ old('start_date', $coupon->start_date ? $coupon->start_date->format('Y-m-d\TH:i') : '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Thời gian kết thúc -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Ngày hết hạn</label>
                    <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $coupon->expires_at ? $coupon->expires_at->format('Y-m-d\TH:i') : '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    @error('expires_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Trạng thái -->
                <div class="col-span-1 md:col-span-2 mt-4">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="status" value="1" {{ old('status', $coupon->status) ? 'checked' : '' }} class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                        <span class="text-sm font-semibold text-gray-700">Kích hoạt Voucher (Cho phép sử dụng)</span>
                    </label>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-4 border-t border-gray-100 pt-6">
                <a href="{{ route('admin.coupons.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                    Hủy bỏ
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    Cập nhật Voucher
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const discountTypeSelect = document.getElementById('discount_type');
        const discountValueContainer = document.getElementById('discount_value_container');
        const discountValueInput = document.getElementById('discount_value');
        const maxDiscountAmountContainer = document.getElementById('max_discount_amount_container');
        const maxDiscountAmountInput = document.getElementById('max_discount_amount');

        function toggleDiscountValue() {
            if (discountTypeSelect.value === 'freeship') {
                discountValueContainer.style.display = 'none';
                discountValueInput.removeAttribute('required');
                discountValueInput.value = '0';
            } else {
                discountValueContainer.style.display = 'block';
                discountValueInput.setAttribute('required', 'required');
                if (Number(discountValueInput.value) === 0) discountValueInput.value = '';
            }

            const isPercent = discountTypeSelect.value === 'percent';
            discountValueInput.min = isPercent ? '1' : '1000';
            discountValueInput.step = isPercent ? '1' : '1000';
            if (isPercent) {
                discountValueInput.max = '100';
            } else {
                discountValueInput.removeAttribute('max');
            }

            maxDiscountAmountContainer.style.display = isPercent ? 'block' : 'none';

            if (isPercent) {
                maxDiscountAmountInput.setAttribute('required', 'required');
            } else {
                maxDiscountAmountInput.removeAttribute('required');
            }
        }

        discountTypeSelect.addEventListener('change', toggleDiscountValue);
        toggleDiscountValue(); // initial load

        // Code duplicate check
        const codeInput = document.getElementById('coupon_code');
        const codeMessage = document.getElementById('code_message');
        const currentId = {{ $coupon->id }};
        let checkTimeout;

        codeInput.addEventListener('input', function() {
            clearTimeout(checkTimeout);
            const code = this.value.trim();
            
            if (code.length === 0) {
                codeMessage.classList.add('hidden');
                return;
            }

            // Show checking state
            codeMessage.textContent = 'Đang kiểm tra...';
            codeMessage.className = 'text-xs mt-1 text-gray-500 block';

            checkTimeout = setTimeout(() => {
                fetch(`{{ route('admin.coupons.check-code') }}?code=${encodeURIComponent(code)}&exclude_id=${currentId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.exists) {
                            codeMessage.textContent = 'Mã voucher này đã tồn tại!';
                            codeMessage.className = 'text-xs mt-1 text-red-500 block font-bold';
                            codeInput.classList.add('border-red-500', 'focus:ring-red-500');
                            codeInput.classList.remove('border-gray-300', 'focus:ring-blue-500');
                        } else {
                            codeMessage.textContent = 'Mã voucher hợp lệ có thể sử dụng.';
                            codeMessage.className = 'text-xs mt-1 text-green-600 block font-bold';
                            codeInput.classList.remove('border-red-500', 'focus:ring-red-500');
                            codeInput.classList.add('border-green-500', 'focus:ring-green-500');
                        }
                    })
                    .catch(err => {
                        codeMessage.textContent = 'Lỗi kiểm tra mã.';
                        codeMessage.className = 'text-xs mt-1 text-red-500 block';
                    });
            }, 500); // 500ms debounce
        });
    });
</script>
@endpush
@endsection
