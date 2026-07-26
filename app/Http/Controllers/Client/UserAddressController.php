<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\UserAddressData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserAddressController extends Controller
{
    public function store(Request $request, UserAddressData $addressData)
    {
        $request->validate([
            'receiver_name' => ['required', 'string', 'max:255'],
            'receiver_phone' => ['required', 'regex:/^0[0-9]{9}$/'],
            'receiver_email' => ['required', 'email', 'max:255'],
        ], [
            'receiver_phone.regex' => 'Số điện thoại phải gồm 10 chữ số và bắt đầu bằng số 0.',
        ]);

        $data = $addressData->fromRequest($request)
            + $request->only('receiver_name', 'receiver_phone', 'receiver_email');
        $user = Auth::user();
        $isFirst = $user->addresses()->count() === 0;
        $isDefault = $isFirst || $request->has('is_default');

        $createdAddress = DB::transaction(function () use ($user, $data, $isDefault, $isFirst) {
            if ($isDefault && ! $isFirst) {
                $user->addresses()->update(['is_default' => false]);
            }

            return $user->addresses()->create($data + ['is_default' => $isDefault]);
        });

        $redirect = back()->with('success', 'Đã thêm địa chỉ mới!');

        if ($request->input('form_context') === 'checkout_address') {
            $redirect->with('selected_address_id', $createdAddress->id);
        }

        return $redirect;
    }

    public function update(Request $request, $id, UserAddressData $addressData)
    {
        $request->validate([
            'receiver_name' => ['required', 'string', 'max:255'],
            'receiver_phone' => ['required', 'regex:/^0[0-9]{9}$/'],
            'receiver_email' => ['required', 'email', 'max:255'],
        ], [
            'receiver_phone.regex' => 'Số điện thoại phải gồm 10 chữ số và bắt đầu bằng số 0.',
        ]);

        $data = $addressData->fromRequest($request)
            + $request->only('receiver_name', 'receiver_phone', 'receiver_email');
        $address = Auth::user()->addresses()->findOrFail($id);

        DB::transaction(function () use ($request, $address, $data): void {
            $address->update($data);

            if ($request->has('is_default')) {
                Auth::user()->addresses()->update(['is_default' => false]);
                $address->refresh();
                $address->update(['is_default' => true]);
            }
        });

        $redirect = back()->with('success', 'Đã cập nhật địa chỉ!');

        if ($request->input('form_context') === 'checkout_address') {
            $redirect->with('selected_address_id', $address->id);
        }

        return $redirect;
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
