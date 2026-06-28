@extends('client.layouts.app')

@section('title', 'Tài khoản của tôi - MaxBall')

@section('content')
    <section class="px-4 py-12 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8">
                <p class="text-sm font-extrabold uppercase tracking-[0.18em] text-[#d92525]">Account</p>
                <h1 class="mt-2 text-4xl font-black text-[#10271d]">Tài khoản của tôi</h1>
            </div>

            <div class="grid gap-8 lg:grid-cols-[380px_1fr]">
                <form method="POST" action="{{ route('account.update') }}" class="rounded-2xl bg-white p-6 shadow-xl shadow-[#10271d]/10 ring-1 ring-black/5">
                    @csrf
                    @method('PUT')

                    <h2 class="mb-5 text-xl font-black text-[#10271d]">Thông tin giao hàng</h2>

                    <div class="space-y-5">
                        <div>
                            <label for="name" class="mb-2 block text-sm font-bold text-gray-700">Họ tên</label>
                            <input id="name" name="name" value="{{ old('name', $user->name) }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 outline-none focus:border-[#10271d] focus:ring-4 focus:ring-[#10271d]/10" required>
                            @error('name') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="mb-2 block text-sm font-bold text-gray-700">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 outline-none focus:border-[#10271d] focus:ring-4 focus:ring-[#10271d]/10" required>
                            @error('email') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="phone" class="mb-2 block text-sm font-bold text-gray-700">Số điện thoại</label>
                            <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 outline-none focus:border-[#10271d] focus:ring-4 focus:ring-[#10271d]/10">
                            @error('phone') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="address" class="mb-2 block text-sm font-bold text-gray-700">Địa chỉ</label>
                            <textarea id="address" name="address" rows="4" class="w-full rounded-xl border border-gray-200 px-4 py-3 outline-none focus:border-[#10271d] focus:ring-4 focus:ring-[#10271d]/10">{{ old('address', $user->address) }}</textarea>
                            @error('address') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" class="w-full rounded-xl bg-[#10271d] px-5 py-3 font-extrabold text-white transition hover:bg-[#d92525]">
                            Lưu thông tin
                        </button>
                    </div>
                </form>

                <div class="rounded-2xl bg-white p-6 shadow-xl shadow-[#10271d]/10 ring-1 ring-black/5">
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-extrabold uppercase tracking-[0.18em] text-[#d92525]">Orders</p>
                            <h2 class="mt-1 text-2xl font-black text-[#10271d]">Đơn hàng của bạn</h2>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @forelse ($orders as $order)
                            <article class="rounded-xl border border-gray-200 p-4">
                                <div class="flex flex-col justify-between gap-3 md:flex-row md:items-start">
                                    <div>
                                        <h3 class="font-black text-[#10271d]">#{{ $order->order_code }}</h3>
                                        <p class="mt-1 text-sm text-gray-500">{{ $order->created_at?->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <div class="text-left md:text-right">
                                        <p class="text-lg font-black text-[#d92525]">{{ number_format($order->total_amount, 0, ',', '.') }}d</p>
                                        <p class="text-sm font-semibold text-gray-500">{{ $order->payment_method }} - {{ $order->payment_status }}</p>
                                    </div>
                                </div>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span class="rounded-full bg-[#10271d]/10 px-3 py-1 text-xs font-bold text-[#10271d]">{{ $order->order_status }}</span>
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-600">{{ $order->customer_phone }}</span>
                                </div>

                                @if ($order->details->isNotEmpty())
                                    <div class="mt-4 divide-y divide-gray-100">
                                        @foreach ($order->details as $detail)
                                            <div class="flex justify-between gap-4 py-2 text-sm">
                                                <span class="font-semibold text-gray-700">
                                                    {{ $detail->variant?->product?->name ?? 'San pham' }}
                                                    @if ($detail->variant?->name)
                                                        <span class="text-gray-400">({{ $detail->variant->name }})</span>
                                                    @endif
                                                </span>
                                                <span class="shrink-0 text-gray-500">x{{ $detail->quantity }} - {{ number_format($detail->price, 0, ',', '.') }}d</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </article>
                        @empty
                            <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center">
                                <h3 class="font-black text-[#10271d]">Chưa có đơn hàng</h3>
                                <p class="mt-2 text-sm text-gray-500">Khi bạn đặt hàng, lịch sử đơn hàng sẽ hiển thị tại đây.</p>
                                <a href="{{ route('client.products.index') }}" class="mt-5 inline-block rounded-full bg-[#10271d] px-5 py-3 text-sm font-extrabold text-white no-underline hover:bg-[#d92525]">
                                    Mua sắm ngay
                                </a>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-5">
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
