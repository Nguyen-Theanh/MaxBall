@extends('admin.layouts.app')

@section('title', 'Quản lý Liên Hệ - MaxBall')
@section('page_title', 'Quản lý Liên Hệ')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Khách hàng</th>
                            <th>Số điện thoại</th>
                            <th>Nội dung</th>
                            <th>Ngày gửi</th>
                            <th>Trạng thái</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contacts as $contact)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $contact->name }}</div>
                                    <small class="text-muted">{{ $contact->email }}</small>
                                </td>
                                <td>{{ $contact->phone }}</td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;">
                                        {{ $contact->message }}
                                    </div>
                                </td>
                                <td>{{ $contact->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if ($contact->status === 'resolved')
                                        <span class="badge text-bg-success">Đã xử lý</span>
                                    @else
                                        <span class="badge text-bg-danger">Chưa xử lý</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.contacts.show', $contact) }}" class="btn btn-sm btn-outline-info">Chi tiết</a>
                                    
                                    <form method="POST" action="{{ route('admin.contacts.updateStatus', $contact) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-{{ $contact->status === 'pending' ? 'success' : 'warning' }}">
                                            {{ $contact->status === 'pending' ? 'Đã xử lý' : 'Bỏ xử lý' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.contacts.destroy', $contact) }}" class="d-inline" onsubmit="return confirm('Xóa liên hệ này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-5 text-center text-muted">Chưa có liên hệ nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $contacts->links() }}
            </div>
        </div>
    </div>
@endsection
