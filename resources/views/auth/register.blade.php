@extends('client.layouts.app')

@section('title', 'Đăng ký - MaxBall')

@section('content')
    <section class="px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-md">
            <div class="rounded-2xl bg-white p-8 shadow-xl shadow-[#10271d]/10 ring-1 ring-black/5">
                <div class="mb-8 text-center">
                    <p class="mb-2 text-sm font-extrabold uppercase tracking-[0.18em] text-[#d92525]">MaxBall</p>
                    <h1 class="text-3xl font-black text-[#10271d]">Tạo tài khỏan</h1>
                    <p class="mt-2 text-sm text-gray-500">Đăng ký nhanh để mua áo và theo dõi đơn hàng.</p>
                </div>

                <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="name" class="mb-2 block text-sm font-bold text-gray-700">Họ tên</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            class="w-full rounded-xl border px-4 py-3 outline-none transition focus:border-[#10271d] focus:ring-4 focus:ring-[#10271d]/10 @error('name') border-red-400 @else border-gray-200 @enderror"
                            autocomplete="name"
                            autofocus
                            required
                        >
                        @error('name')
                            <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="mb-2 block text-sm font-bold text-gray-700">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="w-full rounded-xl border px-4 py-3 outline-none transition focus:border-[#10271d] focus:ring-4 focus:ring-[#10271d]/10 @error('email') border-red-400 @else border-gray-200 @enderror"
                            autocomplete="email"
                            required
                        >
                        @error('email')
                            <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-bold text-gray-700">Mật khẩu</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="w-full rounded-xl border px-4 py-3 outline-none transition focus:border-[#10271d] focus:ring-4 focus:ring-[#10271d]/10 @error('password') border-red-400 @else border-gray-200 @enderror"
                            autocomplete="new-password"
                            required
                        >
                        @error('password')
                            <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-2 block text-sm font-bold text-gray-700">Nhập lại mật khẩu</label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 outline-none transition focus:border-[#10271d] focus:ring-4 focus:ring-[#10271d]/10"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-[#10271d] px-5 py-3 font-extrabold text-white transition hover:bg-[#d92525]">
                        Đăng ký
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-gray-500">
                    Dã có tài khỏan?
                    <a href="{{ route('login') }}" class="font-extrabold text-[#10271d] no-underline hover:text-[#d92525]">Đăng nhập</a>
                </p>
            </div>
        </div>
    </section>
@endsection
