@extends('admin.layouts.app')

@section('title', 'Quản lý yêu cầu trả hàng & Khiếu nại')

@section('content')
<div class="card mb-4">
    <div class="card-header pb-0 border-b flex justify-between items-center">
        <h6>Quản lý yêu cầu trả hàng & Khiếu nại</h6>
    </div>

    <div class="card-body px-0 pt-0 pb-2">
        <form method="GET" action="{{ route('admin.returns.index') }}" class="px-4 py-3 flex gap-3 flex-wrap">
            <select name="type" class="form-select text-sm w-auto">
                <option value="">Tất cả loại</option>
                <option value="return" {{ request('type') == 'return' ? 'selected' : '' }}>Trả hàng / Hoàn tiền</option>
                <option value="complaint" {{ request('type') == 'complaint' ? 'selected' : '' }}>Khiếu nại</option>
            </select>
            
            <select name="status" class="form-select text-sm w-auto">
                <option value="">Tất cả trạng thái</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Đã giải quyết</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Từ chối</option>
            </select>

            <button type="submit" class="btn bg-gradient-dark mb-0">Lọc</button>
            <a href="{{ route('admin.returns.index') }}" class="btn btn-outline-dark mb-0">Xóa lọc</a>
        </form>

        <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
                <thead>
                    <tr>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Mã Đơn</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Khách Hàng</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Loại</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Lý Do</th>
                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Trạng Thái</th>
                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Ngày Yêu Cầu</th>
                        <th class="text-secondary opacity-7"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $req)
                        <tr>
                            <td>
                                <div class="d-flex px-3 py-1">
                                    <div class="d-flex flex-column justify-content-center">
                                        <h6 class="mb-0 text-sm">#{{ $req->order->order_code }}</h6>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <p class="text-xs font-weight-bold mb-0">{{ $req->user->name }}</p>
                                <p class="text-xs text-secondary mb-0">{{ $req->user->email }}</p>
                            </td>
                            <td>
                                <span class="badge {{ $req->type === 'return' ? 'bg-warning text-dark' : 'bg-danger' }}">
                                    {{ $req->type_label }}
                                </span>
                            </td>
                            <td>
                                <p class="text-xs font-weight-bold mb-0">{{ $req->reason }}</p>
                            </td>
                            <td class="align-middle text-center text-sm">
                                <span class="badge 
                                    @if($req->status == 'pending') bg-secondary
                                    @elseif($req->status == 'processing') bg-info
                                    @elseif($req->status == 'resolved') bg-success
                                    @else bg-danger @endif">
                                    {{ $req->status_label }}
                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <span class="text-secondary text-xs font-weight-bold">{{ $req->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                            <td class="align-middle text-center">
                                <a href="{{ route('admin.returns.show', $req->id) }}" class="text-primary font-weight-bold text-xs" data-toggle="tooltip" data-original-title="Xem chi tiết">
                                    {{ in_array($req->status, ['resolved', 'rejected']) ? 'Xem' : 'Xử lý' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-secondary">Không có dữ liệu.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-4 py-3">
            {{ $returns->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
