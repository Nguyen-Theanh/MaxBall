<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function show(Request $request): View
    {
        $orders = $request->user()
            ->orders()
            ->with(['details.variant.product'])
            ->orderByDesc('created_at')
            ->paginate(8);

        return view('client.account.show', [
            'user' => $request->user(),
            'orders' => $orders,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'regex:/^0[0-9]{9}$/'],
            'address' => ['nullable', 'string', 'max:255'],
        ], [
            'phone.regex' => 'Số điện thoại phải gồm 10 chữ số và bắt đầu bằng số 0.',
        ]);

        $user->update($data);

        return back()->with('success', 'Đã cập nhật thông tin tài khoản.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.confirmed' => 'Mật khẩu mới nhập lại không khớp.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
        ]);

        if (! Hash::check($data['current_password'], $request->user()->password)) {
            return back()
                ->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng.'])
                ->onlyInput();
        }

        $request->user()->update([
            'password' => $data['password'],
        ]);

        return back()->with('success', 'Đã đổi mật khẩu thành công.');
    }
}
