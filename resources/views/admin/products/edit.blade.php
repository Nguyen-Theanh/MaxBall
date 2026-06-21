@extends('admin.layouts.app')

@section('title', 'Sua san pham - MaxBall')
@section('page_title', 'Sua san pham')

@section('content')
    <form method="POST" action="{{ route('admin.products.update', $product) }}">
        @method('PUT')
        @include('admin.products._form', ['submitLabel' => 'Luu thay doi'])
    </form>
@endsection
