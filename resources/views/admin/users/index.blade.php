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

                <a href="{{ route('admin.users.create') }}" class="btn btn-primary align-self-start">Thêm user</a>
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
                                    <span class="badge {{ $user->role === 'admin' ? 'text-bg-dark' : 'text-bg-info' }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
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
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Sửa</a>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline"
                                          data-confirm="Tài khoản người dùng này sẽ bị xóa khỏi hệ thống. Bạn có chắc chắn muốn tiếp tục?"
                                          data-confirm-title="Xóa người dùng"
                                          data-confirm-label="Xóa người dùng"
                                          data-confirm-variant="danger">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                    </form>
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
