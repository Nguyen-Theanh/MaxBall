@extends('admin.layouts.app')

@section('title', 'Thêm thông báo khuyến mãi - Admin MaxBall')

@section('content')
<div class="mx-auto max-w-5xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Thêm thông báo khuyến mãi</h2>
        <p class="mt-1 text-sm text-gray-500">Tạo nội dung mới để khách xem trong hộp quà ngoài cửa hàng.</p>
    </div>

    <form action="{{ route('admin.promotion-announcements.store') }}" method="POST" class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        @csrf
        @include('admin.promotion-announcements._form')
    </form>
</div>
@endsection
