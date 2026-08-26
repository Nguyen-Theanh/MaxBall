<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $coupons = Coupon::query()
            ->when($request->input('source') === 'admin', fn ($query) => $query->where('is_public', true))
            ->when($request->input('source') === 'customer', fn ($query) => $query->where('is_public', false))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData($request);
        $validated['is_public'] = true;

        Coupon::create($validated);

        return redirect()->route('admin.coupons.index')->with('success', 'Voucher được tạo thành công.');
    }

    public function edit(string $id)
    {
        $coupon = Coupon::findOrFail($id);

        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, string $id)
    {
        $coupon = Coupon::findOrFail($id);
        $validated = $this->validatedData($request, $coupon);

        $coupon->update($validated);

        return redirect()->route('admin.coupons.index')->with('success', 'Voucher cập nhật thành công.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return redirect()->route('admin.coupons.index')->with('success', 'Voucher đã được xóa thành công.');
    }

    public function toggleStatus(Coupon $coupon)
    {
        $coupon->update(['status' => ! $coupon->status]);

        return redirect()->route('admin.coupons.index')->with('success', 'Đã cập nhật trạng thái Voucher.');
    }

    public function checkCode(Request $request)
    {
        $code = $request->get('code');
        $excludeId = $request->get('exclude_id');

        $query = Coupon::where('code', $code);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $exists = $query->exists();

        return response()->json(['exists' => $exists]);
    }

    private function validatedData(Request $request, ?Coupon $coupon = null): array
    {
        $discountValueRules = match ($request->input('discount_type')) {
            'fixed' => ['required', 'integer', 'min:1000', 'multiple_of:1000'],
            'percent' => ['required', 'integer', 'between:1,100'],
            'freeship' => ['nullable'],
            default => ['required', 'numeric'],
        };

        $uniqueCodeRule = 'unique:coupons,code'.($coupon ? ','.$coupon->id : '');

        $validated = $request->validate([
            'code' => ['required', 'string', $uniqueCodeRule],
            'description' => ['nullable', 'string'],
            'discount_type' => ['required', 'in:fixed,percent,freeship'],
            'discount_value' => $discountValueRules,
            'max_discount_amount' => ['exclude_unless:discount_type,percent', 'required', 'integer', 'min:1000', 'multiple_of:1000'],
            'min_order_value' => ['nullable', 'integer', 'min:1000', 'multiple_of:1000'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'boolean'],
        ], [
            'discount_value.integer' => 'Giá trị giảm phải là số nguyên.',
            'discount_value.min' => 'Số tiền giảm tối thiểu là 1.000đ.',
            'discount_value.multiple_of' => 'Số tiền giảm phải theo bội số 1.000đ.',
            'discount_value.between' => 'Phần trăm giảm phải từ 1% đến 100%.',
            'max_discount_amount.required' => 'Vui lòng nhập số tiền giảm tối đa cho voucher phần trăm.',
            'max_discount_amount.integer' => 'Số tiền giảm tối đa phải là số nguyên.',
            'max_discount_amount.min' => 'Số tiền giảm tối đa phải từ 1.000đ.',
            'max_discount_amount.multiple_of' => 'Số tiền giảm tối đa phải theo bội số 1.000đ.',
            'min_order_value.integer' => 'Giá trị đơn tối thiểu phải là số nguyên.',
            'min_order_value.min' => 'Giá trị đơn tối thiểu phải từ 1.000đ.',
            'min_order_value.multiple_of' => 'Giá trị đơn tối thiểu phải theo bội số 1.000đ.',
        ]);

        if ($validated['discount_type'] === 'freeship') {
            $validated['discount_value'] = 0;
        }

        if ($validated['discount_type'] !== 'percent') {
            $validated['max_discount_amount'] = null;
        }

        $validated['status'] = $request->boolean('status');

        return $validated;
    }
}
