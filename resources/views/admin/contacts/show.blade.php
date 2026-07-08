@extends('admin.layouts.app')

@section('title', 'Chi tiết Liên Hệ - MaxBall')
@section('page_title', 'Chi tiết Liên Hệ')

@section('content')
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Thông tin liên hệ #{{ $contact->id }}</h5>
                    <a href="{{ route('admin.contacts.index') }}" class="btn btn-sm btn-outline-secondary">Quay lại danh sách</a>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Họ và tên:</strong> {{ $contact->name }}
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong> {{ $contact->email }}
                    </div>
                    <div class="mb-3">
                        <strong>Số điện thoại:</strong> {{ $contact->phone }}
                    </div>
                    <div class="mb-3">
                        <strong>Trạng thái:</strong> 
                        @if ($contact->status === 'resolved')
                            <span class="badge text-bg-success">Đã xử lý</span>
                        @else
                            <span class="badge text-bg-danger">Chưa xử lý</span>
                        @endif
                    </div>
                    <div class="mb-3">
                        <strong>Thời gian gửi:</strong> {{ $contact->created_at->format('d/m/Y H:i:s') }}
                    </div>
                    <hr>
                    <div class="mb-3">
                        <strong>Nội dung khách hàng cần hỗ trợ:</strong>
                        <div class="p-3 bg-light rounded mt-2" style="white-space: pre-wrap; font-size: 1.05rem;">{{ $contact->message }}</div>
                    </div>
                    
                    <div class="d-flex gap-2 mt-4 pt-3 border-top">
                        <form method="POST" action="{{ route('admin.contacts.updateStatus', $contact) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-{{ $contact->status === 'pending' ? 'success' : 'warning' }}">
                                <i class="fa-solid fa-check mr-1"></i> {{ $contact->status === 'pending' ? 'Đánh dấu là Đã Xử Lý' : 'Chuyển về Chưa Xử Lý' }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.contacts.destroy', $contact) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn liên hệ này?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fa-solid fa-trash mr-1"></i> Xóa liên hệ
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
