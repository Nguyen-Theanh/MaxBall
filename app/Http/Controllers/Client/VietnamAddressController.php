<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\VietnamAddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class VietnamAddressController extends Controller
{
    public function provinces(VietnamAddressService $addressService): JsonResponse
    {
        try {
            return response()->json(['data' => $addressService->provinces()]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Không thể tải danh sách tỉnh/thành lúc này. Vui lòng thử lại.',
            ], 503);
        }
    }

    public function wards(Request $request, VietnamAddressService $addressService): JsonResponse
    {
        $validated = $request->validate([
            'province_code' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        try {
            return response()->json([
                'data' => $addressService->wards((int) $validated['province_code']),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Không thể tải danh sách xã/phường lúc này. Vui lòng thử lại.',
            ], 503);
        }
    }
}
