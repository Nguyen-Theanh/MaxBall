<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;

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
            'name' => 'required|string|max:255|unique:attributes,name'
        ]);

        Attribute::create(['name' => $request->name]);

        return back()->with('success', 'Thêm thuộc tính thành công');
    }

    public function destroy(Attribute $attribute)
    {
        $attribute->delete();
        return back()->with('success', 'Xóa thuộc tính thành công');
    }

    public function storeValue(Request $request, Attribute $attribute)
    {
        $request->validate([
            'value' => 'required|string|max:255'
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
        $value->delete();
        return back()->with('success', 'Xóa giá trị thành công');
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:attribute_values,id'
        ]);

        foreach ($request->order as $index => $id) {
            AttributeValue::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
