@php
    $isEditing = isset($promotionAnnouncement);
@endphp

<div class="space-y-6 p-6 md:p-8">
    <div>
        <label for="promotion-title" class="mb-2 block text-sm font-semibold text-gray-700">
            Tiêu đề <span class="text-red-500">*</span>
        </label>
        <input
            id="promotion-title"
            type="text"
            name="title"
            maxlength="120"
            required
            value="{{ old('title', $promotionAnnouncement->title ?? '') }}"
            class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-900 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
            placeholder="Ví dụ: Ưu đãi dành cho bạn"
        >
        @error('title')
            <p class="mt-1 text-xs font-medium text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="promotion-content" class="mb-2 block text-sm font-semibold text-gray-700">
            Nội dung khuyến mãi <span class="text-red-500">*</span>
        </label>
        <textarea
            id="promotion-content"
            name="content"
            rows="8"
            maxlength="3000"
            required
            class="w-full resize-y rounded-xl border border-gray-300 px-4 py-3 text-gray-900 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
            placeholder="Nhập chương trình khuyến mãi đang áp dụng..."
        >{{ old('content', $promotionAnnouncement->content ?? '') }}</textarea>
        <p class="mt-2 text-xs text-gray-500">Có thể xuống dòng để chia nội dung thành nhiều ý. Tối đa 3.000 ký tự.</p>
        @error('content')
            <p class="mt-1 text-xs font-medium text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-blue-100 bg-blue-50 p-4">
        <input
            type="checkbox"
            name="is_active"
            value="1"
            @checked(old('is_active', $promotionAnnouncement->is_active ?? true))
            class="mt-0.5 h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
        >
        <span>
            <span class="block text-sm font-semibold text-gray-800">Hiển thị thông báo ngoài cửa hàng</span>
            <span class="mt-1 block text-xs text-gray-500">Các thông báo đang bật sẽ được khách xem lần lượt trong hộp khuyến mãi.</span>
        </span>
    </label>
</div>

<div class="flex justify-end gap-3 border-t border-gray-100 bg-gray-50 px-6 py-4 md:px-8">
    <a href="{{ route('admin.promotion-announcements.index') }}" class="rounded-xl border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
        Hủy
    </a>
    <button type="submit" class="rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
        {{ $isEditing ? 'Lưu thay đổi' : 'Thêm thông báo' }}
    </button>
</div>
