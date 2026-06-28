@extends('admin.layouts.app')

@section('title', 'Quan ly nguoi dung - MaxBall')
@section('page_title', 'Quan ly nguoi dung')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-3 mb-4">
                <form class="row g-2 flex-grow-1" method="GET" action="{{ route('admin.users.index') }}">
                    <div class="col-12 col-md-5">
                        <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Tim ten, email, so dien thoai...">
                    </div>
                    <div class="col-12 col-md-2">
                        <select name="role" class="form-select">
                            <option value="">Tat ca role</option>
                            <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                            <option value="customer" @selected(request('role') === 'customer')>Customer</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Tat ca trang thai</option>
                            <option value="1" @selected(request('status') === '1')>Dang hoat dong</option>
                            <option value="0" @selected(request('status') === '0')>Bi khoa</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-auto">
                        <button class="btn btn-dark" type="submit">Loc</button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>

                <a href="{{ route('admin.users.create') }}" class="btn btn-primary align-self-start">Them user</a>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Trang thai</th>
                            <th>Don hang</th>
                            <th class="text-end">Thao tac</th>
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
                                        <span class="badge text-bg-success">Hoat dong</span>
                                    @else
                                        <span class="badge text-bg-secondary">Bi khoa</span>
                                    @endif
                                </td>
                                <td>{{ $user->orders_count }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Sua</a>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Xoa user nay?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Xoa</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-5 text-center text-muted">Chua co user nao.</td>
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
