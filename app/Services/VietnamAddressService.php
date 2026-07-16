<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class VietnamAddressService
{
    /**
     * @return array<int, array{code: int, name: string, division_type: string|null}>
     */
    public function provinces(): array
    {
        return Cache::remember(
            'vietnam-address:v2:provinces',
            now()->addDay(),
            fn (): array => $this->fetchUnits('p/')
        );
    }

    /**
     * @return array<int, array{code: int, name: string, division_type: string|null, province_code: int|null}>
     */
    public function wards(int $provinceCode): array
    {
        return Cache::remember(
            "vietnam-address:v2:wards:{$provinceCode}",
            now()->addDay(),
            fn (): array => $this->fetchUnits('w/', ['province' => $provinceCode])
        );
    }

    /**
     * @return array{province_name: string, ward_name: string}|null
     */
    public function resolve(int $provinceCode, int $wardCode): ?array
    {
        $province = collect($this->provinces())->firstWhere('code', $provinceCode);

        if (! $province) {
            return null;
        }

        $ward = collect($this->wards($provinceCode))->firstWhere('code', $wardCode);

        if (! $ward || (isset($ward['province_code']) && (int) $ward['province_code'] !== $provinceCode)) {
            return null;
        }

        return [
            'province_name' => $province['name'],
            'ward_name' => $ward['name'],
        ];
    }

    /**
     * @param  array<string, int|string>  $query
     * @return array<int, array<string, int|string|null>>
     */
    private function fetchUnits(string $path, array $query = []): array
    {
        $baseUrl = rtrim((string) config('services.vietnam_address.base_url'), '/');
        $caBundle = config('services.vietnam_address.ca_bundle');
        $httpOptions = filled($caBundle) ? ['verify' => $caBundle] : [];

        $response = Http::withOptions($httpOptions)
            ->acceptJson()
            ->timeout(10)
            ->retry(2, 250)
            ->get("{$baseUrl}/{$path}", $query)
            ->throw();

        return collect($response->json() ?? [])
            ->filter(fn ($unit): bool => is_array($unit) && isset($unit['code'], $unit['name']))
            ->map(fn (array $unit): array => [
                'code' => (int) $unit['code'],
                'name' => (string) $unit['name'],
                'division_type' => isset($unit['division_type']) ? (string) $unit['division_type'] : null,
                'province_code' => isset($unit['province_code']) ? (int) $unit['province_code'] : null,
            ])
            ->values()
            ->all();
    }
}
