@extends('admin.layouts.app')

@section('title', 'Quản lý Đơn hàng')

@section('content')
@php($showPackingSlipActions = request('status') === 'confirmed')

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                Danh sách Đơn hàng
            </h6>

            <form
                action="{{ route('admin.orders.index') }}"
                method="GET"
                class="d-flex gap-2"
            >
                <select
                    name="status"
                    class="form-select form-select-sm w-auto"
                    onchange="this.form.submit()"
                >
                    <option value="">Tất cả trạng thái</option>

                    <option
                        value="pending"
                        {{ request('status') == 'pending' ? 'selected' : '' }}
                    >
                        Chờ xác nhận
                    </option>

                    <option
                        value="confirmed"
                        {{ request('status') == 'confirmed' ? 'selected' : '' }}
                    >
                        Đã xác nhận
                    </option>

                    <option
                        value="shipping"
                        {{ request('status') == 'shipping' ? 'selected' : '' }}
                    >
                        Đang giao hàng
                    </option>

                    <option
                        value="completed"
                        {{ request('status') == 'completed' ? 'selected' : '' }}
                    >
                        Hoàn thành
                    </option>

                    <option
                        value="cancelled"
                        {{ request('status') == 'cancelled' ? 'selected' : '' }}
                    >
                        Đã hủy
                    </option>
                </select>

                <select
                    name="per_page"
                    class="form-select form-select-sm w-auto"
                    aria-label="Số đơn mỗi trang"
                    onchange="this.form.submit()"
                >
                    <option
                        value="10"
                        @selected((int) request('per_page', 10) === 10)
                    >
                        10 đơn/trang
                    </option>

                    <option
                        value="20"
                        @selected((int) request('per_page', 10) === 20)
                    >
                        20 đơn/trang
                    </option>

                    <option
                        value="50"
                        @selected((int) request('per_page', 10) === 50)
                    >
                        50 đơn/trang
                    </option>
                </select>

                <div class="input-group input-group-sm w-auto">
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Mã ĐH, Tên, SĐT..."
                        value="{{ request('search') }}"
                    >

                    <button
                        class="btn btn-outline-secondary"
                        type="submit"
                    >
                        Tìm
                    </button>
                </div>
            </form>
        </div>

        <div class="card-body">
            @if($showPackingSlipActions)
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <button
                        type="button"
                        class="btn btn-dark btn-sm"
                        id="print-selected-orders"
                        disabled
                    >
                        <i class="bi bi-printer me-1"></i>
                        In phiếu đã chọn
                    </button>

                    <span class="small text-muted" id="selected-orders-count">
                        Đã chọn 0 đơn
                    </span>
                </div>

                <form
                    action="{{ route('admin.orders.packing-slips') }}"
                    method="POST"
                    target="_blank"
                    id="print-selected-orders-form"
                    class="d-none"
                >
                    @csrf
                    <div id="selected-order-inputs"></div>
                </form>
            @endif

            <div class="table-responsive">
                <table
                    class="table table-bordered table-hover align-middle"
                    width="100%"
                    cellspacing="0"
                >
                    <thead class="table-light">
                        <tr>
                            @if($showPackingSlipActions)
                                <th class="text-center" width="44">
                                    <input
                                        type="checkbox"
                                        class="form-check-input"
                                        id="select-all-orders"
                                        aria-label="Chọn tất cả đơn trên trang"
                                    >
                                </th>
                            @endif
                            <th>Mã ĐH</th>
                            <th>Khách hàng</th>
                            <th>Tổng tiền</th>
                            <th>Thanh toán</th>
                            <th>Trạng thái</th>
                            <th>Ngày đặt</th>
                            <th
                                class="text-center"
                                width="120"
                            >
                                Hành động
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($orders as $order)
                            <tr
                                @if($showPackingSlipActions)
                                    data-packing-order-row
                                    data-packing-slip-printed="{{ $order->packing_slip_printed_at ? '1' : '0' }}"
                                @endif
                            >
                                @if($showPackingSlipActions)
                                    <td class="text-center">
                                        <input
                                            type="checkbox"
                                            class="form-check-input packing-order-checkbox"
                                            value="{{ $order->id }}"
                                            aria-label="Chọn đơn #{{ $order->order_code }}"
                                        >
                                    </td>
                                @endif
                                <td class="font-weight-bold">
                                    #{{ $order->order_code }}

                                    @if(
                                        $showPackingSlipActions
                                        && $order->packing_slip_printed_at
                                    )
                                        <div class="mt-1" data-packing-status>
                                            <span
                                                class="badge bg-secondary"
                                                title="Đã in lúc {{ $order->packing_slip_printed_at->format('H:i d/m/Y') }}"
                                            >
                                                <i class="bi bi-check-circle me-1"></i>
                                                Đã in
                                            </span>
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <div class="fw-bold">
                                        {{ $order->customer_name }}
                                    </div>

                                    <div class="small text-muted">
                                        {{ $order->customer_phone }}
                                    </div>
                                </td>

                                <td class="text-danger fw-bold">
                                    {{ number_format($order->total_amount, 0, ',', '.') }}đ
                                </td>

                                <td>
                                    @if($order->payment_status == 'paid')
                                        <span class="badge bg-success">
                                            Đã thanh toán
                                            ({{ strtoupper($order->payment_method) }})
                                        </span>

                                    @elseif($order->payment_status == 'failed')
                                        <span class="badge bg-danger">
                                            Thất bại
                                        </span>

                                    @else
                                        @if(
                                            $order->payment_method == 'cod'
                                            && $order->order_status == 'shipping'
                                        )
                                            <form
                                                action="{{ route('admin.orders.updatePaymentStatus', $order->id) }}"
                                                method="POST"
                                                class="m-0"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <select
                                                    name="payment_status"
                                                    class="form-select form-select-sm"
                                                    onchange="confirmPaymentAndSubmit(this)"
                                                >
                                                    <option
                                                        value="pending"
                                                        selected
                                                    >
                                                        Chưa thanh toán (COD)
                                                    </option>

                                                    <option value="paid">
                                                        Đã thanh toán (COD)
                                                    </option>
                                                </select>
                                            </form>
                                        @else
                                            <span class="badge bg-warning text-dark">
                                                Chưa thanh toán
                                                ({{ strtoupper($order->payment_method) }})
                                            </span>
                                        @endif
                                    @endif
                                </td>

                                <td>
                                    @if(
                                        in_array(
                                            $order->order_status,
                                            ['completed', 'cancelled']
                                        )
                                    )
                                        @if($order->order_status == 'completed')
                                            <span class="badge bg-success px-2 py-2">
                                                Hoàn thành
                                            </span>
                                        @else
                                            <span class="badge bg-danger px-2 py-2">
                                                Đã hủy
                                            </span>

                                            @if($order->cancellation_reason)
                                                <div
                                                    class="mt-1 small text-danger"
                                                    style="max-width: 220px;"
                                                >
                                                    {{ $order->cancellation_reason_label }}
                                                </div>
                                            @endif
                                        @endif
                                    @else
                                        <form
                                            action="{{ route('admin.orders.updateStatus', $order->id) }}"
                                            method="POST"
                                            class="m-0"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <select
                                                name="order_status"
                                                class="form-select form-select-sm"
                                                style="min-width: 140px;"
                                                data-admin-cancel
                                                data-order-id="{{ $order->id }}"
                                                data-order-code="{{ $order->order_code }}"
                                                data-current-status="{{ $order->order_status }}"
                                                data-cancel-action="{{ route('admin.orders.updateStatus', $order->id) }}"
                                                onchange="confirmAndSubmit(this)"
                                            >
                                                @if($order->order_status == 'pending')
                                                    <option
                                                        value="pending"
                                                        selected
                                                    >
                                                        Chờ xác nhận
                                                    </option>

                                                    <option value="confirmed">
                                                        Xác nhận đơn
                                                    </option>

                                                    <option value="cancelled">
                                                        Hủy đơn hàng
                                                    </option>

                                                @elseif(
                                                    in_array(
                                                        $order->order_status,
                                                        ['confirmed', 'processing']
                                                    )
                                                )
                                                    <option
                                                        value="{{ $order->order_status }}"
                                                        selected
                                                    >
                                                        Đã xác nhận
                                                    </option>

                                                    <option value="shipping">
                                                        Đang giao hàng
                                                    </option>

                                                    <option value="cancelled">
                                                        Hủy đơn hàng
                                                    </option>

                                                @elseif($order->order_status == 'shipping')
                                                    <option
                                                        value="shipping"
                                                        selected
                                                    >
                                                        Đang giao hàng
                                                    </option>

                                                    @if($order->payment_status == 'paid')
                                                        <option value="completed">
                                                            Hoàn thành
                                                        </option>
                                                    @endif
                                                @endif
                                            </select>

                                            @if(
                                                $order->hasActiveReservation()
                                                && $order->reservation_expires_at
                                            )
                                                <div class="mt-1 small text-warning">
                                                    Giữ hàng đến
                                                    {{ $order->reservation_expires_at->format('H:i d/m/Y') }}
                                                </div>
                                            @endif
                                        </form>
                                    @endif
                                </td>

                                <td>
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </td>

                                <td class="text-center">
                                    <div class="d-flex flex-wrap justify-content-center gap-1">
                                        <a
                                            href="{{ route('admin.orders.show', $order->id) }}"
                                            class="btn btn-primary btn-sm"
                                            title="Chi tiết"
                                        >
                                            Chi tiết
                                        </a>

                                        @if($showPackingSlipActions)
                                            <form
                                                action="{{ route('admin.orders.packing-slips') }}"
                                                method="POST"
                                                target="_blank"
                                                class="m-0 packing-slip-form"
                                            >
                                                @csrf
                                                <input
                                                    type="hidden"
                                                    name="order_ids[]"
                                                    value="{{ $order->id }}"
                                                >
                                                <button
                                                    type="submit"
                                                    class="btn btn-outline-dark btn-sm"
                                                    title="In phiếu đóng hàng"
                                                >
                                                    <i class="bi bi-printer me-1"></i>
                                                    <span data-packing-button-label>
                                                        {{ $order->packing_slip_printed_at ? 'In lại' : 'In phiếu' }}
                                                    </span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="{{ $showPackingSlipActions ? 8 : 7 }}"
                                    class="text-center py-4"
                                >
                                    Chưa có đơn hàng nào!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div
                class="mt-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2"
            >
                <div class="small text-muted">
                    @if($orders->total() > 0)
                        Hiển thị {{ $orders->firstItem() }}–{{ $orders->lastItem() }} trên tổng {{ number_format($orders->total()) }} đơn hàng
                    @else
                        Không có đơn hàng phù hợp
                    @endif
                </div>

                @if($orders->hasPages())
                    {{ $orders->onEachSide(1)->links() }}
                @endif
            </div>
        </div>
    </div>
</div>

<script>
async function confirmPaymentAndSubmit(selectElement) {
    const confirmed = await window.AppConfirm.open({
        title: 'Xác nhận thanh toán COD',
        message: 'Xác nhận đã thu đủ tiền COD cho đơn hàng này?',
        confirmLabel: 'Đã thu tiền',
        variant: 'primary',
    });

    if (confirmed) {
        HTMLFormElement.prototype.submit.call(
            selectElement.closest('form')
        );
    } else {
        selectElement.value = 'pending';
    }
}

async function confirmAndSubmit(selectElement) {
    const form = selectElement.closest('form');
    const status = selectElement.value;

    let options = null;

    if (status === 'confirmed') {
        options = {
            title: 'Xác nhận đơn COD',
            message:
                'Xác nhận đơn và trừ số hàng đang giữ khỏi tồn kho?',
            confirmLabel: 'Xác nhận đơn',
            variant: 'primary',
        };
    } else if (status === 'completed') {
        options = {
            title: 'Hoàn thành đơn hàng',
            message:
                'Xác nhận đơn hàng đã được giao thành công và chuyển sang trạng thái hoàn thành?',
            confirmLabel: 'Hoàn thành',
            variant: 'primary',
        };
    } else if (status === 'cancelled') {
        window.openAdminCancelModal(
            selectElement
        );

        return;
    }

    if (
        !options
        || await window.AppConfirm.open(options)
    ) {
        HTMLFormElement.prototype.submit.call(
            form
        );
    } else {
        form.reset();
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const orderCheckboxes = Array.from(
        document.querySelectorAll('.packing-order-checkbox')
    );
    const selectAll = document.getElementById('select-all-orders');
    const printButton = document.getElementById('print-selected-orders');
    const selectedCount = document.getElementById('selected-orders-count');
    const printForm = document.getElementById('print-selected-orders-form');
    const selectedInputs = document.getElementById('selected-order-inputs');

    if (!selectAll || !printButton || !selectedCount || !printForm || !selectedInputs) {
        return;
    }

    function selectedOrders() {
        return orderCheckboxes.filter((checkbox) => checkbox.checked);
    }

    function updatePackingSelection() {
        const selected = selectedOrders();

        printButton.disabled = selected.length === 0;
        selectedCount.textContent = `Đã chọn ${selected.length} đơn`;
        selectAll.checked = orderCheckboxes.length > 0
            && selected.length === orderCheckboxes.length;
        selectAll.indeterminate = selected.length > 0
            && selected.length < orderCheckboxes.length;
    }

    function markOrdersAsPrinted(checkboxes) {
        const rows = checkboxes
            .map((checkbox) => checkbox.closest('[data-packing-order-row]'))
            .filter((row) => row && row.dataset.packingSlipPrinted === '0');
        const tableBody = rows[0]?.parentElement;
        const firstPrintedRow = tableBody?.querySelector(
            '[data-packing-order-row][data-packing-slip-printed="1"]'
        );

        rows.forEach((row) => {
            row.dataset.packingSlipPrinted = '1';

            const orderCodeCell = row.querySelector('td:nth-child(2)');
            const statusWrapper = document.createElement('div');
            const statusBadge = document.createElement('span');

            statusWrapper.className = 'mt-1';
            statusWrapper.dataset.packingStatus = '';
            statusBadge.className = 'badge bg-secondary';
            statusBadge.textContent = 'Đã in';
            statusWrapper.appendChild(statusBadge);
            orderCodeCell?.appendChild(statusWrapper);

            const buttonLabel = row.querySelector('[data-packing-button-label]');

            if (buttonLabel) {
                buttonLabel.textContent = 'In lại';
            }

            if (firstPrintedRow) {
                tableBody.insertBefore(row, firstPrintedRow);
            } else {
                tableBody?.appendChild(row);
            }
        });

        checkboxes.forEach((checkbox) => {
            checkbox.checked = false;
        });

        updatePackingSelection();
    }

    selectAll.addEventListener('change', function () {
        orderCheckboxes.forEach((checkbox) => {
            checkbox.checked = selectAll.checked;
        });

        updatePackingSelection();
    });

    orderCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', updatePackingSelection);
    });

    printButton.addEventListener('click', function () {
        const selected = selectedOrders();

        selectedInputs.replaceChildren();

        selected.forEach((checkbox) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'order_ids[]';
            input.value = checkbox.value;
            selectedInputs.appendChild(input);
        });

        printForm.submit();
        markOrdersAsPrinted(selected);
    });

    document.querySelectorAll('.packing-slip-form').forEach((form) => {
        form.addEventListener('submit', function () {
            const checkbox = form
                .closest('[data-packing-order-row]')
                ?.querySelector('.packing-order-checkbox');

            if (checkbox) {
                window.setTimeout(() => markOrdersAsPrinted([checkbox]), 0);
            }
        });
    });
});
</script>

@include('admin.orders._cancel_modal')
@endsection
