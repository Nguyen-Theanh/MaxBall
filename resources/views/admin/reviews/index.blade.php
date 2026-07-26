@extends('admin.layouts.app')

@section('title', 'Quản lý đánh giá - MaxBall')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Quản lý đánh giá</h1>
            <p class="mt-1 text-sm text-gray-500">Kiểm duyệt đánh giá của khách hàng và đăng nội dung dưới tên thương hiệu MaxBall.</p>
        </div>
        <a href="{{ route('client.products.index') }}" target="_blank" class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
            Xem trang sản phẩm
        </a>
    </div>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Tổng đánh giá</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $statistics['total'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Đang hiển thị</p>
            <p class="mt-2 text-3xl font-bold text-green-600">{{ $statistics['visible'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Đã ẩn</p>
            <p class="mt-2 text-3xl font-bold text-gray-500">{{ $statistics['hidden'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Từ MaxBall</p>
            <p class="mt-2 text-3xl font-bold text-blue-600">{{ $statistics['admin'] }}</p>
        </div>
    </div>

    <details class="overflow-hidden rounded-2xl bg-white shadow-sm" @if($errors->any() || old('product_id')) open @endif>
        <summary class="flex cursor-pointer list-none items-center justify-between px-6 py-5">
            <div>
                <h2 class="font-bold text-gray-900">Đăng đánh giá ẩn danh</h2>
                <p class="mt-1 text-sm text-gray-500">Tên tài khoản quản trị sẽ không xuất hiện trên trang khách hàng.</p>
            </div>
            <span class="rounded-lg bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700">+ Tạo đánh giá</span>
        </summary>

        <form method="POST" action="{{ route('admin.reviews.store') }}" enctype="multipart/form-data" class="border-t border-gray-100 p-6">
            @csrf

            <div class="grid gap-5 lg:grid-cols-2">
                <div>
                    <label for="product_id" class="mb-2 block text-sm font-semibold text-gray-700">Sản phẩm <span class="text-red-600">*</span></label>
                    <select id="product_id" name="product_id" class="w-full rounded-xl border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500" required>
                        <option value="">Chọn sản phẩm</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected((string) old('product_id') === (string) $product->id)>
                                {{ $product->name }}{{ $product->status ? '' : ' (đang ẩn)' }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="rating" class="mb-2 block text-sm font-semibold text-gray-700">Số sao <span class="text-red-600">*</span></label>
                    <select id="rating" name="rating" class="w-full rounded-xl border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500" required>
                        <option value="">Chọn mức đánh giá</option>
                        @for($rating = 5; $rating >= 1; $rating--)
                            <option value="{{ $rating }}" @selected((string) old('rating') === (string) $rating)>
                                {{ str_repeat('★', $rating) }} {{ $rating }}/5
                            </option>
                        @endfor
                    </select>
                    @error('rating')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="lg:col-span-2">
                    <label for="public_name" class="mb-2 block text-sm font-semibold text-gray-700">Tên hiển thị công khai <span class="text-red-600">*</span></label>
                    <input
                        id="public_name"
                        name="public_name"
                        value="{{ old('public_name') }}"
                        maxlength="50"
                        class="w-full rounded-xl border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Ví dụ: Nguyễn Minh Anh"
                        required
                    >
                    <p class="mt-1 text-xs text-gray-500">Admin tự chọn tên hiển thị; không nhập tên hoặc email của tài khoản quản trị.</p>
                    @error('public_name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="lg:col-span-2">
                    <label for="content" class="mb-2 block text-sm font-semibold text-gray-700">Nội dung đánh giá</label>
                    <textarea id="content" name="content" rows="4" maxlength="1000" class="w-full rounded-xl border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Chia sẻ thông tin hữu ích về sản phẩm...">{{ old('content') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Tối đa 1.000 ký tự.</p>
                    @error('content')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="lg:col-span-2">
                    <label for="media" class="mb-2 block text-sm font-semibold text-gray-700">Ảnh hoặc video</label>
                    <input
                        id="media"
                        type="file"
                        name="media[]"
                        accept=".jpg,.jpeg,.png,.webp,.mp4,.mov,.webm,image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm"
                        multiple
                        class="block w-full rounded-xl border border-gray-300 bg-white text-sm text-gray-600 file:mr-4 file:border-0 file:bg-blue-50 file:px-4 file:py-3 file:font-semibold file:text-blue-700 hover:file:bg-blue-100"
                    >
                    <p class="mt-2 text-xs text-gray-500">Tối đa 5 tệp. Ảnh tối đa 5 MB; video tối đa 50 MB.</p>
                    @if($errors->has('media') || $errors->has('media.*'))
                        <p class="mt-1 text-xs text-red-600">{{ $errors->first('media') ?: $errors->first('media.*') }}</p>
                    @endif
                </div>
            </div>

            <div class="mt-5 flex flex-col gap-4 rounded-xl bg-blue-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-bold text-blue-900">Danh tính công khai sẽ dùng tên admin nhập ở trên</p>
                    <p class="mt-1 text-xs text-blue-700">Khách hàng không nhìn thấy tên, email hoặc dấu hiệu đây là tài khoản admin.</p>
                </div>
                <label class="inline-flex items-center gap-2 text-sm font-semibold text-gray-700">
                    <input type="hidden" name="is_visible" value="0">
                    <input type="checkbox" name="is_visible" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" @checked(old('is_visible', '1') === '1')>
                    Hiển thị ngay
                </label>
            </div>

            <div class="mt-5 flex justify-end">
                <button type="submit" class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-blue-700">
                    Đăng đánh giá
                </button>
            </div>
        </form>
    </details>

    <div class="rounded-2xl bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Sản phẩm, khách hàng, nội dung..." class="rounded-xl border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500 xl:col-span-2">

            <select name="visibility" class="rounded-xl border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Mọi trạng thái</option>
                <option value="visible" @selected(request('visibility') === 'visible')>Đang hiển thị</option>
                <option value="hidden" @selected(request('visibility') === 'hidden')>Đã ẩn</option>
            </select>

            <select name="source" class="rounded-xl border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Mọi nguồn</option>
                <option value="customer" @selected(request('source') === 'customer')>Khách hàng</option>
                <option value="admin" @selected(request('source') === 'admin')>MaxBall</option>
            </select>

            <div class="flex gap-2">
                <select name="rating" class="min-w-0 flex-1 rounded-xl border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Mọi số sao</option>
                    @for($rating = 5; $rating >= 1; $rating--)
                        <option value="{{ $rating }}" @selected((string) request('rating') === (string) $rating)>{{ $rating }} sao</option>
                    @endfor
                </select>
                <button class="rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-black">Lọc</button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Sản phẩm</th>
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Người đánh giá</th>
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Đánh giá</th>
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Media</th>
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Trạng thái</th>
                        <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-gray-500">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($reviews as $review)
                        <tr class="{{ $review->is_visible ? '' : 'bg-gray-50 opacity-75' }}">
                            <td class="px-5 py-4 align-top">
                                <a href="{{ route('client.products.show', $review->product?->slug) }}" target="_blank" class="font-semibold text-blue-600 hover:text-blue-800">
                                    {{ $review->product?->name ?? 'Sản phẩm đã xóa' }}
                                </a>
                                @if($review->orderDetail?->variant)
                                    <p class="mt-1 text-xs text-gray-500">{{ $review->orderDetail->variant->name }}</p>
                                @endif
                                <p class="mt-1 text-xs text-gray-400">{{ $review->created_at->format('d/m/Y H:i') }}</p>
                            </td>
                            <td class="px-5 py-4 align-top">
                                @if($review->is_admin_review)
                                    <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700">{{ $review->public_name ?: 'Khách hàng MaxBall' }}</span>
                                    <p class="mt-2 text-xs text-gray-400">Nội bộ: {{ $review->user?->name }}</p>
                                @else
                                    <p class="font-semibold text-gray-800">{{ $review->user?->name ?? 'Khách hàng' }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ $review->user?->email }}</p>
                                    @if($review->order_detail_id)
                                        <span class="mt-2 inline-flex rounded-full bg-green-50 px-2 py-1 text-[11px] font-bold text-green-700">Đã mua hàng</span>
                                    @endif
                                @endif
                            </td>
                            <td class="max-w-md px-5 py-4 align-top">
                                <div class="text-lg text-yellow-400">{{ str_repeat('★', $review->rating) }}<span class="text-gray-300">{{ str_repeat('★', 5 - $review->rating) }}</span></div>
                                <p class="mt-2 line-clamp-3 whitespace-pre-line text-sm leading-6 text-gray-600">{{ $review->content ?: 'Không có nội dung nhận xét.' }}</p>
                            </td>
                            <td class="px-5 py-4 align-top">
                                @if($review->media->isNotEmpty())
                                    <div class="flex max-w-48 flex-wrap gap-2">
                                        @foreach($review->media->take(4) as $media)
                                            @if($media->type === 'video')
                                                <video src="{{ $media->url }}" class="h-12 w-12 rounded-lg bg-black object-cover" preload="metadata"></video>
                                            @else
                                                <img src="{{ $media->url }}" alt="Media đánh giá" class="h-12 w-12 rounded-lg border object-cover">
                                            @endif
                                        @endforeach
                                        @if($review->media->count() > 4)
                                            <span class="flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 text-xs font-bold text-gray-600">+{{ $review->media->count() - 4 }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">Không có</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 align-top">
                                @if($review->is_visible)
                                    <span class="inline-flex rounded-full bg-green-50 px-2.5 py-1 text-xs font-bold text-green-700">Đang hiển thị</span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-200 px-2.5 py-1 text-xs font-bold text-gray-600">Đã ẩn</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right align-top">
                                <form method="POST" action="{{ route('admin.reviews.visibility', $review) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_visible" value="{{ $review->is_visible ? 0 : 1 }}">
                                    <button type="submit" class="rounded-lg border px-3 py-2 text-xs font-bold {{ $review->is_visible ? 'border-gray-300 text-gray-600 hover:bg-gray-50' : 'border-green-300 text-green-700 hover:bg-green-50' }}">
                                        {{ $review->is_visible ? 'Ẩn đánh giá' : 'Hiện đánh giá' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-sm text-gray-500">Chưa có đánh giá phù hợp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reviews->hasPages())
            <div class="border-t border-gray-100 px-5 py-4">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
