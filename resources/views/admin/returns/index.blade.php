@extends('admin.layouts.app')

@section('title', 'Qu?n l? yêu c?u tr? hàng & Khi?u n?i')

@section('content')
<div class="card mb-4">
    <div class="card-header pb-0 border-b flex justify-between items-center">
        <h6>Qu?n l? yêu c?u tr? hàng & Khi?u n?i</h6>
    </div>

    <div class="card-body px-0 pt-0 pb-2">
        <form method="GET" action="{{ route('admin.returns.index') }}" class="px-4 py-3 flex gap-3 flex-wrap">
            <select name="type" class="form-select text-sm w-auto">
                <option value="">T?t c? lo?i</option>
                <option value="return" {{ request('type') == 'return' ? 'selected' : '' }}>Tr? hàng / Hoàn ti?n</option>
                <option value="complaint" {{ request('type') == 'complaint' ? 'selected' : '' }}>Khi?u n?i</option>
            </select>
            
            <select name="status" class="form-select text-sm w-auto">
                <option value="">T?t c? tr?ng thái</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Ch? x? l?</option>
                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Ðang x? l?</option>
                <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Ð? gi?i quy?t</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>T? ch?i</option>
            </select>

            <button type="submit" class="btn bg-gradient-dark mb-0">L?c</button>
            <a href="{{ route('admin.returns.index') }}" class="btn btn-outline-dark mb-0">Xóa l?c</a>
        </form>

        <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
                <thead>
                    <tr>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">M? Ðõn</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Khách Hàng</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Lo?i</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">L? Do</th>
                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tr?ng Thái</th>
                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Ngày Yêu C?u</th>
                        <th class="text-secondary opacity-7"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse( as )
                        <tr>
                            <td>
                                <div class="d-flex px-3 py-1">
                                    <div class="d-flex flex-column justify-content-center">
                                        <h6 class="mb-0 text-sm">#{{ ->order->order_code }}</h6>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <p class="text-xs font-weight-bold mb-0">{{ ->user->name }}</p>
                                <p class="text-xs text-secondary mb-0">{{ ->user->email }}</p>
                            </td>
                            <td>
                                <span class="badge badge-sm {{ ->type === 'return' ? 'bg-gradient-warning' : 'bg-gradient-danger' }}">
                                    {{ ->type_label }}
                                </span>
                            </td>
                            <td>
                                <p class="text-xs font-weight-bold mb-0">{{ ->reason }}</p>
                            </td>
                            <td class="align-middle text-center text-sm">
                                <span class="badge badge-sm 
                                    @if(->status == 'pending') bg-gradient-secondary
                                    @elseif(->status == 'processing') bg-gradient-info
                                    @elseif(->status == 'resolved') bg-gradient-success
                                    @else bg-gradient-danger @endif">
                                    {{ ->status_label }}
                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <span class="text-secondary text-xs font-weight-bold">{{ ->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                            <td class="align-middle text-center">
                                <a href="{{ route('admin.returns.show', ->id) }}" class="text-primary font-weight-bold text-xs" data-toggle="tooltip" data-original-title="Xem chi ti?t">
                                    X? l?
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-secondary">Không có d? li?u.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-4 py-3">
            {{ ->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
