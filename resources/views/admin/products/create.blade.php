@extends('admin.layouts.app')

@section('title', 'Them san pham - MaxBall')
@section('page_title', 'Them san pham')

@section('content')
    <form method="POST" action="{{ route('admin.products.store') }}">
        @include('admin.products._form', ['submitLabel' => 'Them san pham'])
    </form>
@endsection
