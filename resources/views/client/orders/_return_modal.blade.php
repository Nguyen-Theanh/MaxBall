<div id="customer-return-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/60 px-4 py-8">
    <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between border-b px-6 py-4 shrink-0">
            <div>
                <h2 class="text-xl font-black text-gray-900" id="customer-return-title">Yêu cầu Trả hàng / Hoàn tiền</h2>
                <p class="mt-1 text-sm text-gray-500">Đơn hàng <strong id="customer-return-order-code"></strong></p>
            </div>
            <button type="button" data-close-customer-return class="text-2xl leading-none text-gray-400 hover:text-gray-700">&times;</button>
        </div>

        <div class="overflow-y-auto p-6">
            <form id="customer-return-form" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <input type="hidden" name="return_order_id" id="customer-return-order-id" value="{{ old('return_order_id') }}">
                <input type="hidden" name="type" id="customer-return-type" value="{{ old('type', 'return') }}">

                <div>
                    <label for="customer-return-reason" class="mb-2 block text-sm font-bold text-gray-700">
                        Lý do <span class="text-red-600">*</span>
                    </label>
                    <select id="customer-return-reason" name="reason" class="w-full rounded-xl border border-gray-300 px-4 py-3 outline-none focus:border-red-500 focus:ring-4 focus:ring-red-500/10" required>
                        <option value="">-- Chọn lý do --</option>
                        <option value="Hàng bị lỗi / hư hỏng" @selected(old('reason') === 'Hàng bị lỗi / hư hỏng')>Hàng bị lỗi / hư hỏng</option>
                        <option value="Giao thiếu hàng" @selected(old('reason') === 'Giao thiếu hàng')>Giao thiếu hàng</option>
                        <option value="Giao sai sản phẩm" @selected(old('reason') === 'Giao sai sản phẩm')>Giao sai sản phẩm</option>
                        <option value="Hàng không giống mô tả" @selected(old('reason') === 'Hàng không giống mô tả')>Hàng không giống mô tả</option>
                        <option value="Khác" @selected(old('reason') === 'Khác')>Khác</option>
                    </select>
                    @error('reason')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="customer-return-description" class="mb-2 block text-sm font-bold text-gray-700">
                        Mô tả chi tiết <span class="text-red-600">*</span>
                    </label>
                    <textarea id="customer-return-description" name="description" rows="4" maxlength="1000" class="w-full rounded-xl border border-gray-300 px-4 py-3 outline-none focus:border-red-500 focus:ring-4 focus:ring-red-500/10" placeholder="Vui lòng cung cấp thêm thông tin chi tiết về tình trạng hàng hóa..." required>{{ old('description') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Tối đa 1.000 ký tự.</p>
                    @error('description')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold text-gray-700">
                        Hình ảnh bằng chứng
                    </label>
                    <input type="file" name="images[]" multiple accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                    <p class="mt-1 text-xs text-gray-500">Bạn có thể chọn tối đa 5 ảnh.</p>
                    @error('images')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    @error('images.*')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-xl bg-amber-50 p-4 text-sm leading-6 text-amber-800">
                    Lưu ý: Yêu cầu của bạn sẽ được xem xét. Nếu hợp lệ, tiền sẽ được hoàn vào Ví MaxBall.
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" data-close-customer-return class="rounded-xl border border-gray-300 px-5 py-2.5 font-bold text-gray-700 hover:bg-gray-50">
                        Hủy
                    </button>
                    <button type="submit" class="rounded-xl bg-red-600 px-5 py-2.5 font-bold text-white hover:bg-red-700">
                        Gửi yêu cầu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('customer-return-modal');
    const form = document.getElementById('customer-return-form');
    const orderIdInput = document.getElementById('customer-return-order-id');
    const typeInput = document.getElementById('customer-return-type');
    const title = document.getElementById('customer-return-title');
    const orderCode = document.getElementById('customer-return-order-code');

    const openModal = (trigger, preserveValues = false) => {
        if (!preserveValues) {
            form.reset();
        }
        
        const type = trigger.dataset.returnType; // 'return' or 'complaint'
        typeInput.value = type;
        
        if (type === 'return') {
            title.textContent = 'Yêu cầu Trả hàng / Hoàn tiền';
        } else {
            title.textContent = 'Gửi khiếu nại đơn hàng';
        }

        form.action = trigger.dataset.action;
        orderIdInput.value = trigger.dataset.orderId;
        orderCode.textContent = #;
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    document.querySelectorAll('[data-customer-return]').forEach((trigger) => {
        trigger.addEventListener('click', () => openModal(trigger));
    });

    document.querySelectorAll('[data-close-customer-return]').forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    const restoredOrderId = @json(old('return_order_id'));
    if (restoredOrderId) {
        const type = @json(old('type', 'return'));
        const restoredTrigger = document.querySelector([data-customer-return][data-order-id=""][data-return-type=""]);
        if (restoredTrigger) {
            openModal(restoredTrigger, true);
        }
    }
});
</script>
