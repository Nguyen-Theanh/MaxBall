<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = \App\Models\Coupon::orderByDesc('id')->paginate(10);
        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:coupons,code',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:fixed,percent,freeship',
            'discount_value' => 'required_unless:discount_type,freeship|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:start_date',
            'status' => 'boolean'
        ]);

        if ($validated['discount_type'] === 'freeship') {
            $validated['discount_value'] = 0;
        }

        $validated['status'] = $request->has('status');

        \App\Models\Coupon::create($validated);

        return redirect()->route('admin.coupons.index')->with('success', 'Voucher được tạo thành công.');
    }

    public function edit(string $id)
    {
        $coupon = \App\Models\Coupon::findOrFail($id);
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, string $id)
    {
        $coupon = \App\Models\Coupon::findOrFail($id);
        
        $validated = $request->validate([
            'code' => 'required|string|unique:coupons,code,' . $coupon->id,
            'description' => 'nullable|string',
            'discount_type' => 'required|in:fixed,percent,freeship',
            'discount_value' => 'required_unless:discount_type,freeship|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:start_date',
            'status' => 'boolean'
        ]);

        if ($validated['discount_type'] === 'freeship') {
            $validated['discount_value'] = 0;
        }

        $validated['status'] = $request->has('status');

        $coupon->update($validated);

        return redirect()->route('admin.coupons.index')->with('success', 'Voucher cập nhật thành công.');
    }

    public function destroy(\App\Models\Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->route('admin.coupons.index')->with('success', 'Voucher đã được xóa thành công.');
    }

    public function toggleStatus(\App\Models\Coupon $coupon)
    {
        $coupon->update(['status' => !$coupon->status]);
        return redirect()->route('admin.coupons.index')->with('success', 'Đã cập nhật trạng thái Voucher.');
    }

    public function checkCode(Request $request)
    {
        $code = $request->get('code');
        $excludeId = $request->get('exclude_id');
        
        $query = \App\Models\Coupon::where('code', $code);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        $exists = $query->exists();
        return response()->json(['exists' => $exists]);
    }
}
