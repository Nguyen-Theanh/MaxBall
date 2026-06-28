@extends('admin.layouts.app')

@section('title', 'Sua user - MaxBall')
@section('page_title', 'Sua user')

@section('content')
    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @method('PUT')
        @include('admin.users._form', ['submitLabel' => 'Luu thay doi', 'requirePassword' => false])
    </form>
@endsection
