<div id="customer-cancel-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/60 px-4 py-8">
    <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b px-6 py-4">
            <div>
                <h2 class="text-xl font-black text-gray-900">Hủy đơn hàng</h2>
                <p class="mt-1 text-sm text-gray-500">Đơn hàng <strong id="customer-cancel-order-code"></strong></p>
            </div>
            <button type="button" data-close-customer-cancel class="text-2xl leading-none text-gray-400 hover:text-gray-700">&times;</button>
        </div>

        <form id="customer-cancel-form" method="POST" class="space-y-5 p-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="cancel_order_id" id="customer-cancel-order-id" value="{{ old('cancel_order_id') }}">

            <div>
                <label for="customer-cancellation-reason" class="mb-2 block text-sm font-bold text-gray-700">
                    Lý do hủy đơn <span class="text-red-600">*</span>
                </label>
                <select id="customer-cancellation-reason" name="cancellation_reason" class="w-full rounded-xl border border-gray-300 px-4 py-3 outline-none focus:border-red-500 focus:ring-4 focus:ring-red-500/10" required>
                    <option value="">-- Chọn lý do --</option>
                    @foreach($customerCancellationReasons as $value => $label)
                        <option value="{{ $value }}" @selected(old('cancellation_reason') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('cancellation_reason')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div id="customer-cancellation-note-wrapper" class="{{ old('cancellation_reason') === 'other' ? '' : 'hidden' }}">
                <label for="customer-cancellation-note" class="mb-2 block text-sm font-bold text-gray-700">
                    Lý do khác <span class="text-red-600">*</span>
                </label>
                <textarea id="customer-cancellation-note" name="cancellation_note" rows="4" maxlength="1000" class="w-full rounded-xl border border-gray-300 px-4 py-3 outline-none focus:border-red-500 focus:ring-4 focus:ring-red-500/10" placeholder="Vui lòng nhập lý do hủy đơn hàng...">{{ old('cancellation_note') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Tối đa 1.000 ký tự.</p>
                @error('cancellation_note')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="rounded-xl bg-amber-50 p-4 text-sm leading-6 text-amber-800">
                Sản phẩm sẽ được hoàn lại kho sau khi hủy. Thao tác này không thể hoàn tác.
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" data-close-customer-cancel class="rounded-xl border border-gray-300 px-5 py-2.5 font-bold text-gray-700 hover:bg-gray-50">
                    Quay lại
                </button>
                <button type="submit" class="rounded-xl bg-red-600 px-5 py-2.5 font-bold text-white hover:bg-red-700">
                    Xác nhận hủy đơn
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('customer-cancel-modal');
    const form = document.getElementById('customer-cancel-form');
    const orderIdInput = document.getElementById('customer-cancel-order-id');
    const orderCode = document.getElementById('customer-cancel-order-code');
    const reasonSelect = document.getElementById('customer-cancellation-reason');
    const noteWrapper = document.getElementById('customer-cancellation-note-wrapper');
    const noteInput = document.getElementById('customer-cancellation-note');

    const toggleNote = () => {
        const isOther = reasonSelect.value === 'other';
        noteWrapper.classList.toggle('hidden', !isOther);
        noteInput.required = isOther;

        if (!isOther) {
            noteInput.value = '';
        }
    };

    const openModal = (trigger, preserveValues = false) => {
        if (!preserveValues) {
            form.reset();
        }

        form.action = trigger.dataset.cancelAction;
        orderIdInput.value = trigger.dataset.orderId;
        orderCode.textContent = `#${trigger.dataset.orderCode}`;
        toggleNote();
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    document.querySelectorAll('[data-customer-cancel]').forEach((trigger) => {
        trigger.addEventListener('click', () => openModal(trigger));
    });

    document.querySelectorAll('[data-close-customer-cancel]').forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    reasonSelect.addEventListener('change', toggleNote);
    toggleNote();

    const restoredOrderId = @json(old('cancel_order_id'));
    if (restoredOrderId) {
        const restoredTrigger = document.querySelector(`[data-customer-cancel][data-order-id="${restoredOrderId}"]`);
        if (restoredTrigger) {
            openModal(restoredTrigger, true);
        }
    }
});
</script>
