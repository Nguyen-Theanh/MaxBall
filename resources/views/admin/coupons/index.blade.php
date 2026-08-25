@extends('admin.layouts.app')

@section('content')
<div class="px-8 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Quản Lý Voucher</h2>
            <p class="text-sm text-gray-500 mt-1">Quản lý các mã giảm giá cho khách hàng</p>
        </div>
        <a href="{{ route('admin.coupons.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Thêm Voucher
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="py-3 px-4 font-semibold text-sm text-gray-600">Mã (Code)</th>
                        <th class="py-3 px-4 font-semibold text-sm text-gray-600">Giảm giá</th>
                        <th class="py-3 px-4 font-semibold text-sm text-gray-600">Đơn Tối Thiểu</th>
                        <th class="py-3 px-4 font-semibold text-sm text-gray-600">Số lượng (Đã dùng)</th>
                        <th class="py-3 px-4 font-semibold text-sm text-gray-600">Hiệu lực</th>
                        <th class="py-3 px-4 font-semibold text-sm text-gray-600">Trạng thái</th>
                        <th class="py-3 px-4 font-semibold text-sm text-gray-600 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($coupons as $coupon)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-4">
                                <span class="font-bold text-blue-600">{{ $coupon->code }}</span>
                            </td>
                            <td class="py-3 px-4 font-medium text-red-600">
                                @if($coupon->discount_type == 'freeship')
                                    Miễn phí vận chuyển
                                @elseif($coupon->discount_type == 'fixed')
                                    Giảm {{ number_format($coupon->discount_value, 0, ',', '.') }}đ
                                @else
                                    Giảm {{ $coupon->discount_value }}%
                                @endif
                            </td>
                            <td class="py-3 px-4 text-gray-600 text-sm">
                                {{ $coupon->min_order_value ? number_format($coupon->min_order_value, 0, ',', '.') . 'đ' : 'Không có' }}
                            </td>
                            <td class="py-3 px-4 text-gray-600 text-sm">
                                {{ $coupon->usage_limit ?: 'Vô hạn' }} ({{ $coupon->used_count }})
                            </td>
                            <td class="py-3 px-4 text-gray-500 text-xs">
                                <div>Từ: {{ $coupon->start_date ? $coupon->start_date->format('d/m/Y H:i') : 'Ngay lập tức' }}</div>
                                <div>Đến: {{ $coupon->expires_at ? $coupon->expires_at->format('d/m/Y H:i') : 'Mãi mãi' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <form action="{{ route('admin.coupons.toggle-status', $coupon->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none {{ $coupon->status ? 'bg-green-500' : 'bg-gray-300' }}">
                                        <span class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform {{ $coupon->status ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                </form>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Sửa">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                    <form action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa voucher này không?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Xóa">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-500">
                                Không có mã giảm giá nào được tìm thấy.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($coupons->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $coupons->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
