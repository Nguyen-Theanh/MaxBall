@extends('admin.layouts.app')

@section('title', 'Quản lý Thuộc tính & Biến thể - MaxBall')
@section('page_title', 'Thuộc Tính Sản Phẩm')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <style>
        .sortable-ghost { opacity: 0.4; }
        .draggable-badge { cursor: grab; }
        .draggable-badge:active { cursor: grabbing; }
    </style>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Thêm thuộc tính mới</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.attributes.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Tên thuộc tính (VD: Màu sắc, Kích cỡ)</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Thêm thuộc tính</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            @foreach ($attributes as $attribute)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">{{ $attribute->name }}</h5>
                        <form method="POST" action="{{ route('admin.attributes.destroy', $attribute) }}"
                              data-confirm="Toàn bộ giá trị thuộc tính liên quan cũng sẽ bị xóa. Thao tác này không thể hoàn tác."
                              data-confirm-title="Xóa thuộc tính"
                              data-confirm-label="Xóa thuộc tính"
                              data-confirm-variant="danger">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Xóa thuộc tính</button>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 d-flex flex-wrap gap-2 sortable-values" data-attribute-id="{{ $attribute->id }}">
                            @forelse ($attribute->values as $value)
                                <div class="badge border text-dark bg-light p-2 d-flex align-items-center gap-2 fs-6 draggable-badge" data-id="{{ $value->id }}">
                                    <svg class="text-muted drag-handle me-1" width="12" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M7 2a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM7 5a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM7 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM7 11a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM7 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                    </svg>
                                    {{ $value->value }}
                                    <form method="POST" action="{{ route('admin.attributes.values.destroy', $value) }}" class="d-inline"
                                          data-confirm="Giá trị thuộc tính này sẽ bị xóa. Bạn có chắc chắn muốn tiếp tục?"
                                          data-confirm-title="Xóa giá trị thuộc tính"
                                          data-confirm-label="Xóa"
                                          data-confirm-variant="danger">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-close" style="font-size: 0.5rem;"></button>
                                    </form>
                                </div>
                            @empty
                                <span class="text-muted small">Chưa có giá trị nào.</span>
                            @endforelse
                        </div>
                        <hr>
                        <form method="POST" action="{{ route('admin.attributes.values.store', $attribute) }}" class="row g-2 align-items-center">
                            @csrf
                            <div class="col-auto">
                                <label class="visually-hidden">Thêm giá trị</label>
                                <input type="text" name="value" class="form-control form-control-sm" placeholder="Nhập giá trị (VD: Đỏ, 39...)" required>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-secondary">Thêm giá trị</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach

            @if($attributes->hasPages())
                <div class="mt-4">
                    {{ $attributes->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const containers = document.querySelectorAll('.sortable-values');
            containers.forEach(container => {
                new Sortable(container, {
                    animation: 150,
                    filter: '.btn-close',
                    preventOnFilter: false,
                    ghostClass: 'sortable-ghost',
                    onEnd: function (evt) {
                        const itemEl = evt.item;
                        const parent = itemEl.closest('.sortable-values');
                        const items = parent.querySelectorAll('[data-id]');
                        let order = [];
                        items.forEach(el => order.push(el.getAttribute('data-id')));

                        fetch("{{ route('admin.attributes.values.reorder') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ order: order })
                        }).then(res => res.json()).then(data => {
                            if (!data.success) {
                                window.AppConfirm.alert({
                                    title: 'Không thể lưu thứ tự',
                                    message: 'Có lỗi xảy ra khi lưu vị trí. Vui lòng thử lại.',
                                });
                            }
                        }).catch(err => {
                            console.error(err);
                            window.AppConfirm.alert({
                                title: 'Lỗi kết nối',
                                message: 'Không thể kết nối để lưu vị trí. Vui lòng kiểm tra kết nối và thử lại.',
                            });
                        });
                    }
                });
            });
        });
    </script>
@endsection
