@csrf

<style>
    .attribute-picker {
        max-width: 340px;
        position: relative;
    }

    .attribute-picker-menu {
        position: absolute;
        top: calc(100% + 4px);
        right: 0;
        left: 0;
        z-index: 20;
        background: #fff;
        border: 1px solid #d1d5db;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.14);
    }

    .attribute-picker-menu.d-none {
        display: none;
    }

    .attribute-option {
        width: 100%;
        border: 0;
        background: #fff;
        padding: 9px 12px;
        text-align: left;
        color: #374151;
    }

    .attribute-option:hover,
    .attribute-option.active {
        background: #4055f3;
        color: #fff;
    }

    .selected-attribute-card {
        min-width: 260px;
        border: 1px solid #e5e7eb;
        background: #fff;
        border-radius: 8px;
        padding: 12px;
    }

    .selected-attribute-values {
        max-height: 120px;
        overflow-y: auto;
    }

    .variant-row.variant-row-duplicate {
        outline: 2px solid #dc3545;
        outline-offset: -2px;
    }

    .variant-sku-input.variant-sku-duplicate {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.15);
    }
</style>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                    <div class="invalid-feedback duplicate-error-name" style="display: none;">Tên sản phẩm này đã tồn tại trong hệ thống. Vui lòng chọn tên khác!</div>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label">Slug</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug', $product->slug) }}" class="form-control @error('slug') is-invalid @enderror" placeholder="Để trống sẽ tự tạo theo tên">
                    @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Biến thể sản phẩm</label>
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                        <div>
                            <div class="form-text mt-0">Chọn thuộc tính dùng cho biến thể, hoặc tạo thuộc tính mới ngay tại đây.</div>
                        </div>

                        <div class="d-flex gap-2 align-items-start">
                            <button type="button" id="show-new-attribute" class="btn btn-sm btn-outline-primary">Thêm mới</button>
                            <div class="attribute-picker">
                                <button type="button" id="attribute-picker-toggle" class="form-select form-select-sm text-start pe-5">Thêm hiện có</button>
                                <div id="attribute-picker-menu" class="attribute-picker-menu d-none">
                                    <div class="p-2 border-bottom">
                                        <input type="search" id="attribute-picker-search" class="form-control form-control-sm" placeholder="Tìm kiếm...">
                                    </div>
                                    <div id="attribute-picker-list" style="max-height: 220px; overflow-y: auto;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="new-attribute-panel" class="border rounded bg-white p-3 mb-3 d-none">
                        <div class="fw-semibold text-muted small mb-2">Thuộc tính mới</div>
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Tên</label>
                                <input type="text" id="new-attribute-name" class="form-control form-control-sm" placeholder="Ví dụ: Chiều dài">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Giá trị</label>
                                <input type="text" id="new-attribute-values" class="form-control form-control-sm" placeholder="Ví dụ: 30cm, 40cm, 50cm">
                            </div>
                            <div class="col-md-2 d-grid">
                                <button type="button" id="add-new-attribute" class="btn btn-sm btn-primary">Thêm</button>
                            </div>
                        </div>
                    </div>

                    <div id="selected-attributes" class="d-flex flex-wrap gap-2 mb-3"></div>
                    <div id="new-attributes-hidden"></div>

                    @if ($errors->has('variants.*.name') || $errors->has('variants.*.sku') || $errors->has('variants.*.id'))
                        <div class="alert alert-danger py-2" role="alert">
                            {{ $errors->first('variants.*.name') ?: ($errors->first('variants.*.sku') ?: $errors->first('variants.*.id')) }}
                        </div>
                    @endif
                    <div id="variant-duplicate-alert" class="alert alert-danger d-none py-2" role="alert"></div>
                    <div id="variant-generation-message" class="alert alert-info d-none py-2" role="status"></div>

                    <div id="variants-container">
                        @php $vIndex = 0; @endphp
                        @foreach(old('variants', $product->variants ?? []) as $v)
                            @php
                                $variant = is_array($v) ? (object) $v : $v;
                            @endphp
                            <div class="card mb-3 variant-row shadow-sm border-0 bg-light" data-index="{{ $vIndex }}" data-original-name="{{ old("variants.$vIndex.name", $variant->name ?? '') }}">
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
                                            <div class="d-flex flex-wrap gap-2 variant-attr-container"></div>
                                            <input type="hidden" name="variants[{{ $vIndex }}][name]" value="{{ old("variants.$vIndex.name", $variant->name ?? '') }}" class="variant-name-input">
                                            @error("variants.$vIndex.name") <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <label class="form-label small text-muted mb-1">Mã SKU</label>
                                            <input type="text" name="variants[{{ $vIndex }}][sku]" value="{{ old("variants.$vIndex.sku", $variant->sku ?? '') }}" class="form-control form-control-sm variant-sku-input @error("variants.$vIndex.sku") is-invalid @enderror" placeholder="Tự động tạo SKU">
                                            @error("variants.$vIndex.sku") <div class="invalid-feedback">{{ $message }}</div> @enderror
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

                                        <div class="col-12">
                                            <label class="form-label small text-muted mb-1">Ảnh biến thể</label>
                                            <input type="file" name="variants[{{ $vIndex }}][image]" accept="image/*" class="form-control form-control-sm">
                                            @if (!empty($variant->variant_image_url))
                                                <div class="mt-2">
                                                    <img src="{{ $variant->variant_image_url }}" alt="Ảnh biến thể" class="rounded border" style="width: 76px; height: 76px; object-fit: cover;">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @php $vIndex++; @endphp
                        @endforeach
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <button type="button" id="generate-variants" class="btn btn-sm btn-dark">Tạo biến thể</button>
                        <button type="button" id="add-variant" class="btn btn-sm btn-outline-secondary">Thêm biến thể thủ công</button>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Mô tả</label>
                    <textarea id="description" name="description" rows="5" class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="thumbnail" class="form-label">Ảnh đại diện</label>
                    <input type="text" id="thumbnail" name="thumbnail" value="{{ old('thumbnail', $product->thumbnail) }}" class="form-control @error('thumbnail') is-invalid @enderror" placeholder="Nhập URL ảnh hoặc đường dẫn trong storage">
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
                    <div class="invalid-feedback duplicate-error-category" style="display: none;">Danh mục này đã tồn tại trong hệ thống. Bạn có thể chọn ở danh sách phía trên hoặc nhập tên khác!</div>
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
    const allAttributes = @json($attributes ?? []);
    const selectedAttributes = [];
    const addedAttributeKeys = new Set();
    let newAttributeIndex = 0;

    const nameField = document.getElementById('name');
    const slugField = document.getElementById('slug');
    const variantsContainer = document.getElementById('variants-container');
    const addVariantBtn = document.getElementById('add-variant');
    const generateVariantsBtn = document.getElementById('generate-variants');
    const showNewAttributeBtn = document.getElementById('show-new-attribute');
    const newAttributePanel = document.getElementById('new-attribute-panel');
    const newAttributeName = document.getElementById('new-attribute-name');
    const newAttributeValues = document.getElementById('new-attribute-values');
    const addNewAttributeBtn = document.getElementById('add-new-attribute');
    const newAttributesHidden = document.getElementById('new-attributes-hidden');
    const selectedAttributesContainer = document.getElementById('selected-attributes');
    const pickerToggle = document.getElementById('attribute-picker-toggle');
    const pickerMenu = document.getElementById('attribute-picker-menu');
    const pickerSearch = document.getElementById('attribute-picker-search');
    const pickerList = document.getElementById('attribute-picker-list');
    const duplicateAlert = document.getElementById('variant-duplicate-alert');
    const generationMessage = document.getElementById('variant-generation-message');
    const productForm = variantsContainer?.closest('form');
    const submitButton = productForm?.querySelector('button[type="submit"], input[type="submit"]');

    const toSlug = (str) => {
        return String(str || '')
            .normalize('NFD')
            .replace(/\p{Diacritic}/gu, '')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    };

    if (nameField && slugField) {
        nameField.addEventListener('input', function() {
            if (!slugField.dataset.userEdited) {
                slugField.value = toSlug(this.value);
            }

            refreshAutoSkus();
        });

        slugField.addEventListener('input', function() {
            this.dataset.userEdited = this.value.length > 0;
            refreshAutoSkus();
        });
    }

    let variantIndex = variantsContainer ? (function() {
        let maxIndex = -1;
        variantsContainer.querySelectorAll('.variant-row').forEach(row => {
            const index = parseInt(row.dataset.index, 10);
            if (!isNaN(index) && index > maxIndex) {
                maxIndex = index;
            }
        });
        return maxIndex + 1;
    })() : 0;

    function toggleGlobalPriceSection() {
        const priceSection = document.getElementById('product-price-section');
        if (!priceSection) return;
        
        const hasAttributes = selectedAttributes.length > 0;
        const hasVariants = document.querySelectorAll('#variants-container .variant-row').length > 0;
        
        const basePriceVisible = priceSection.querySelector('input[type="text"]');
        const basePriceHidden = priceSection.querySelector('input[name="base_price"]');
        
        if (hasAttributes || hasVariants) {
            priceSection.classList.add('d-none');
            if (basePriceVisible) {
                basePriceVisible.removeAttribute('required');
            }
            if (basePriceHidden && !basePriceHidden.value) {
                basePriceHidden.value = '0';
            }
        } else {
            priceSection.classList.remove('d-none');
            if (basePriceVisible) {
                basePriceVisible.setAttribute('required', 'required');
            }
        }
    }

    function normalizeKey(value) {
        return toSlug(value);
    }

    function parseValues(value) {
        const seen = new Set();

        return String(value || '')
            .split(/[\n,;|]+/)
            .map((item) => item.trim())
            .filter((item) => {
                const key = normalizeKey(item);

                if (!item || seen.has(key)) {
                    return false;
                }

                seen.add(key);
                return true;
            })
            .map((item, index) => ({ id: `new-${newAttributeIndex}-${index}`, value: item }));
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function addAttribute(attribute, persist = false) {
        const key = normalizeKey(attribute.name);

        if (!key || addedAttributeKeys.has(key)) {
            return;
        }

        const values = (attribute.values || [])
            .map((value) => typeof value === 'string' ? { value } : value)
            .filter((value) => value.value);

        if (!values.length) {
            return;
        }

        const normalizedAttribute = {
            id: attribute.id || `new-${Date.now()}-${selectedAttributes.length}`,
            name: attribute.name,
            values: values.map((value) => ({
                ...value,
                selected: Object.prototype.hasOwnProperty.call(value, 'selected')
                    ? Boolean(value.selected)
                    : Boolean(persist),
            })),
            key,
        };

        selectedAttributes.push(normalizedAttribute);
        addedAttributeKeys.add(key);

        if (persist) {
            const holder = document.createElement('div');
            holder.innerHTML = `
                <input type="hidden" name="new_attributes[${newAttributeIndex}][name]">
                <input type="hidden" name="new_attributes[${newAttributeIndex}][values_text]">
            `;
            holder.querySelector(`[name="new_attributes[${newAttributeIndex}][name]"]`).value = normalizedAttribute.name;
            holder.querySelector(`[name="new_attributes[${newAttributeIndex}][values_text]"]`).value = values.map((value) => value.value).join(', ');
            newAttributesHidden.appendChild(holder);
            newAttributeIndex++;
        }

        renderSelectedAttributes();
        renderPickerList();
        renderAllVariantAttributes();
        toggleGlobalPriceSection();
    }

    function removeAttribute(key) {
        const index = selectedAttributes.findIndex((attribute) => attribute.key === key);

        if (index === -1) {
            return;
        }

        selectedAttributes.splice(index, 1);
        addedAttributeKeys.delete(key);
        renderSelectedAttributes();
        renderPickerList();
        renderAllVariantAttributes();
        toggleGlobalPriceSection();
    }

    function renderSelectedAttributes() {
        selectedAttributesContainer.innerHTML = selectedAttributes.map((attribute) => `
            <div class="selected-attribute-card" data-selected-attribute="${attribute.key}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-semibold">${escapeHtml(attribute.name)}</span>
                    <button type="button" class="btn-close remove-attribute" style="font-size: 0.65rem;" data-key="${attribute.key}" aria-label="Xóa"></button>
                </div>
                <div class="selected-attribute-values vstack gap-1">
                    ${attribute.values.map((value, index) => `
                        <label class="form-check mb-0">
                            <input type="checkbox" class="form-check-input attribute-value-check" data-attribute-key="${attribute.key}" data-value-index="${index}" ${value.selected ? 'checked' : ''}>
                            <span class="form-check-label">${escapeHtml(value.value)}</span>
                        </label>
                    `).join('')}
                </div>
            </div>
        `).join('');

        selectedAttributesContainer.querySelectorAll('.remove-attribute').forEach((button) => {
            button.addEventListener('click', function() {
                removeAttribute(this.dataset.key);
            });
        });

        selectedAttributesContainer.querySelectorAll('.attribute-value-check').forEach((checkbox) => {
            checkbox.addEventListener('change', function() {
                const attribute = selectedAttributes.find((item) => item.key === this.dataset.attributeKey);

                if (!attribute) {
                    return;
                }

                const value = attribute.values[Number(this.dataset.valueIndex)];

                if (value) {
                    value.selected = this.checked;
                }

                renderAllVariantAttributes();
            });
        });

        toggleGlobalPriceSection();
    }
    function renderPickerList() {
        const keyword = normalizeKey(pickerSearch?.value || '');
        const options = allAttributes.filter((attribute) => {
            return !addedAttributeKeys.has(normalizeKey(attribute.name))
                && (!keyword || normalizeKey(attribute.name).includes(keyword));
        });

        pickerList.innerHTML = options.length
            ? options.map((attribute) => `<button type="button" class="attribute-option" data-attribute-id="${attribute.id}">${escapeHtml(attribute.name)}</button>`).join('')
            : '<div class="text-muted small px-3 py-2">Không có thuộc tính phù hợp.</div>';

        pickerList.querySelectorAll('[data-attribute-id]').forEach((button) => {
            button.addEventListener('click', function() {
                const attribute = allAttributes.find((item) => String(item.id) === String(this.dataset.attributeId));

                if (attribute) {
                    addAttribute(attribute);
                    pickerMenu.classList.add('d-none');
                    pickerSearch.value = '';
                    renderPickerList();
                }
            });
        });
    }

    function inferVariantOptions(row) {
        const name = row.dataset.originalName
            || row.querySelector('.variant-name-input')?.value
            || '';
        const remainingParts = name
            .split(/\s+-\s+/)
            .map((part) => part.trim())
            .filter(Boolean);
        const inferred = {};

        selectedAttributes.forEach((attribute) => {
            const selectedValues = attribute.values.filter((value) => value.selected);
            const partIndex = remainingParts.findIndex((part) => {
                return selectedValues.some((value) => normalizeKey(value.value) === normalizeKey(part));
            });

            if (partIndex === -1) {
                return;
            }

            const matchedPart = remainingParts[partIndex];
            const matchedValue = selectedValues.find((value) => normalizeKey(value.value) === normalizeKey(matchedPart));

            if (matchedValue) {
                inferred[attribute.key] = matchedValue.value;
                remainingParts.splice(partIndex, 1);
            }
        });

        return inferred;
    }

    function initializeAttributesFromExistingVariants() {
        const rows = Array.from(variantsContainer?.querySelectorAll('.variant-row') || []);

        if (!rows.length) {
            return;
        }

        const usageByAttribute = new Map();

        rows.forEach((row) => {
            const name = row.dataset.originalName
                || row.querySelector('.variant-name-input')?.value
                || '';
            const usedAttributeKeys = new Set();

            name.split(/\s+-\s+/)
                .map((part) => part.trim())
                .filter(Boolean)
                .forEach((part) => {
                    const partKey = normalizeKey(part);
                    const matchingAttribute = allAttributes.find((attribute) => {
                        const attributeKey = normalizeKey(attribute.name);

                        return !usedAttributeKeys.has(attributeKey)
                            && (attribute.values || []).some((value) => normalizeKey(value.value) === partKey);
                    });

                    if (!matchingAttribute) {
                        return;
                    }

                    const attributeKey = normalizeKey(matchingAttribute.name);
                    const usedValues = usageByAttribute.get(attributeKey) || new Set();
                    usedValues.add(partKey);
                    usageByAttribute.set(attributeKey, usedValues);
                    usedAttributeKeys.add(attributeKey);
                });
        });

        allAttributes.forEach((attribute) => {
            const attributeKey = normalizeKey(attribute.name);
            const usedValues = usageByAttribute.get(attributeKey);

            if (!usedValues?.size) {
                return;
            }

            addAttribute({
                ...attribute,
                values: (attribute.values || []).map((value) => ({
                    ...value,
                    selected: usedValues.has(normalizeKey(value.value)),
                })),
            });
        });
    }

    function renderVariantAttributes(row) {
        const container = row.querySelector('.variant-attr-container');

        if (!container) {
            return;
        }

        const oldValues = {};
        container.querySelectorAll('.variant-attr-select').forEach((select) => {
            oldValues[select.dataset.attributeKey] = select.value;
        });
        const inferredValues = inferVariantOptions(row);

        container.innerHTML = selectedAttributes.map((attribute) => ({
            ...attribute,
            values: attribute.values.filter((value) => value.selected),
        })).filter((attribute) => attribute.values.length).map((attribute) => `
            <select class="form-select form-select-sm variant-attr-select" data-attribute-key="${attribute.key}" style="width: auto; min-width: 150px;">
                <option value="">- ${escapeHtml(attribute.name)} -</option>
                ${attribute.values.map((value) => `<option value="${escapeHtml(value.value)}">${escapeHtml(value.value)}</option>`).join('')}
            </select>
        `).join('');

        container.querySelectorAll('.variant-attr-select').forEach((select) => {
            const restoredValue = oldValues[select.dataset.attributeKey]
                || inferredValues[select.dataset.attributeKey];

            if (restoredValue) {
                select.value = restoredValue;
            }

            select.addEventListener('change', function() {
                updateVariantName(row);
            });
        });

        updateVariantName(row);
    }

    function renderAllVariantAttributes() {
        variantsContainer.querySelectorAll('.variant-row').forEach(renderVariantAttributes);
    }

    function createVariantRow(index, data = {}) {
        const div = document.createElement('div');
        div.className = 'card mb-3 variant-row shadow-sm border-0 bg-light';
        div.dataset.index = index;
        div.dataset.originalName = data.name || '';
        div.innerHTML = `
            <div class="card-body p-3">
                <input type="hidden" name="variants[${index}][id]" value="${escapeHtml(data.id || '')}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 text-primary fw-bold variant-title">${escapeHtml(data.name || 'Biến thể mới')}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-variant">Xóa</button>
                </div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small text-muted mb-1">Phân loại / Thuộc tính</label>
                        <div class="d-flex flex-wrap gap-2 variant-attr-container"></div>
                        <input type="hidden" name="variants[${index}][name]" value="${escapeHtml(data.name || '')}" class="variant-name-input">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Mã SKU</label>
                        <input type="text" name="variants[${index}][sku]" value="${escapeHtml(data.sku || '')}" class="form-control form-control-sm variant-sku-input" placeholder="Tự động tạo SKU">
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
                    <div class="col-12">
                        <label class="form-label small text-muted mb-1">Ảnh biến thể</label>
                        <input type="file" name="variants[${index}][image]" accept="image/*" class="form-control form-control-sm">
                    </div>
                </div>
            </div>`;

        bindVariantRow(div);
        renderVariantAttributes(div);
        if (data.options) {
            applyVariantOptions(div, data.options);
        }
        syncVariantSku(div);

        return div;
    }

    function applyVariantOptions(row, options) {
        row.querySelectorAll('.variant-attr-select').forEach((select) => {
            if (options[select.dataset.attributeKey]) {
                select.value = options[select.dataset.attributeKey];
            }
        });

        updateVariantName(row);
    }

    function generateAttributeCombinations() {
        const attributesWithSelectedValues = selectedAttributes.map((attribute) => ({
            ...attribute,
            values: attribute.values.filter((value) => value.selected),
        })).filter((attribute) => attribute.values.length);

        return attributesWithSelectedValues.reduce((combinations, attribute) => {
            const next = [];

            combinations.forEach((combo) => {
                attribute.values.forEach((value) => {
                    next.push({
                        ...combo,
                        [attribute.key]: value.value,
                    });
                });
            });

            return next;
        }, [{}]);
    }

    function variantSignatureFromOptions(options) {
        return Object.entries(options)
            .filter(([, value]) => value)
            .map(([key, value]) => `${key}:${normalizeKey(value)}`)
            .sort()
            .join('|');
    }

    function variantNameFromOptions(options) {
        return selectedAttributes
            .map((attribute) => options[attribute.key] || '')
            .filter(Boolean)
            .join(' - ');
    }

    function variantSignatureFromRow(row) {
        const options = {};

        row.querySelectorAll('.variant-attr-select').forEach((select) => {
            if (select.value) {
                options[select.dataset.attributeKey] = select.value;
            }
        });

        return variantSignatureFromOptions(options);
    }

    function hasCompleteVariantSelection(row) {
        if (!selectedAttributes.length) {
            return true;
        }

        const activeAttributes = selectedAttributes.filter((attribute) => {
            return attribute.values.some((value) => value.selected);
        });

        if (activeAttributes.length !== selectedAttributes.length) {
            return false;
        }

        const selects = Array.from(row.querySelectorAll('.variant-attr-select'));

        return selects.length === activeAttributes.length
            && selects.every((select) => Boolean(select.value));
    }

    function updateDuplicateVariantState() {
        const rows = Array.from(variantsContainer?.querySelectorAll('.variant-row') || []);
        const nameOwners = new Map();
        const skuOwners = new Map();
        const messages = new Set();

        rows.forEach((row) => {
            row.classList.remove('variant-row-duplicate');
            row.querySelector('.variant-sku-input')?.classList.remove('variant-sku-duplicate');
        });

        rows.forEach((row) => {
            const name = row.querySelector('.variant-name-input')?.value.trim() || '';
            const nameKey = normalizeKey(name);
            const skuInput = row.querySelector('.variant-sku-input');
            const sku = skuInput?.value.trim() || '';
            const skuKey = sku.toUpperCase();
            const canCompareName = selectedAttributes.length === 0
                || row.dataset.variantComplete === '1';

            if (nameKey && canCompareName) {
                if (nameOwners.has(nameKey)) {
                    row.classList.add('variant-row-duplicate');
                    nameOwners.get(nameKey).classList.add('variant-row-duplicate');
                    messages.add(`Biến thể "${name}" đang bị trùng.`);
                } else {
                    nameOwners.set(nameKey, row);
                }
            }

            if (skuKey) {
                if (skuOwners.has(skuKey)) {
                    row.classList.add('variant-row-duplicate');
                    skuInput?.classList.add('variant-sku-duplicate');

                    const firstRow = skuOwners.get(skuKey);
                    firstRow.classList.add('variant-row-duplicate');
                    firstRow.querySelector('.variant-sku-input')?.classList.add('variant-sku-duplicate');
                    messages.add(`Mã SKU "${sku}" đang bị trùng.`);
                } else {
                    skuOwners.set(skuKey, row);
                }
            }
        });

        const hasDuplicates = messages.size > 0;

        if (duplicateAlert) {
            duplicateAlert.textContent = hasDuplicates
                ? `${Array.from(messages).join(' ')} Vui lòng xóa hoặc chỉnh lại biến thể trùng.`
                : '';
            duplicateAlert.classList.toggle('d-none', !hasDuplicates);
        }

        if (submitButton) {
            submitButton.disabled = hasDuplicates;
        }

        return !hasDuplicates;
    }

    function bindVariantRow(row) {
        row.querySelector('.remove-variant')?.addEventListener('click', function() {
            row.remove();
            updateDuplicateVariantState();
            toggleGlobalPriceSection();
        });

        const skuInput = row.querySelector('.variant-sku-input');
        if (skuInput) {
            skuInput.dataset.autoSku = '';
            skuInput.dataset.manualSku = skuInput.value.trim() ? '1' : '';
            skuInput.addEventListener('input', function() {
                this.dataset.manualSku = this.value && this.value !== this.dataset.autoSku ? '1' : '';
                updateDuplicateVariantState();
            });
        }
    }

    function updateVariantName(row) {
        const parts = Array.from(row.querySelectorAll('.variant-attr-select'))
            .map((select) => select.value)
            .filter(Boolean);
        const isComplete = hasCompleteVariantSelection(row);

        row.dataset.variantComplete = isComplete ? '1' : '';

        if (!isComplete || !parts.length) {
            syncVariantSku(row);
            updateDuplicateVariantState();
            return;
        }

        const newName = parts.join(' - ');
        const nameInput = row.querySelector('.variant-name-input');
        const title = row.querySelector('.variant-title');

        if (nameInput) {
            nameInput.value = newName;
        }

        if (title) {
            title.innerText = newName || 'Biến thể mới';
        }

        syncVariantSku(row);
        updateDuplicateVariantState();
    }

    function refreshAutoSkus() {
        variantsContainer.querySelectorAll('.variant-row').forEach(syncVariantSku);
    }

    function syncVariantSku(row) {
        const skuInput = row.querySelector('.variant-sku-input');

        if (!skuInput || skuInput.dataset.manualSku === '1') {
            return;
        }

        const index = Number(row.dataset.index || 0) + 1;
        const variantName = row.querySelector('.variant-name-input')?.value || '';
        const base = (slugField?.value || toSlug(nameField?.value || '') || 'san-pham').toUpperCase();
        const suffix = toSlug(variantName || `var-${index}`).toUpperCase();
        const sku = `${base}-${suffix}`.replace(/-+/g, '-').replace(/^-|-$/g, '');

        skuInput.value = sku;
        skuInput.dataset.autoSku = sku;
        updateDuplicateVariantState();
    }

    showNewAttributeBtn?.addEventListener('click', function() {
        newAttributePanel.classList.toggle('d-none');
        newAttributeName.focus();
    });

    addNewAttributeBtn?.addEventListener('click', function() {
        const name = newAttributeName.value.trim();
        const values = parseValues(newAttributeValues.value);

        if (!name || !values.length) {
            return;
        }

        addAttribute({ name, values }, true);
        newAttributeName.value = '';
        newAttributeValues.value = '';
        newAttributePanel.classList.add('d-none');
    });

    pickerToggle?.addEventListener('click', function() {
        pickerMenu.classList.toggle('d-none');
        pickerSearch.focus();
        renderPickerList();
    });

    pickerSearch?.addEventListener('input', renderPickerList);

    document.addEventListener('click', function(event) {
        if (!event.target.closest('.attribute-picker')) {
            pickerMenu?.classList.add('d-none');
        }
    });

    initializeAttributesFromExistingVariants();

    variantsContainer?.querySelectorAll('.variant-row').forEach((row) => {
        bindVariantRow(row);
        renderVariantAttributes(row);
        syncVariantSku(row);
    });

    generateVariantsBtn?.addEventListener('click', function() {
        const hasSelectedValues = selectedAttributes.some((attribute) => attribute.values.some((value) => value.selected));

        if (!hasSelectedValues) {
            return;
        }

        const existingSignatures = new Set(
            Array.from(variantsContainer.querySelectorAll('.variant-row'))
                .map(variantSignatureFromRow)
                .filter(Boolean)
        );
        const existingNames = new Set(
            Array.from(variantsContainer.querySelectorAll('.variant-name-input'))
                .map((input) => normalizeKey(input.value))
                .filter(Boolean)
        );
        let createdCount = 0;
        let skippedCount = 0;

        generateAttributeCombinations().forEach((options) => {
            const signature = variantSignatureFromOptions(options);
            const variantName = variantNameFromOptions(options);
            const nameKey = normalizeKey(variantName);

            if (!signature || existingSignatures.has(signature) || (nameKey && existingNames.has(nameKey))) {
                skippedCount++;
                return;
            }

            existingSignatures.add(signature);
            existingNames.add(nameKey);
            const row = createVariantRow(variantIndex++, { options, name: variantName });
            variantsContainer.appendChild(row);
            createdCount++;
        });

        if (generationMessage) {
            generationMessage.textContent = createdCount === 0 && skippedCount > 0
                ? 'Các biến thể tương ứng đã tồn tại, hệ thống không tạo thêm bản trùng.'
                : createdCount > 0 && skippedCount > 0
                    ? `Đã tạo ${createdCount} biến thể mới và bỏ qua ${skippedCount} biến thể đã tồn tại.`
                    : '';
            generationMessage.classList.toggle('d-none', !generationMessage.textContent);
        }

        updateDuplicateVariantState();
        toggleGlobalPriceSection();
    });

    addVariantBtn?.addEventListener('click', function() {
        const row = createVariantRow(variantIndex++);
        variantsContainer.appendChild(row);
        generationMessage?.classList.add('d-none');
        updateDuplicateVariantState();
        toggleGlobalPriceSection();
    });

    productForm?.addEventListener('submit', function(event) {
        if (isNameDuplicate || isCategoryDuplicate) {
            event.preventDefault();
            const errDiv = isNameDuplicate ? errorNameDiv : errorCategoryDiv;
            errDiv?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        if (!updateDuplicateVariantState()) {
            event.preventDefault();
            duplicateAlert?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    renderPickerList();
    updateDuplicateVariantState();
    
    // Check duplicate names
    const nameInput = document.getElementById('name');
    const categoryNameInput = document.getElementById('category_name');

    const errorNameDiv = document.querySelector('.duplicate-error-name');
    const errorCategoryDiv = document.querySelector('.duplicate-error-category');
    
    let debounceTimer;
    let isNameDuplicate = false;
    let isCategoryDuplicate = false;
    const productId = '{{ $product->id ?? '' }}';

    function checkSubmitButtonState() {
        if (!submitButton) return;
        if (isNameDuplicate || isCategoryDuplicate) {
            submitButton.disabled = true;
        } else {
            submitButton.disabled = false;
        }
    }

    if (nameInput) {
        nameInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const value = this.value.trim();
            
            if (value === '') {
                isNameDuplicate = false;
                nameInput.classList.remove('is-invalid');
                errorNameDiv.style.display = 'none';
                checkSubmitButtonState();
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(`/admin/products/check-name?name=${encodeURIComponent(value)}&ignore_id=${productId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            isNameDuplicate = true;
                            nameInput.classList.add('is-invalid');
                            errorNameDiv.style.display = 'block';
                        } else {
                            isNameDuplicate = false;
                            nameInput.classList.remove('is-invalid');
                            errorNameDiv.style.display = 'none';
                        }
                        checkSubmitButtonState();
                    })
                    .catch(err => console.error(err));
            }, 500);
        });
    }

    if (categoryNameInput) {
        categoryNameInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const value = this.value.trim();
            
            if (value === '') {
                isCategoryDuplicate = false;
                categoryNameInput.classList.remove('is-invalid');
                errorCategoryDiv.style.display = 'none';
                checkSubmitButtonState();
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(`/admin/categories/check-name?name=${encodeURIComponent(value)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            isCategoryDuplicate = true;
                            categoryNameInput.classList.add('is-invalid');
                            errorCategoryDiv.style.display = 'block';
                        } else {
                            isCategoryDuplicate = false;
                            categoryNameInput.classList.remove('is-invalid');
                            errorCategoryDiv.style.display = 'none';
                        }
                        checkSubmitButtonState();
                    })
                    .catch(err => console.error(err));
            }, 500);
        });
    }

    toggleGlobalPriceSection();
});

document.addEventListener('input', function(e) {
    if (e.target.classList.contains('format-number')) {
        let rawValue = e.target.value.replace(/[^0-9]/g, '');
        let hiddenInput = e.target.nextElementSibling;

        if (hiddenInput && hiddenInput.tagName === 'INPUT' && hiddenInput.type === 'hidden') {
            hiddenInput.value = rawValue;
        }

        e.target.value = rawValue ? new Intl.NumberFormat('en-US').format(rawValue) : '';
    }
});
</script>
