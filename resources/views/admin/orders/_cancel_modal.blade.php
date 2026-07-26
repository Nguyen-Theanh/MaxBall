<div id="admin-cancel-modal" class="fixed inset-0 z-[1055] hidden items-center justify-center bg-black/60 px-4 py-8">
    <div class="w-full max-w-xl overflow-hidden rounded-3 bg-white shadow-lg">
        <div class="d-flex align-items-start justify-content-between border-bottom px-4 py-3">
            <div>
                <h5 class="mb-1 fw-bold">Hủy đơn hàng</h5>
                <p class="mb-0 small text-muted">Đơn hàng <strong id="admin-cancel-order-code"></strong></p>
            </div>
            <button type="button" data-close-admin-cancel class="btn-close" aria-label="Đóng"></button>
        </div>

        <form id="admin-cancel-form" method="POST" class="p-4">
            @csrf
            @method('PATCH')
            <input type="hidden" name="order_status" value="cancelled">
            <input type="hidden" name="cancel_order_id" id="admin-cancel-order-id" value="{{ old('cancel_order_id') }}">

            <div class="mb-3">
                <label for="admin-cancellation-reason" class="form-label fw-bold">
                    Lý do hủy đơn <span class="text-danger">*</span>
                </label>
                <select id="admin-cancellation-reason" name="cancellation_reason" class="form-select" required>
                    <option value="">-- Chọn lý do --</option>
                    @foreach($adminCancellationReasons as $value => $label)
                        <option value="{{ $value }}" @selected(old('cancellation_reason') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('cancellation_reason')
                    <div class="mt-1 small text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div id="admin-cancellation-note-wrapper" class="mb-3 {{ old('cancellation_reason') === 'other' ? '' : 'd-none' }}">
                <label for="admin-cancellation-note" class="form-label fw-bold">
                    Ghi chú lý do khác <span class="text-danger">*</span>
                </label>
                <textarea id="admin-cancellation-note" name="cancellation_note" rows="4" maxlength="1000" class="form-control" placeholder="Nhập lý do hủy đơn hàng...">{{ old('cancellation_note') }}</textarea>
                <div class="form-text">Tối đa 1.000 ký tự.</div>
                @error('cancellation_note')
                    <div class="mt-1 small text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="alert alert-warning small">
                Số lượng sản phẩm sẽ được hoàn lại kho. Thao tác hủy đơn không thể hoàn tác.
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button type="button" data-close-admin-cancel class="btn btn-outline-secondary">Quay lại</button>
                <button type="submit" class="btn btn-danger">Xác nhận hủy đơn</button>
            </div>
        </form>
    </div>
</div>

<script>
(() => {
    const modal = document.getElementById('admin-cancel-modal');
    const form = document.getElementById('admin-cancel-form');
    const orderIdInput = document.getElementById('admin-cancel-order-id');
    const orderCode = document.getElementById('admin-cancel-order-code');
    const reasonSelect = document.getElementById('admin-cancellation-reason');
    const noteWrapper = document.getElementById('admin-cancellation-note-wrapper');
    const noteInput = document.getElementById('admin-cancellation-note');
    let activeSource = null;

    const toggleNote = () => {
        const isOther = reasonSelect.value === 'other';
        noteWrapper.classList.toggle('d-none', !isOther);
        noteInput.required = isOther;

        if (!isOther) {
            noteInput.value = '';
        }
    };

    window.openAdminCancelModal = (source, preserveValues = false) => {
        activeSource = source;

        if (!preserveValues) {
            form.reset();
        }

        form.action = source.dataset.cancelAction;
        orderIdInput.value = source.dataset.orderId;
        orderCode.textContent = `#${source.dataset.orderCode}`;
        source.value = source.dataset.currentStatus;
        toggleNote();
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    const closeModal = () => {
        if (activeSource) {
            activeSource.value = activeSource.dataset.currentStatus;
        }

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    document.querySelectorAll('[data-close-admin-cancel]').forEach((button) => {
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
        const restoredSource = document.querySelector(`[data-admin-cancel][data-order-id="${restoredOrderId}"]`);
        if (restoredSource) {
            window.openAdminCancelModal(restoredSource, true);
        }
    }
})();
</script>
