@extends('admin.layouts.app')

@section('title', 'Quản lý rút tiền - MaxBall')
@section('page_title', 'Quản lý yêu cầu rút tiền')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <!-- Filter, Header, etc -->
            <form action="{{ route('admin.withdrawals.index') }}" method="GET" class="mb-4 d-flex gap-2 align-items-center">
                <select name="status" class="form-select w-auto">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Đang chờ duyệt</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Đã từ chối</option>
                </select>
                <button type="submit" class="btn btn-primary">Lọc</button>
            </form>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th class="text-nowrap">Thời gian</th>
                        <th class="text-nowrap">Khách hàng</th>
                        <th class="text-nowrap">Số tiền</th>
                        <th class="text-nowrap">Thông tin ngân hàng</th>
                        <th class="text-nowrap">Trạng thái</th>
                        <th class="text-nowrap text-end">Hành động</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($withdrawRequests as $request)
                        <tr>
                            <td class="text-nowrap">{{ $request->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <strong>{{ $request->user->name }}</strong><br>
                                <span class="text-muted small">{{ $request->user->email }}</span>
                            </td>
                            <td class="text-danger fw-bold">
                                {{ number_format($request->amount, 0, ',', '.') }}đ
                            </td>
                            <td>
                                <div class="small">
                                    Ngân hàng: <strong>{{ $request->bank_name }}</strong><br>
                                    STK: <strong>{{ $request->account_number }}</strong><br>
                                    Chủ thẻ: <strong>{{ $request->account_name }}</strong>
                                </div>
                            </td>
                            <td>
                                @if ($request->status === 'pending')
                                    <span class="badge bg-warning text-dark">Đang chờ</span>
                                @elseif ($request->status === 'approved')
                                    <span class="badge bg-success">Đã duyệt</span>
                                @else
                                    <span class="badge bg-danger">Từ chối</span><br>
                                    <span class="small text-muted" title="{{ $request->admin_note }}">Lý do: {{ \Illuminate\Support\Str::limit($request->admin_note, 20) }}</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                @if ($request->status === 'pending')
                                    <div class="d-flex justify-content-end gap-2">
                                        <!-- Duyệt (Sử dụng AppConfirm) -->
                                        <form action="{{ route('admin.withdrawals.approve', $request->id) }}" method="POST" class="m-0 p-0"
                                              data-confirm="Xác nhận đã chuyển khoản thành công số tiền <strong class='text-danger'>{{ number_format($request->amount, 0, ',', '.') }}đ</strong> cho khách hàng?<br><br>Ngân hàng: <strong>{{ $request->bank_name }}</strong><br>STK: <strong>{{ $request->account_number }}</strong><br>Chủ thẻ: <strong>{{ $request->account_name }}</strong>"
                                              data-confirm-title="Xác nhận chuyển khoản"
                                              data-confirm-label="Đã chuyển khoản"
                                              data-confirm-variant="primary">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="bi bi-check-circle"></i> Duyệt
                                            </button>
                                        </form>

                                        <!-- Từ chối (Mở Custom modal) -->
                                        <button type="button" class="btn btn-sm btn-danger" onclick="openRejectModal({{ $request->id }})">
                                            <i class="bi bi-x-circle"></i> Từ chối
                                        </button>
                                    </div>
                                @else
                                    <button class="btn btn-sm btn-secondary" disabled>Đã xử lý</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Không có yêu cầu nào.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $withdrawRequests->links() }}
            </div>
        </div>
    </div>

    <!-- Modals -->
    @push('modals')
    @foreach($withdrawRequests as $request)
        @if ($request->status === 'pending')

            <!-- Modal Từ Chối -->
            <div id="rejectModal{{ $request->id }}" class="fixed inset-0 z-[1055] hidden items-center justify-center bg-black/60 px-4 py-8">
                <div class="w-full max-w-lg overflow-hidden rounded-xl bg-white shadow-2xl">
                    <div class="d-flex align-items-center justify-content-between border-bottom px-4 py-3">
                        <h5 class="mb-0 fw-bold">Từ chối yêu cầu rút tiền</h5>
                        <button type="button" class="btn-close" aria-label="Đóng" onclick="closeRejectModal({{ $request->id }})"></button>
                    </div>
                    <form action="{{ route('admin.withdrawals.reject', $request->id) }}" method="POST">
                        @csrf
                        <div class="p-4">
                            <div class="alert alert-warning small">
                                Việc từ chối sẽ tự động hoàn lại số tiền <strong>{{ number_format($request->amount, 0, ',', '.') }}đ</strong> vào ví MaxBall của khách hàng.
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Lý do từ chối (bắt buộc)</label>
                                <textarea name="admin_note" class="form-control" rows="3" required placeholder="Ví dụ: Sai thông tin tài khoản ngân hàng..."></textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 bg-light px-4 py-3 border-top">
                            <button type="button" class="btn btn-secondary" onclick="closeRejectModal({{ $request->id }})">Hủy</button>
                            <button type="submit" class="btn btn-danger">Xác nhận từ chối</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endforeach

    <script>
        function openRejectModal(id) {
            const modal = document.getElementById('rejectModal' + id);
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }
        function closeRejectModal(id) {
            const modal = document.getElementById('rejectModal' + id);
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }
    </script>
    @endpush
@endsection
