<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::with('values')->latest()->get();

        return view('admin.attributes.index', compact('attributes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:attributes,name',
        ]);

        Attribute::create(['name' => $request->name]);

        return back()->with('success', 'Thêm thuộc tính thành công');
    }

    public function destroy(Attribute $attribute)
    {
        $values = $attribute->values()->pluck('value');

        if ($this->anyValueIsUsedByVariants($values)) {
            return back()->with(
                'error',
                "Không thể xóa thuộc tính vì vẫn còn biến thể sản phẩm đang sử dụng thuộc tính này.\nVui lòng cập nhật hoặc xóa các biến thể sản phẩm trước."
            );
        }

        if ($values->isNotEmpty()) {
            return back()->with(
                'error',
                "Không thể xóa thuộc tính vì vẫn còn các giá trị thuộc tính.\nVui lòng xóa các giá trị trước."
            );
        }

        $attribute->delete();

        return back()->with('success', 'Xóa thuộc tính thành công');
    }

    public function storeValue(Request $request, Attribute $attribute)
    {
        $request->validate([
            'value' => 'required|string|max:255',
        ]);

        // Check if value already exists for this attribute
        if ($attribute->values()->where('value', $request->value)->exists()) {
            return back()->with('error', 'Giá trị này đã tồn tại');
        }

        $attribute->values()->create(['value' => $request->value]);

        return back()->with('success', 'Thêm giá trị thành công');
    }

    public function destroyValue(AttributeValue $value)
    {
        if ($this->anyValueIsUsedByVariants(collect([$value->value]))) {
            return back()->with(
                'error',
                "Không thể xóa giá trị thuộc tính vì vẫn còn biến thể sản phẩm đang sử dụng giá trị này.\nVui lòng cập nhật hoặc xóa các biến thể sản phẩm trước."
            );
        }

        $value->delete();

        return back()->with('success', 'Xóa giá trị thành công');
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:attribute_values,id',
        ]);

        foreach ($request->order as $index => $id) {
            AttributeValue::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    private function anyValueIsUsedByVariants(Collection $values): bool
    {
        $normalizedValues = $values
            ->map(fn (string $value): string => Str::lower(trim($value)))
            ->filter()
            ->flip();

        if ($normalizedValues->isEmpty()) {
            return false;
        }

        return ProductVariant::query()
            ->whereNotNull('name')
            ->pluck('name')
            ->contains(function (string $variantName) use ($normalizedValues): bool {
                $parts = preg_split('/\s*-\s*/u', $variantName) ?: [];

                return collect($parts)->contains(
                    fn (string $part): bool => $normalizedValues->has(Str::lower(trim($part)))
                );
            });
    }
}
