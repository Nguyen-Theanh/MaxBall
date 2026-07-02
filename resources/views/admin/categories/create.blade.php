@extends('admin.layouts.app')

@section('title', 'Thêm Danh mục - MaxBall Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="{{ route('admin.categories.index') }}" class="text-decoration-none text-muted">
            &larr; Quay lại danh sách
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 ps-4">
                    <h4 class="card-title mb-0 fw-bold">Thêm danh mục mới</h4>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.categories.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="name" class="form-label fw-bold">Tên danh mục <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="VD: Giày Thể Thao, Nike, Phụ kiện..." required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="parent_id" class="form-label fw-bold">Danh mục cha</label>
                            <select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
                                <option value="">-- Là danh mục gốc (Không có cha) --</option>
                                @foreach($parentCategories as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                        ⭐ {{ $parent->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-muted mt-2">
                                Nếu bạn muốn tạo danh mục lớn (như Giày, Áo), hãy để trống. Nếu tạo danh mục con (như Nike), hãy chọn danh mục cha tương ứng.
                            </div>
                            @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary px-4 py-2 fw-medium">Lưu danh mục</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 d-none d-lg-block">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-primary mb-3">💡 Mẹo nhỏ</h5>
                    <p class="text-muted small mb-2">Đường dẫn (Slug) sẽ tự động được tạo từ Tên danh mục của bạn.</p>
                    <p class="text-muted small">Cấu trúc hiển thị ngoài trang chủ (Mega Menu) sẽ tự động đồng bộ theo cây danh mục bạn thiết lập tại đây.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection