@extends('admin.layouts.app')

@section('title', 'Them san pham - MaxBall')
@section('page_title', 'Thêm sản phẩm')

@section('content')
    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
        @include('admin.products._form', ['submitLabel' => 'Thêm sản phẩm'])
    </form>
@endsection
