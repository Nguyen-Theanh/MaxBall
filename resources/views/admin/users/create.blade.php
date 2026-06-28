@extends('admin.layouts.app')

@section('title', 'Them user - MaxBall')
@section('page_title', 'Them user')

@section('content')
    <form method="POST" action="{{ route('admin.users.store') }}">
        @include('admin.users._form', ['submitLabel' => 'Them user', 'requirePassword' => true])
    </form>
@endsection
