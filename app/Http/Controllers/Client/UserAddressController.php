<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAddressController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'address_detail' => 'required|string|max:500',
        ]);

        $user = Auth::user();
        $isFirst = $user->addresses()->count() === 0;

        $isDefault = $isFirst || $request->has('is_default');

        if ($isDefault && !$isFirst) {
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create([
            'receiver_name' => $request->receiver_name,
            'receiver_phone' => $request->receiver_phone,
            'address_detail' => $request->address_detail,
            'is_default' => $isDefault,
        ]);

        return back()->with('success', 'Đã thêm địa chỉ mới!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'address_detail' => 'required|string|max:500',
        ]);

        $address = Auth::user()->addresses()->findOrFail($id);
        $address->update([
            'receiver_name' => $request->receiver_name,
            'receiver_phone' => $request->receiver_phone,
            'address_detail' => $request->address_detail,
        ]);

        if ($request->has('is_default')) {
            Auth::user()->addresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        }

        return back()->with('success', 'Đã cập nhật địa chỉ!');
    }

    public function destroy($id)
    {
        $address = Auth::user()->addresses()->findOrFail($id);
        
        if ($address->is_default) {
            return back()->with('error', 'Không thể xóa địa chỉ mặc định!');
        }

        $address->delete();

        return back()->with('success', 'Đã xóa địa chỉ!');
    }

    public function setDefault($id)
    {
        Auth::user()->addresses()->update(['is_default' => false]);
        
        $address = Auth::user()->addresses()->findOrFail($id);
        $address->update(['is_default' => true]);

        return back()->with('success', 'Đã đặt làm địa chỉ mặc định!');
    }
}
