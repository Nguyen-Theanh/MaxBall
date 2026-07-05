@extends('admin.layouts.app')

@section('title', 'Them user - MaxBall')
@section('page_title', 'Thêm user')

@section('content')
    <form method="POST" action="{{ route('admin.users.store') }}">
        @include('admin.users._form', ['submitLabel' => 'Thêm user', 'requirePassword' => true])
    </form>
@endsection
