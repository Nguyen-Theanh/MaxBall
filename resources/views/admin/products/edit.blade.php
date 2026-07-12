@extends('admin.layouts.app')

@section('title', 'Sửa sản phẩm - MaxBall')
@section('page_title', 'Sửa sản phẩm')

@section('content')
    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
        @method('PUT')
        @include('admin.products._form', ['submitLabel' => 'Lưu thay đổi'])
    </form>
@endsection
