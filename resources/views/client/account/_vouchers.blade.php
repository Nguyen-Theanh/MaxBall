<!-- TAB: VOUCHERS -->
<div id="tab-vouchers" class="tab-content hidden p-6 md:p-8">
    <div class="mb-6 flex flex-col gap-4 border-b pb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-medium text-gray-900">Voucher Của Tôi</h2>
            <p class="mt-1 text-sm text-gray-500">Theo dõi các voucher bạn đã lưu hoặc được MaxBall tặng.</p>
        </div>
        <div class="rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800 ring-1 ring-amber-200">
            <span class="font-black">{{ $activeVoucherCount }}</span> voucher có thể sử dụng
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        @forelse($userVouchers as $userVoucher)
            @php
                $coupon = $userVoucher->coupon;
                $isAvailable = $userVoucher->is_available;
            @endphp

            <article class="relative overflow-hidden rounded-2xl border {{ $isAvailable ? 'border-amber-200 bg-amber-50/40' : 'border-gray-200 bg-gray-50' }}">
                <div class="absolute inset-y-0 left-0 w-1.5 {{ $isAvailable ? 'bg-amber-500' : 'bg-gray-300' }}"></div>

                <div class="p-5 pl-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex min-w-0 items-start gap-3">
                            <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl {{ $isAvailable ? 'bg-amber-100 text-amber-600' : 'bg-gray-200 text-gray-500' }}">
                                <i class="fa-solid {{ $coupon->discount_type === 'freeship' ? 'fa-truck-fast' : 'fa-ticket' }}"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-black text-gray-900">
                                    @if($coupon->discount_type === 'freeship')
                                        Miễn phí vận chuyển
                                    @elseif($coupon->discount_type === 'fixed')
                                        Giảm {{ number_format($coupon->discount_value, 0, ',', '.') }}đ
                                    @else
                                        Giảm {{ number_format($coupon->discount_value, 0, ',', '.') }}%
                                    @endif
                                </h3>
                                @if($coupon->discount_type === 'percent' && $coupon->max_discount_amount)
                                    <p class="mt-1 text-xs font-semibold text-gray-500">Giảm tối đa {{ number_format($coupon->max_discount_amount, 0, ',', '.') }}đ</p>
                                @endif
                                <p class="mt-1 truncate font-mono text-sm font-bold text-[#d92525]">{{ $coupon->code }}</p>
                            </div>
                        </div>

                        <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold {{ $isAvailable ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                            {{ $userVoucher->status_label }}
                        </span>
                    </div>

                    @if($coupon->description)
                        <p class="mt-4 text-sm text-gray-600">{{ $coupon->description }}</p>
                    @endif

                    <div class="mt-4 grid grid-cols-2 gap-3 border-t border-dashed border-gray-200 pt-4 text-xs text-gray-500">
                        <div>
                            <p>Đơn tối thiểu</p>
                            <p class="mt-1 font-bold text-gray-800">
                                {{ $coupon->min_order_value > 0 ? number_format($coupon->min_order_value, 0, ',', '.').'đ' : 'Không yêu cầu' }}
                            </p>
                        </div>
                        <div>
                            <p>Hạn sử dụng</p>
                            <p class="mt-1 font-bold text-gray-800">
                                {{ $coupon->expires_at ? $coupon->expires_at->format('d/m/Y H:i') : 'Không thời hạn' }}
                            </p>
                        </div>
                    </div>

                    @if($userVoucher->is_used && $userVoucher->used_at)
                        <p class="mt-3 text-xs text-gray-400">Đã dùng lúc {{ $userVoucher->used_at->format('H:i d/m/Y') }}</p>
                    @elseif($isAvailable)
                        <p class="mt-3 text-xs font-medium text-green-700">Voucher sẽ xuất hiện trong danh sách ưu đãi khi bạn thanh toán.</p>
                    @endif
                </div>
            </article>
        @empty
            <div class="col-span-full flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-200 py-16 text-center">
                <div class="grid h-20 w-20 place-items-center rounded-full bg-gray-50 text-3xl text-gray-300">
                    <i class="fa-solid fa-ticket"></i>
                </div>
                <h3 class="mt-4 font-bold text-gray-700">Bạn chưa có voucher nào</h3>
                <p class="mt-1 max-w-md text-sm text-gray-500">Đánh giá sản phẩm đã mua để nhận voucher freeship dùng một lần và không thời hạn.</p>
            </div>
        @endforelse
    </div>

    @if($userVouchers->hasPages())
        <div class="mt-6">
            {{ $userVouchers->onEachSide(1)->links('pagination::tailwind') }}
        </div>
    @endif
</div>
