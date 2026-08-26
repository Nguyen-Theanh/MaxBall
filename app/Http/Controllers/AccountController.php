<?php

namespace App\Http\Controllers;

use App\Services\UserAddressData;
use App\Support\OrderCancellationReasons;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function show(Request $request): View
    {
        $ordersQuery = $request->user()
            ->orders()
            ->with(['details.variant.product', 'details.review'])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $ordersQuery->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhereHas('details.variant.product', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $ordersQuery->paginate(8, ['*'], 'orders_page')
            ->withQueryString()
            ->fragment('orders');

        $addresses = $request->user()
            ->addresses()
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->paginate(8, ['*'], 'addresses_page')
            ->withQueryString()
            ->fragment('address');
        $defaultAddress = $request->user()
            ->addresses()
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->first();
        $customerCancellationReasons = OrderCancellationReasons::customer();

        $walletTransactions = $request->user()
            ->walletTransactions()
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'wallet_page')
            ->fragment('wallet');

        $userVouchers = $request->user()
            ->userVouchers()
            ->with('coupon')
            ->whereHas('coupon')
            ->orderBy('is_used')
            ->orderByDesc('created_at')
            ->paginate(8, ['*'], 'vouchers_page')
            ->withQueryString()
            ->fragment('vouchers');

        $activeVoucherCount = $request->user()
            ->userVouchers()
            ->where('is_used', false)
            ->whereHas('coupon', fn ($query) => $query->currentlyAvailable())
            ->count();

        return view('client.account.show', [
            'user' => $request->user(),
            'orders' => $orders,
            'addresses' => $addresses,
            'defaultAddress' => $defaultAddress,
            'customerCancellationReasons' => $customerCancellationReasons,
            'walletTransactions' => $walletTransactions,
            'userVouchers' => $userVouchers,
            'activeVoucherCount' => $activeVoucherCount,
        ]);
    }

    public function update(Request $request, UserAddressData $addressData): RedirectResponse
    {
        $user = $request->user();
        $addressInput = (array) $request->input('default_address', []);
        $hasAddressInput = collect(['address_line', 'province_code', 'ward_code'])
            ->contains(fn (string $field): bool => filled($addressInput[$field] ?? null));

        if ($hasAddressInput && blank($request->input('phone'))) {
            throw ValidationException::withMessages([
                'phone' => 'Vui lòng nhập số điện thoại để dùng cho địa chỉ nhận hàng mặc định.',
            ]);
        }

        $profileData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'regex:/^0[0-9]{9}$/'],
        ], [
            'phone.regex' => 'Số điện thoại phải gồm 10 chữ số và bắt đầu bằng số 0.',
        ]);

        $resolvedAddress = $hasAddressInput
            ? $addressData->fromRequest($request, 'default_address')
            : null;

        DB::transaction(function () use ($user, $profileData, $resolvedAddress): void {
            $defaultAddress = $user->addresses()
                ->where('is_default', true)
                ->lockForUpdate()
                ->first()
                ?? $user->addresses()->oldest()->lockForUpdate()->first();

            if ($resolvedAddress) {
                $profileData['address'] = $resolvedAddress['address_detail'];
            }

            $user->update($profileData);

            if ($resolvedAddress) {
                $user->addresses()->update(['is_default' => false]);

                $payload = $resolvedAddress + [
                    'receiver_name' => $profileData['name'],
                    'receiver_phone' => $profileData['phone'],
                    'receiver_email' => $profileData['email'],
                    'is_default' => true,
                ];

                if ($defaultAddress) {
                    $defaultAddress->refresh();
                    $defaultAddress->update($payload);
                } else {
                    $user->addresses()->create($payload);
                }
            } elseif ($defaultAddress) {
                $contactData = [
                    'receiver_name' => $profileData['name'],
                    'receiver_email' => $profileData['email'],
                ];

                if (filled($profileData['phone'] ?? null)) {
                    $contactData['receiver_phone'] = $profileData['phone'];
                }

                $defaultAddress->update($contactData);
            }
        });

        return back()->with(
            'success',
            $resolvedAddress
                ? 'Đã cập nhật hồ sơ và địa chỉ nhận hàng mặc định.'
                : 'Đã cập nhật thông tin tài khoản.'
        );
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
