@props([
    'prefix' => 'address',
    'inputClass' => 'w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-[#d92525]',
    'labelClass' => 'mb-2 block text-sm font-medium text-gray-700',
    'addressLine' => null,
    'provinceCode' => null,
    'wardCode' => null,
    'fieldPrefix' => null,
    'required' => true,
])

@php
    $field = fn (string $name): string => $fieldPrefix ? "{$fieldPrefix}[{$name}]" : $name;
    $errorKey = fn (string $name): string => $fieldPrefix ? "{$fieldPrefix}.{$name}" : $name;
    $provinceField = $field('province_code');
    $wardField = $field('ward_code');
    $addressLineField = $field('address_line');
    $provinceErrorKey = $errorKey('province_code');
    $wardErrorKey = $errorKey('ward_code');
    $addressLineErrorKey = $errorKey('address_line');
@endphp

<div
    class="space-y-4"
    data-vietnam-address
    data-address-prefix="{{ $prefix }}"
    data-initial-province="{{ old($provinceErrorKey, $provinceCode) }}"
    data-initial-ward="{{ old($wardErrorKey, $wardCode) }}"
>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label for="{{ $prefix }}_province_code" class="{{ $labelClass }}">Tỉnh/Thành phố</label>
            <select
                id="{{ $prefix }}_province_code"
                name="{{ $provinceField }}"
                class="{{ $inputClass }} bg-white disabled:cursor-not-allowed disabled:bg-gray-100"
                data-province-select
                @if($required) required @endif
                disabled
            >
                <option value="">Đang tải tỉnh/thành...</option>
            </select>
            @error($provinceErrorKey)
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="{{ $prefix }}_ward_code" class="{{ $labelClass }}">Xã/Phường</label>
            <select
                id="{{ $prefix }}_ward_code"
                name="{{ $wardField }}"
                class="{{ $inputClass }} bg-white disabled:cursor-not-allowed disabled:bg-gray-100"
                data-ward-select
                @if($required) required @endif
                disabled
            >
                <option value="">Chọn tỉnh/thành trước</option>
            </select>
            @error($wardErrorKey)
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="{{ $prefix }}_address_line" class="{{ $labelClass }}">Số nhà, tên đường, thôn/ấp</label>
        <textarea
            id="{{ $prefix }}_address_line"
            name="{{ $addressLineField }}"
            rows="2"
            maxlength="150"
            placeholder="Ví dụ: 123 Nguyễn Huệ"
            class="{{ $inputClass }}"
            data-address-line
            @if($required) required @endif
        >{{ old($addressLineErrorKey, $addressLine) }}</textarea>
        @error($addressLineErrorKey)
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="hidden items-center gap-2 text-xs text-red-600" data-address-status>
        <span data-address-status-text></span>
        <button type="button" class="font-semibold underline" data-address-retry>Thử lại</button>
    </div>
</div>
