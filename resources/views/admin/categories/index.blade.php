@extends('admin.layouts.app')

@section('title', 'Quản lý Danh mục - MaxBall Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-end mb-4">
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary shadow-sm">
            + Thêm danh mục
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">ID</th>
                            <th scope="col">Tên danh mục</th>
                            <th scope="col">Đường dẫn (Slug)</th>
                            <th scope="col">Trạng thái</th>
                            <th scope="col" class="text-end pe-4">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr class="table-active">
                                <td class="ps-4 fw-bold">{{ $category->id }}</td>
                                <td class="fw-bold text-primary">⭐ {{ $category->name }}</td>
                                <td class="text-muted">{{ $category->slug }}</td>
                                <td>
                                    <span class="badge bg-{{ $category->status ? 'success' : 'secondary' }}">
                                        {{ $category->status ? 'Hoạt động' : 'Đã ẩn' }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-sm btn-outline-primary">Sửa</a>
                                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="d-inline"
                                          data-confirm="Danh mục này sẽ bị xóa khỏi hệ thống. Bạn có chắc chắn muốn tiếp tục?"
                                          data-confirm-title="Xóa danh mục"
                                          data-confirm-label="Xóa danh mục"
                                          data-confirm-variant="danger">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                            
                            @foreach($category->children as $child)
                            <tr>
                                <td class="ps-4 text-muted">{{ $child->id }}</td>
                                <td class="ps-5">↳ {{ $child->name }}</td>
                                <td class="text-muted">{{ $child->slug }}</td>
                                <td>
                                    <span class="badge bg-{{ $child->status ? 'success' : 'secondary' }}">
                                        {{ $child->status ? 'Hoạt động' : 'Đã ẩn' }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.categories.edit', $child->id) }}" class="btn btn-sm btn-outline-primary">Sửa</a>
                                    <form action="{{ route('admin.categories.destroy', $child->id) }}" method="POST" class="d-inline"
                                          data-confirm="Danh mục con này sẽ bị xóa khỏi hệ thống. Bạn có chắc chắn muốn tiếp tục?"
                                          data-confirm-title="Xóa danh mục con"
                                          data-confirm-label="Xóa danh mục"
                                          data-confirm-variant="danger">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach

                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Chưa có danh mục nào trong hệ thống.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($categories->hasPages())
        <div class="mt-4">
            {{ $categories->onEachSide(1)->links() }}
        </div>
    @endif
</div>
@endsection
