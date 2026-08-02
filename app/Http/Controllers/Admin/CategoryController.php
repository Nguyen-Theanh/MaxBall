<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    // 1. Hiển thị danh sách danh mục (Hiển thị rõ Cha - Con)
    public function index()
    {
        // Chỉ lấy các danh mục gốc, kèm theo các danh mục con của nó
        $categories = Category::query()
            ->whereNull('parent_id')
            ->with(['children' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    // 2. Form tạo danh mục mới
    public function create()
    {
        // Khi tạo danh mục con, chỉ cho phép chọn cha là 3 danh mục gốc
        $parentCategories = Category::whereNull('parent_id')->get();

        return view('admin.categories.create', compact('parentCategories'));
    }

    // 3. Lưu danh mục mới
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'parent_id' => $request->parent_id,
            'status' => 1,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Đã thêm danh mục!');
    }

    // 4. Hiển thị form sửa danh mục
    public function edit(Category $category)
    {
        // Lấy các danh mục gốc, nhưng loại trừ chính nó (để tránh việc 1 danh mục nhận chính nó làm cha)
        $parentCategories = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->get();

        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    // 5. Cập nhật dữ liệu danh mục
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'parent_id' => $request->parent_id,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Đã cập nhật danh mục thành công!');
    }

    // 6. Xóa danh mục
    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return redirect()
                ->route('admin.categories.index')
                ->with(
                    'error',
                    "Không thể xóa danh mục vì vẫn còn sản phẩm thuộc danh mục này.\nVui lòng chuyển hoặc xóa các sản phẩm trước."
                );
        }

        // Kiểm tra an toàn: Nếu danh mục này đang có danh mục con thì không cho xóa
        if ($category->children()->count() > 0) {
            return redirect()->route('admin.categories.index')->with('error', 'Không thể xóa! Danh mục này đang chứa các danh mục con.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Đã xóa danh mục!');
    }
}
