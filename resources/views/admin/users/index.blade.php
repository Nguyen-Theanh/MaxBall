@extends('admin.layouts.app')

@section('title', 'Quan ly nguoi dung - MaxBall')
@section('page_title', 'Quản lý người dùng')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-3 mb-4">
                <form class="row g-2 flex-grow-1" method="GET" action="{{ route('admin.users.index') }}">
                    <div class="col-12 col-md-5">
                        <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Tìm tên, email, số điện thoại...">
                    </div>
                    <div class="col-12 col-md-2">
                        <select name="role" class="form-select">
                            <option value="">Tất cả role</option>
                            <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                            <option value="customer" @selected(request('role') === 'customer')>Customer</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Tất cả trạng thái</option>
                            <option value="1" @selected(request('status') === '1')>Đang hoạt động</option>
                            <option value="0" @selected(request('status') === '0')>Bị khóa</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-auto">
                        <button class="btn btn-dark" type="submit">Lọc</button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>


            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Trạng thái</th>
                            <th>Đơn hàng</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $user->name }}</div>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </td>
                                <td>{{ $user->phone ?: '-' }}</td>
                                <td>
                                    <form method="POST"
                                          action="{{ route('admin.users.toggle-role', $user) }}"
                                          class="role-toggle-form d-inline-flex align-items-center gap-2"
                                          data-user-name="{{ $user->name }}">
                                        @csrf
                                        @method('PATCH')
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input role-toggle"
                                                   type="checkbox"
                                                   role="switch"
                                                   id="role-toggle-{{ $user->id }}"
                                                   aria-label="Đổi role của {{ $user->name }}"
                                                   @checked($user->role === 'admin')
                                                   @disabled(auth()->id() === $user->id)>
                                        </div>
                                        <label for="role-toggle-{{ $user->id }}"
                                               class="badge {{ $user->role === 'admin' ? 'text-bg-danger' : 'text-bg-info' }}">
                                            {{ ucfirst($user->role) }}
                                        </label>
                                        @if(auth()->id() === $user->id)
                                            <small class="text-muted fst-italic">(Bạn)</small>
                                        @endif
                                    </form>
                                </td>
                                <td>
                                    @if ($user->status)
                                        <span class="badge text-bg-success">Hoạt động</span>
                                    @else
                                        <span class="badge text-bg-secondary">Bị khóa</span>
                                    @endif
                                </td>
                                <td>{{ $user->orders_count }}</td>
                                <td class="text-end">
                                    @if(auth()->id() !== $user->id)
                                        <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="d-inline"
                                              data-confirm="{{ $user->status ? 'Tài khoản này sẽ bị khóa và không thể đăng nhập. Bạn có chắc chắn?' : 'Tài khoản này sẽ được mở khóa và có thể đăng nhập bình thường. Bạn có chắc chắn?' }}"
                                              data-confirm-title="{{ $user->status ? 'Khóa tài khoản' : 'Mở khóa tài khoản' }}"
                                              data-confirm-label="{{ $user->status ? 'Khóa' : 'Mở khóa' }}"
                                              data-confirm-variant="{{ $user->status ? 'warning' : 'success' }}">
                                            @csrf
                                            @method('PATCH')
                                            @if ($user->status)
                                                <button type="submit" class="btn btn-sm btn-outline-warning">Khóa</button>
                                            @else
                                                <button type="submit" class="btn btn-sm btn-outline-success">Mở khóa</button>
                                            @endif
                                        </form>
                                    @else
                                        <span class="text-muted fst-italic" style="font-size: 0.85rem;">(Bạn)</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-5 text-center text-muted">Chưa có user nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .role-toggle-form .form-check-input {
            width: 2.35rem;
            height: 1.2rem;
            cursor: pointer;
        }

        .role-toggle-form .form-check-input:checked {
            border-color: #dc3545;
            background-color: #dc3545;
        }

        .role-toggle-form .form-check-input:disabled,
        .role-toggle-form .form-check-input:disabled + * {
            cursor: not-allowed;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.querySelectorAll('.role-toggle-form .role-toggle').forEach((toggle) => {
            toggle.addEventListener('change', async () => {
                const form = toggle.closest('form');
                const nextRole = toggle.checked ? 'Admin' : 'Customer';
                const userName = form.dataset.userName;
                const accepted = window.AppConfirm
                    ? await window.AppConfirm.open({
                        title: 'Thay đổi quyền tài khoản',
                        message: `Bạn có chắc muốn chuyển ${userName} thành ${nextRole}?`,
                        confirmLabel: 'Đổi quyền',
                        cancelLabel: 'Hủy',
                        variant: toggle.checked ? 'danger' : 'warning',
                    })
                    : true;

                if (!accepted) {
                    toggle.checked = !toggle.checked;
                    return;
                }

                toggle.disabled = true;
                HTMLFormElement.prototype.submit.call(form);
            });
        });
    </script>
@endpush
