@extends('client.layouts.app')

@section('title', 'Dang nhap - MaxBall')

@section('content')
    <section class="px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-md">
            <div class="rounded-2xl bg-white p-8 shadow-xl shadow-[#10271d]/10 ring-1 ring-black/5">
                <div class="mb-8 text-center">
                    <p class="mb-2 text-sm font-extrabold uppercase tracking-[0.18em] text-[#d92525]">MaxBall</p>
                    <h1 class="text-3xl font-black text-[#10271d]">Đăng nhập</h1>
                    <p class="mt-2 text-sm text-gray-500">Tiếp tục mua sắm và quản lý tài khỏan của bạn.</p>
                </div>

                <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="mb-2 block text-sm font-bold text-gray-700">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="w-full rounded-xl border px-4 py-3 outline-none transition focus:border-[#10271d] focus:ring-4 focus:ring-[#10271d]/10 @error('email') border-red-400 @else border-gray-200 @enderror"
                            autocomplete="email"
                            autofocus
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
                            autocomplete="current-password"
                            required
                        >
                        @error('password')
                            <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-2 text-sm font-semibold text-gray-600">
                        <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-gray-300 text-[#10271d]">
                        Ghi nhớ đăng nhập
                    </label>

                    <button type="submit" class="w-full rounded-xl bg-[#10271d] px-5 py-3 font-extrabold text-white transition hover:bg-[#d92525]">
                        Đăng nhập
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-gray-500">
                    Chưa có tài khoản?
                    <a href="{{ route('register') }}" class="font-extrabold text-[#10271d] no-underline hover:text-[#d92525]">Đăng ký</a>
                </p>
            </div>
        </div>
    </section>
@endsection
