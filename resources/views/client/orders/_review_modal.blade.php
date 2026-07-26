<div id="product-review-modal" class="fixed inset-0 z-[110] hidden items-center justify-center bg-black/60 px-4 py-8">
    <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-2xl">
        <div class="flex items-start justify-between border-b px-6 py-4">
            <div>
                <h2 class="text-xl font-black text-gray-900">Đánh giá sản phẩm</h2>
                <p id="review-product-name" class="mt-1 font-bold text-gray-700"></p>
                <p id="review-product-variant" class="mt-1 text-sm text-gray-500"></p>
                <p class="mt-1 text-xs text-gray-400">Đơn hàng <span id="review-order-code"></span></p>
            </div>
            <button type="button" data-close-product-review class="text-2xl leading-none text-gray-400 hover:text-gray-700">&times;</button>
        </div>

        <form id="product-review-form" method="POST" enctype="multipart/form-data" class="space-y-5 p-6">
            @csrf
            <input type="hidden" name="form_context" value="review">
            <input type="hidden" name="review_context" value="{{ request()->routeIs('account.show') ? 'account' : (request()->routeIs('client.orders.index') ? 'orders.index' : 'orders.show') }}">
            <input type="hidden" name="review_order_detail_id" id="review-order-detail-id" value="{{ old('review_order_detail_id') }}">
            <input type="hidden" name="rating" id="review-rating" value="{{ old('rating') }}">

            <div>
                <p class="mb-2 text-sm font-bold text-gray-700">Mức độ hài lòng <span class="text-red-600">*</span></p>
                <div class="flex gap-2" id="review-stars" role="radiogroup" aria-label="Chọn số sao đánh giá">
                    @for($rating = 1; $rating <= 5; $rating++)
                        <button type="button"
                                data-review-star="{{ $rating }}"
                                class="text-4xl leading-none text-gray-300 transition hover:scale-110 hover:text-yellow-400"
                                role="radio"
                                aria-checked="false"
                                aria-label="{{ $rating }} sao">★</button>
                    @endfor
                </div>
                <p id="review-rating-label" class="mt-2 text-sm font-medium text-gray-500">Vui lòng chọn từ 1 đến 5 sao.</p>
                @error('rating')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="review-content" class="mb-2 block text-sm font-bold text-gray-700">Nhận xét của bạn</label>
                <textarea id="review-content" name="content" rows="5" maxlength="1000" class="w-full rounded-xl border border-gray-300 px-4 py-3 outline-none focus:border-yellow-500 focus:ring-4 focus:ring-yellow-500/10" placeholder="Chia sẻ cảm nhận về sản phẩm, chất lượng, kích thước...">{{ old('content') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Không bắt buộc, tối đa 1.000 ký tự.</p>
                @error('content')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="review-media" class="mb-2 block text-sm font-bold text-gray-700">Ảnh hoặc video thực tế</label>
                <label for="review-media" class="flex cursor-pointer items-center justify-center gap-3 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-5 text-center transition hover:border-yellow-500 hover:bg-yellow-50">
                    <span class="text-2xl">📷</span>
                    <span>
                        <span class="block text-sm font-bold text-gray-700">Chọn ảnh hoặc video</span>
                        <span class="mt-1 block text-xs text-gray-500">Tối đa 5 tệp · Ảnh 5 MB · Video 50 MB</span>
                    </span>
                </label>
                <input
                    type="file"
                    id="review-media"
                    name="media[]"
                    class="sr-only"
                    accept=".jpg,.jpeg,.png,.webp,.mp4,.mov,.webm,image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm"
                    multiple
                >
                <p class="mt-2 text-xs text-gray-500">Định dạng: JPG, PNG, WebP, MP4, MOV hoặc WebM.</p>
                <p id="review-media-error" class="mt-2 hidden text-xs font-medium text-red-600"></p>
                @if($errors->has('media') || $errors->has('media.*'))
                    <p class="mt-2 text-xs font-medium text-red-600">
                        {{ $errors->first('media') ?: $errors->first('media.*') }}
                    </p>
                @endif
                <div id="review-media-preview" class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3"></div>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" data-close-product-review class="rounded-xl border border-gray-300 px-5 py-2.5 font-bold text-gray-700 hover:bg-gray-50">
                    Quay lại
                </button>
                <button type="submit" class="rounded-xl bg-yellow-500 px-5 py-2.5 font-bold text-gray-950 hover:bg-yellow-400">
                    Gửi đánh giá
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('product-review-modal');
    const form = document.getElementById('product-review-form');
    const detailIdInput = document.getElementById('review-order-detail-id');
    const ratingInput = document.getElementById('review-rating');
    const ratingLabel = document.getElementById('review-rating-label');
    const contentInput = document.getElementById('review-content');
    const mediaInput = document.getElementById('review-media');
    const mediaPreview = document.getElementById('review-media-preview');
    const mediaError = document.getElementById('review-media-error');
    const productName = document.getElementById('review-product-name');
    const productVariant = document.getElementById('review-product-variant');
    const orderCode = document.getElementById('review-order-code');
    const stars = [...document.querySelectorAll('[data-review-star]')];
    const ratingLabels = {
        1: 'Rất không hài lòng',
        2: 'Không hài lòng',
        3: 'Bình thường',
        4: 'Hài lòng',
        5: 'Rất hài lòng',
    };
    const imageExtensions = new Set(['jpg', 'jpeg', 'png', 'webp']);
    const videoExtensions = new Set(['mp4', 'mov', 'webm']);
    let mediaPreviewUrls = [];

    const clearMediaPreview = () => {
        mediaPreviewUrls.forEach((url) => URL.revokeObjectURL(url));
        mediaPreviewUrls = [];
        mediaPreview.innerHTML = '';
    };

    const validateMediaFiles = (files) => {
        if (files.length > 5) {
            return 'Mỗi đánh giá chỉ được chọn tối đa 5 ảnh hoặc video.';
        }

        for (const file of files) {
            const extension = file.name.split('.').pop()?.toLowerCase() || '';
            const isImage = imageExtensions.has(extension);
            const isVideo = videoExtensions.has(extension);

            if (!isImage && !isVideo) {
                return 'Chỉ chấp nhận ảnh JPG, PNG, WebP hoặc video MP4, MOV, WebM.';
            }

            if (isImage && file.size > 5 * 1024 * 1024) {
                return `Ảnh "${file.name}" vượt quá giới hạn 5 MB.`;
            }

            if (isVideo && file.size > 50 * 1024 * 1024) {
                return `Video "${file.name}" vượt quá giới hạn 50 MB.`;
            }
        }

        return null;
    };

    const showMediaError = (message = '') => {
        mediaError.textContent = message;
        mediaError.classList.toggle('hidden', !message);
    };

    const renderMediaPreview = (files) => {
        clearMediaPreview();

        files.forEach((file) => {
            const extension = file.name.split('.').pop()?.toLowerCase() || '';
            const url = URL.createObjectURL(file);
            mediaPreviewUrls.push(url);

            const item = document.createElement('div');
            item.className = 'overflow-hidden rounded-xl border border-gray-200 bg-gray-50';

            const media = document.createElement(imageExtensions.has(extension) ? 'img' : 'video');
            media.src = url;
            media.className = 'h-24 w-full object-cover';

            if (media.tagName === 'VIDEO') {
                media.controls = true;
                media.preload = 'metadata';
            } else {
                media.alt = file.name;
            }

            const name = document.createElement('p');
            name.className = 'truncate px-2 py-1.5 text-[11px] text-gray-600';
            name.textContent = file.name;

            item.append(media, name);
            mediaPreview.appendChild(item);
        });
    };

    const setRating = (value) => {
        const rating = Number(value) || 0;
        ratingInput.value = rating || '';
        ratingLabel.textContent = rating ? `${rating}/5 - ${ratingLabels[rating]}` : 'Vui lòng chọn từ 1 đến 5 sao.';
        if (rating) {
            ratingLabel.classList.remove('text-red-600');
        }

        stars.forEach((star) => {
            const active = Number(star.dataset.reviewStar) <= rating;
            star.classList.toggle('text-yellow-400', active);
            star.classList.toggle('text-gray-300', !active);
            star.setAttribute('aria-checked', Number(star.dataset.reviewStar) === rating ? 'true' : 'false');
        });
    };

    const openModal = (trigger, preserveValues = false) => {
        if (!preserveValues) {
            form.reset();
            contentInput.value = '';
            setRating(0);
            clearMediaPreview();
            showMediaError();
        }

        form.action = trigger.dataset.reviewAction;
        detailIdInput.value = trigger.dataset.orderDetailId;
        productName.textContent = trigger.dataset.productName;
        productVariant.textContent = trigger.dataset.productVariant ? `Phân loại: ${trigger.dataset.productVariant}` : '';
        orderCode.textContent = `#${trigger.dataset.orderCode}`;

        if (preserveValues) {
            setRating(ratingInput.value);
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    document.querySelectorAll('[data-product-review]').forEach((trigger) => {
        trigger.addEventListener('click', () => openModal(trigger));
    });

    document.querySelectorAll('[data-close-product-review]').forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    stars.forEach((star) => {
        star.addEventListener('click', () => setRating(star.dataset.reviewStar));
    });

    mediaInput.addEventListener('change', () => {
        const files = [...mediaInput.files];
        const error = validateMediaFiles(files);

        if (error) {
            mediaInput.value = '';
            clearMediaPreview();
            showMediaError(error);
            return;
        }

        showMediaError();
        renderMediaPreview(files);
    });

    form.addEventListener('submit', (event) => {
        const mediaValidationError = validateMediaFiles([...mediaInput.files]);

        if (!ratingInput.value) {
            event.preventDefault();
            ratingLabel.textContent = 'Vui lòng chọn số sao trước khi gửi.';
            ratingLabel.classList.add('text-red-600');
        }

        if (mediaValidationError) {
            event.preventDefault();
            showMediaError(mediaValidationError);
        }
    });

    const restoredDetailId = @json(old('review_order_detail_id'));
    if (restoredDetailId) {
        const restoredTrigger = document.querySelector(`[data-product-review][data-order-detail-id="${restoredDetailId}"]`);
        if (restoredTrigger) {
            openModal(restoredTrigger, true);
        }
    }
});
</script>
