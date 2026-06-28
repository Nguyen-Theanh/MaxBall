@csrf

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label">Slug</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug', $product->slug) }}" class="form-control @error('slug') is-invalid @enderror" placeholder="De trong se tu tao theo ten">
                    @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Biến thể sản phẩm</label>
                    <div id="variants-container">
                        @php $vIndex = 0; @endphp
                        @foreach(old('variants', $product->variants ?? []) as $v)
                            @php
                                $variant = is_array($v) ? (object) $v : $v;
                            @endphp
                            <div class="card mb-2 variant-row" data-index="{{ $vIndex }}">
                                <div class="card-body p-2">
                                    <input type="hidden" name="variants[{{ $vIndex }}][id]" value="{{ $variant->id ?? '' }}">
                                    <div class="row g-2">
                                        <div class="col-4">
                                            <input type="text" name="variants[{{ $vIndex }}][name]" value="{{ old("variants.$vIndex.name", $variant->name ?? '') }}" class="form-control" placeholder="Tên (size/color)">
                                        </div>
                                        <div class="col-2">
                                            <input type="text" name="variants[{{ $vIndex }}][sku]" value="{{ old("variants.$vIndex.sku", $variant->sku ?? '') }}" class="form-control" placeholder="SKU">
                                        </div>
                                        <div class="col-2">
                                            <input type="number" name="variants[{{ $vIndex }}][base_price]" value="{{ old("variants.$vIndex.base_price", $variant->base_price ?? '') }}" class="form-control" placeholder="Giá">
                                        </div>
                                        <div class="col-2">
                                            <input type="number" name="variants[{{ $vIndex }}][discount_price]" value="{{ old("variants.$vIndex.discount_price", $variant->discount_price ?? '') }}" class="form-control" placeholder="KM">
                                        </div>
                                        <div class="col-1">
                                            <input type="number" name="variants[{{ $vIndex }}][stock]" value="{{ old("variants.$vIndex.stock", $variant->stock ?? '') }}" class="form-control" placeholder="SL">
                                        </div>
                                        <div class="col-1">
                                            <button type="button" class="btn btn-sm btn-danger remove-variant">×</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @php $vIndex++; @endphp
                        @endforeach
                    </div>
                    <button type="button" id="add-variant" class="btn btn-sm btn-outline-secondary mt-2">Thêm biến thể</button>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Mô tả</label>
                    <textarea id="description" name="description" rows="5" class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="thumbnail" class="form-label">Ảnh đại diện</label>
                    <input type="text" id="thumbnail" name="thumbnail" value="{{ old('thumbnail', $product->thumbnail) }}" class="form-control @error('thumbnail') is-invalid @enderror" placeholder="Nhap URL anh hoac duong dan trong storage">
                    @error('thumbnail') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Tải ảnh từ tệp</label>
                    <input type="file" id="image" name="image" accept="image/*" class="form-control @error('image') is-invalid @enderror">
                    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">Không chọn ảnh mới để giữ nguyên ảnh cũ. Nếu upload ảnh mới, ảnh cũ sẽ bị thay thế.</div>
                </div>

                <div class="mb-3">
                    <label for="gallery_images" class="form-label">Ảnh chi tiết</label>
                    <input type="file" id="gallery_images" name="gallery_images[]" multiple accept="image/*" class="form-control @error('gallery_images') is-invalid @enderror @error('gallery_images.*') is-invalid @enderror">
                    @error('gallery_images') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @error('gallery_images.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">Chọn nhiều ảnh để thêm ảnh chi tiết cho sản phẩm.</div>
                </div>

                @if ($product->productImages->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label">Ảnh chi tiết hiện có</label>
                        <div class="row g-2">
                            @foreach ($product->productImages as $galleryImage)
                                <div class="col-6 col-md-4">
                                    <img src="{{ $galleryImage->url }}" alt="Ảnh chi tiết" class="img-fluid rounded border">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($product->thumbnail)
                    <div class="mb-3">
                        <label class="form-label">Ảnh hiện tại</label>
                        <div>
                            <img src="{{ $product->thumbnail_url }}" alt="Ảnh hiện tại" class="img-fluid rounded" style="max-height: 200px;">
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="mb-3">
                    <label for="category_id" class="form-label">Danh mục</label>
                    <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                        <option value="">Chọn danh mục</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) old('category_id', $product->category_id) === $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="category_name" class="form-label">Hoặc tạo danh mục mới</label>
                    <input type="text" id="category_name" name="category_name" value="{{ old('category_name') }}" class="form-control @error('category_name') is-invalid @enderror" placeholder="VD: Training Kit">
                    @error('category_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row g-3" id="product-price-section">
                    <div class="col-12">
                        <label for="base_price" class="form-label">Giá gốc <span class="text-danger">*</span></label>
                        <input type="number" id="base_price" name="base_price" value="{{ old('base_price', $product->base_price) }}" min="0" step="1000" class="form-control @error('base_price') is-invalid @enderror" required>
                        @error('base_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label for="discount_price" class="form-label">Giá khuyến mãi</label>
                        <input type="number" id="discount_price" name="discount_price" value="{{ old('discount_price', $product->discount_price) }}" min="0" step="1000" class="form-control @error('discount_price') is-invalid @enderror">
                        @error('discount_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-check form-switch mt-4">
                    <input type="checkbox" id="status" name="status" value="1" class="form-check-input" @checked(old('status', $product->status ?? true))>
                    <label for="status" class="form-check-label">Hiển thị ngoài client</label>
                </div>
            </div>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg">{{ $submitLabel }}</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameField = document.getElementById('name');
    const slugField = document.getElementById('slug');
    if (!nameField || !slugField) return;

    const toSlug = (str) => {
        return str
            .normalize('NFD')
            .replace(/\p{Diacritic}/gu, '')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    };

    nameField.addEventListener('input', function() {
        // only update slug automatically if user hasn't manually edited slug
        if (!slugField.dataset.userEdited) {
            slugField.value = toSlug(this.value);
        }
    });

    slugField.addEventListener('input', function() {
        this.dataset.userEdited = this.value.length > 0;
    });

    // Variants JS
    const variantsContainer = document.getElementById('variants-container');
    const addVariantBtn = document.getElementById('add-variant');
    let variantIndex = variantsContainer ? (function() {
        const els = variantsContainer.querySelectorAll('.variant-row');
        return els.length ? Number(els[els.length - 1].dataset.index) + 1 : 0;
    })() : 0;

    function createVariantRow(index, data = {}) {
        const div = document.createElement('div');
        div.className = 'card mb-2 variant-row';
        div.dataset.index = index;
        div.innerHTML = `
            <div class="card-body p-2">
                <input type="hidden" name="variants[${index}][id]" value="${data.id || ''}">
                <div class="row g-2">
                    <div class="col-4">
                        <input type="text" name="variants[${index}][name]" value="${data.name || ''}" class="form-control" placeholder="Tên (size/color)">
                    </div>
                    <div class="col-2">
                        <input type="text" name="variants[${index}][sku]" value="${data.sku || ''}" class="form-control" placeholder="SKU">
                    </div>
                    <div class="col-2">
                        <input type="number" name="variants[${index}][base_price]" value="${data.base_price || ''}" class="form-control" placeholder="Giá">
                    </div>
                    <div class="col-2">
                        <input type="number" name="variants[${index}][discount_price]" value="${data.discount_price || ''}" class="form-control" placeholder="KM">
                    </div>
                    <div class="col-1">
                        <input type="number" name="variants[${index}][stock]" value="${data.stock || ''}" class="form-control" placeholder="SL">
                    </div>
                    <div class="col-1">
                        <button type="button" class="btn btn-sm btn-danger remove-variant">×</button>
                    </div>
                </div>
            </div>`;
        return div;
    }

    function bindRemove(btn) {
        btn.addEventListener('click', function() {
            const row = this.closest('.variant-row');
            if (row) {
                row.remove();
                checkVariantVisibility();
            }
        });
    }

    function checkVariantVisibility() {
        const priceSection = document.getElementById('product-price-section');
        const variantRows = variantsContainer ? variantsContainer.querySelectorAll('.variant-row').length : 0;

        if (variantRows > 0) {
            priceSection.style.display = 'none';
        } else {
            priceSection.style.display = 'grid';
        }
    }

    if (variantsContainer) {
        variantsContainer.querySelectorAll('.remove-variant').forEach(bindRemove);
        checkVariantVisibility();
    }

    if (addVariantBtn) {
        addVariantBtn.addEventListener('click', function() {
            const row = createVariantRow(variantIndex++);
            variantsContainer.appendChild(row);
            bindRemove(row.querySelector('.remove-variant'));
            checkVariantVisibility();
        });
    }
});
</script>
