<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class UserAddressData
{
    public function __construct(private readonly VietnamAddressService $addressService)
    {
    }

    /**
     * Validate an address payload and resolve its administrative-unit names.
     *
     * @return array<string, int|string>
     */
    public function fromRequest(Request $request, ?string $prefix = null): array
    {
        $field = fn (string $name): string => $prefix ? "{$prefix}.{$name}" : $name;

        $validated = $request->validate([
            $field('address_line') => ['required', 'string', 'max:150'],
            $field('province_code') => ['required', 'integer', 'min:1', 'max:99'],
            $field('ward_code') => ['required', 'integer', 'min:1', 'max:99999'],
        ], [
            $field('address_line').'.required' => 'Vui lòng nhập số nhà, tên đường hoặc thôn/ấp.',
            $field('province_code').'.required' => 'Vui lòng chọn tỉnh/thành phố.',
            $field('ward_code').'.required' => 'Vui lòng chọn xã/phường.',
        ]);

        $address = $prefix ? $validated[$prefix] : $validated;

        try {
            $location = $this->addressService->resolve(
                (int) $address['province_code'],
                (int) $address['ward_code']
            );
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                $field('province_code') => 'Không thể xác minh địa chỉ lúc này. Vui lòng thử lại.',
            ]);
        }

        if (! $location) {
            throw ValidationException::withMessages([
                $field('ward_code') => 'Xã/phường không thuộc tỉnh/thành phố đã chọn.',
            ]);
        }

        $addressDetail = implode(', ', [
            trim($address['address_line']),
            $location['ward_name'],
            $location['province_name'],
        ]);

        if (Str::length($addressDetail) > 255) {
            throw ValidationException::withMessages([
                $field('address_line') => 'Địa chỉ quá dài. Vui lòng nhập ngắn gọn hơn.',
            ]);
        }

        return [
            'address_line' => trim($address['address_line']),
            'province_code' => (int) $address['province_code'],
            'province_name' => $location['province_name'],
            'ward_code' => (int) $address['ward_code'],
            'ward_name' => $location['ward_name'],
            'address_detail' => $addressDetail,
        ];
    }
}
