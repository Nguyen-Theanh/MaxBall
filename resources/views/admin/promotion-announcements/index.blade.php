@extends('admin.layouts.app')

@section('title', 'Thông báo khuyến mãi - Admin MaxBall')

@section('content')
<div class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Thông báo khuyến mãi</h2>
            <p class="mt-1 text-sm text-gray-500">Quản lý các thông báo hiện trong hộp quà ngoài cửa hàng.</p>
        </div>
        <a href="{{ route('admin.promotion-announcements.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14"/></svg>
            Thêm thông báo
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left">
                <thead class="border-b border-gray-100 bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Thông báo</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Trạng thái</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Cập nhật</th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-gray-500">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($promotionAnnouncements as $announcement)
                        <tr class="transition hover:bg-gray-50/70">
                            <td class="max-w-xl px-6 py-4">
                                <p class="font-bold text-gray-900">{{ $announcement->title }}</p>
                                <p class="mt-1 line-clamp-2 text-sm leading-6 text-gray-500">{{ $announcement->content }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <form action="{{ route('admin.promotion-announcements.toggle-status', $announcement) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold {{ $announcement->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}" aria-label="{{ $announcement->is_active ? 'Ẩn' : 'Hiện' }} thông báo {{ $announcement->title }}">
                                        <span class="h-2 w-2 rounded-full {{ $announcement->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                        {{ $announcement->is_active ? 'Đang hiển thị' : 'Đang ẩn' }}
                                    </button>
                                </form>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $announcement->updated_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.promotion-announcements.edit', $announcement) }}" class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-100">Sửa</a>
                                    <form
                                        action="{{ route('admin.promotion-announcements.destroy', $announcement) }}"
                                        method="POST"
                                        data-confirm="Bạn có chắc chắn muốn xóa thông báo “{{ $announcement->title }}” không?"
                                        data-confirm-title="Xóa thông báo khuyến mãi"
                                        data-confirm-label="Xóa thông báo"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-100">Xóa</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-14 text-center">
                                <div class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-orange-50 text-orange-500">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12v8H4v-8m16-5H4v5h16V7ZM12 20V7m0 0H8.5a2.5 2.5 0 1 1 2.5-2.5L12 7Zm0 0h3.5A2.5 2.5 0 1 0 13 4.5L12 7Z"/></svg>
                                </div>
                                <p class="mt-3 font-semibold text-gray-700">Chưa có thông báo khuyến mãi</p>
                                <p class="mt-1 text-sm text-gray-500">Hãy thêm thông báo đầu tiên để hiển thị cho khách.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($promotionAnnouncements->hasPages())
            <div class="border-t border-gray-100 px-6 py-4">
                {{ $promotionAnnouncements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
