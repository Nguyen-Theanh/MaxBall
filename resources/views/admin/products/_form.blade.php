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
                            <div class="card mb-3 variant-row shadow-sm border-0 bg-light" data-index="{{ $vIndex }}">
                                <div class="card-body p-3">
                                    <input type="hidden" name="variants[{{ $vIndex }}][id]" value="{{ $variant->id ?? '' }}">
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0 text-primary fw-bold variant-title">
                                            {{ old("variants.$vIndex.name", $variant->name ?? 'Biến thể mới') ?: 'Biến thể mới' }}
                                        </h6>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-variant">Xóa</button>
                                    </div>
                                    
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label small text-muted mb-1">Phân loại / Thuộc tính</label>
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($attributes ?? [] as $attr)
                                                    <select class="form-select form-select-sm variant-attr-select" style="width: auto; min-width: 120px;" onchange="updateVariantName(this)">
                                                        <option value="">- {{ $attr->name }} -</option>
                                                        @foreach($attr->values as $val)
                                                            <option value="{{ $val->value }}">{{ $val->value }}</option>
                                                        @endforeach
                                                    </select>
                                                @endforeach
                                            </div>
                                            <input type="hidden" name="variants[{{ $vIndex }}][name]" value="{{ old("variants.$vIndex.name", $variant->name ?? '') }}" class="variant-name-input">
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <label class="form-label small text-muted mb-1">Mã SKU</label>
                                            <input type="text" name="variants[{{ $vIndex }}][sku]" value="{{ old("variants.$vIndex.sku", $variant->sku ?? '') }}" class="form-control form-control-sm" placeholder="Nhập SKU">
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <label class="form-label small text-muted mb-1">Giá bán</label>
                                            <div class="input-group input-group-sm">
                                                <input type="text" class="form-control format-number" placeholder="0" value="{{ old("variants.$vIndex.base_price", $variant->base_price ?? '') ? number_format(old("variants.$vIndex.base_price", $variant->base_price ?? '')) : '' }}">
                                                <input type="hidden" name="variants[{{ $vIndex }}][base_price]" value="{{ old("variants.$vIndex.base_price", $variant->base_price ?? '') }}">
                                                <span class="input-group-text">đ</span>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <label class="form-label small text-muted mb-1">Giá khuyến mãi</label>
                                            <div class="input-group input-group-sm">
                                                <input type="text" class="form-control format-number" placeholder="0" value="{{ old("variants.$vIndex.discount_price", $variant->discount_price ?? '') ? number_format(old("variants.$vIndex.discount_price", $variant->discount_price ?? '')) : '' }}">
                                                <input type="hidden" name="variants[{{ $vIndex }}][discount_price]" value="{{ old("variants.$vIndex.discount_price", $variant->discount_price ?? '') }}">
                                                <span class="input-group-text">đ</span>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <label class="form-label small text-muted mb-1">Số lượng</label>
                                            <input type="text" class="form-control form-control-sm format-number" placeholder="0" value="{{ old("variants.$vIndex.stock", $variant->stock ?? '') ? number_format(old("variants.$vIndex.stock", $variant->stock ?? '')) : '' }}">
                                            <input type="hidden" name="variants[{{ $vIndex }}][stock]" value="{{ old("variants.$vIndex.stock", $variant->stock ?? '') }}">
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
                                {{ $category->display_name }}
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
                        <label class="form-label">Giá gốc chung <span class="text-danger">*</span></label>
                        <small class="text-muted d-block mb-2">Giá hiển thị đại diện ở danh sách sản phẩm (giá tham khảo)</small>
                        <div class="input-group">
                            <input type="text" class="form-control format-number" required value="{{ old('base_price', $product->base_price) ? number_format(old('base_price', $product->base_price)) : '' }}">
                            <input type="hidden" name="base_price" id="base_price_hidden" value="{{ old('base_price', $product->base_price) }}">
                            <span class="input-group-text">đ</span>
                        </div>
                        @error('base_price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Giá khuyến mãi chung</label>
                        <div class="input-group">
                            <input type="text" class="form-control format-number" value="{{ old('discount_price', $product->discount_price) ? number_format(old('discount_price', $product->discount_price)) : '' }}">
                            <input type="hidden" name="discount_price" value="{{ old('discount_price', $product->discount_price) }}">
                            <span class="input-group-text">đ</span>
                        </div>
                        @error('discount_price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
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
    window.windowAttributes = @json($attributes ?? []);
    
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
        div.className = 'card mb-3 variant-row shadow-sm border-0 bg-light';
        div.dataset.index = index;
        div.innerHTML = `
            <div class="card-body p-3">
                <input type="hidden" name="variants[${index}][id]" value="${data.id || ''}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 text-primary fw-bold variant-title">
                        ${data.name || 'Biến thể mới'}
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-variant">Xóa</button>
                </div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small text-muted mb-1">Phân loại / Thuộc tính</label>
                        <div class="d-flex flex-wrap gap-2">
                            ${windowAttributes.map(attr => `
                                <select class="form-select form-select-sm variant-attr-select" style="width: auto; min-width: 120px;" onchange="updateVariantName(this)">
                                    <option value="">- ${attr.name} -</option>
                                    ${attr.values.map(val => `<option value="${val.value}">${val.value}</option>`).join('')}
                                </select>
                            `).join('')}
                        </div>
                        <input type="hidden" name="variants[${index}][name]" value="${data.name || ''}" class="variant-name-input">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Mã SKU</label>
                        <input type="text" name="variants[${index}][sku]" value="${data.sku || ''}" class="form-control form-control-sm" placeholder="Nhập SKU">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Giá bán</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control format-number" placeholder="0" value="${data.base_price ? new Intl.NumberFormat('en-US').format(data.base_price) : ''}">
                            <input type="hidden" name="variants[${index}][base_price]" value="${data.base_price || ''}">
                            <span class="input-group-text">đ</span>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Giá khuyến mãi</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control format-number" placeholder="0" value="${data.discount_price ? new Intl.NumberFormat('en-US').format(data.discount_price) : ''}">
                            <input type="hidden" name="variants[${index}][discount_price]" value="${data.discount_price || ''}">
                            <span class="input-group-text">đ</span>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Số lượng</label>
                        <input type="text" class="form-control form-control-sm format-number" placeholder="0" value="${data.stock ? new Intl.NumberFormat('en-US').format(data.stock) : ''}">
                        <input type="hidden" name="variants[${index}][stock]" value="${data.stock || ''}">
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
        // The general price section is now always visible
        // so the user can edit the reference price shown outside.
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

function updateVariantName(selectElement) {
    const row = selectElement.closest('.variant-row');
    const selects = row.querySelectorAll('.variant-attr-select');
    const nameInput = row.querySelector('.variant-name-input');
    
    let parts = [];
    selects.forEach(s => {
        if (s.value) parts.push(s.value);
    });
    
    
    const newName = parts.join(' - ');
    nameInput.value = newName;
    
    const title = row.querySelector('.variant-title');
    if(title) {
        title.innerText = newName || 'Biến thể mới';
    }
}

// Format numbers logic
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('format-number')) {
        let rawValue = e.target.value.replace(/[^0-9]/g, '');
        
        let hiddenInput = e.target.nextElementSibling;
        if (hiddenInput && hiddenInput.tagName === 'INPUT' && hiddenInput.type === 'hidden') {
            hiddenInput.value = rawValue;
        }

        if (rawValue) {
            e.target.value = new Intl.NumberFormat('en-US').format(rawValue);
        } else {
            e.target.value = '';
        }
        
        // Form validation requires base_price hidden input to trigger required properly
        // However, HTML5 'required' on the text input is enough to prevent empty submission
    }
});
</script>
