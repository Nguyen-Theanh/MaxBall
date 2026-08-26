<?php

namespace App\Services;

use Illuminate\Support\Str;

class ChatbotKnowledgeService
{
    /**
     * @return array{
     *     answer: string,
     *     height: int|null,
     *     weight: int|null,
     *     recommended_sizes: array<int, string>
     * }|null
     */
    public function sizeAdvice(string $message): ?array
    {
        $normalized = $this->normalize($message);
        $height = $this->extractHeight($normalized);
        $weight = $this->extractWeight($normalized);
        $requestedSize = $this->extractRequestedSize($normalized);

        if (! $this->isSizeAdviceRequest($normalized, $height, $weight)) {
            return null;
        }

        $chart = data_get($this->knowledge(), 'size_guide.chart', []);

        if (! is_array($chart) || $chart === []) {
            return [
                'answer' => 'MaxBall chưa cập nhật bảng size để tư vấn cho bạn.',
                'height' => $height,
                'weight' => $weight,
                'recommended_sizes' => [],
            ];
        }

        if ($this->isSizeChartRequest($normalized)) {
            return [
                'answer' => $this->sizeChartAnswer($chart),
                'height' => $height,
                'weight' => $weight,
                'recommended_sizes' => [],
            ];
        }

        if ($height === null && $weight === null) {
            return [
                'answer' => 'Bạn cho MaxBall xin chiều cao và cân nặng để mình tư vấn size phù hợp nhé.',
                'height' => null,
                'weight' => null,
                'recommended_sizes' => [],
            ];
        }

        if ($height !== null && $weight === null) {
            return [
                'answer' => $this->missingWeightAnswer($height, $requestedSize, $chart),
                'height' => $height,
                'weight' => null,
                'recommended_sizes' => [],
            ];
        }

        if ($height === null) {
            return [
                'answer' => sprintf(
                    'MaxBall đã nhận cân nặng %d kg. Bạn cho MaxBall xin thêm chiều cao để mình tư vấn size chính xác hơn nhé.',
                    $weight,
                ),
                'height' => null,
                'weight' => $weight,
                'recommended_sizes' => [],
            ];
        }

        return $this->recommendFromMeasurements($height, $weight, $normalized, $chart);
    }

    public function directAnswer(string $message): ?string
    {
        if ($sizeAdvice = $this->sizeAdvice($message)) {
            return $sizeAdvice['answer'];
        }

        $normalized = $this->normalize($message);

        if ($this->containsAny($normalized, [
            'phuong thuc thanh toan', 'hinh thuc thanh toan', 'thanh toan bang gi',
            'thanh toan the nao', 'co thanh toan cod', 'co chuyen khoan',
        ])) {
            return $this->paymentAnswer();
        }

        if ($this->containsAny($normalized, [
            'so dien thoai', 'sdt', 'hotline', 'goi cho shop', 'lien he shop',
        ])) {
            return $this->contactAnswer();
        }

        if ($this->containsAny($normalized, ['dia chi cua hang', 'shop o dau', 'cua hang o dau'])) {
            return 'Địa chỉ MaxBall: '.data_get($this->knowledge(), 'contact.address').'.';
        }

        if ($this->containsAny($normalized, ['email cua hang', 'email shop'])) {
            return 'Email MaxBall: '.data_get($this->knowledge(), 'contact.email').'.';
        }

        return null;
    }

    public function context(): string
    {
        return json_encode(
            $this->knowledge(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );
    }

    private function contactAnswer(): string
    {
        $contact = data_get($this->knowledge(), 'contact', []);

        return implode("\n", [
            '**Thông tin liên hệ MaxBall:**',
            '- **Hotline:** '.($contact['hotline'] ?? 'Chưa cập nhật').' ('.($contact['hotline_hours'] ?? 'chưa cập nhật giờ hỗ trợ').')',
            '- **Email:** '.($contact['email'] ?? 'Chưa cập nhật'),
            '- **Địa chỉ:** '.($contact['address'] ?? 'Chưa cập nhật'),
        ]);
    }

    private function paymentAnswer(): string
    {
        $methods = data_get($this->knowledge(), 'payment_methods', []);

        if (! is_array($methods) || $methods === []) {
            return 'MaxBall chưa cập nhật thông tin phương thức thanh toán.';
        }

        return "**MaxBall hiện hỗ trợ các phương thức thanh toán:**\n- ".implode("\n- ", $methods);
    }

    /**
     * @param  array<string, array<string, array<int, int>>>  $chart
     * @return array{answer: string, height: int, weight: int, recommended_sizes: array<int, string>}
     */
    private function recommendFromMeasurements(int $height, int $weight, string $normalized, array $chart): array
    {
        $heightSizes = $this->matchingSizes($height, 'height_cm', $chart);
        $weightSizes = $this->matchingSizes($weight, 'weight_kg', $chart);
        $matchingSizes = array_values(array_intersect($heightSizes, $weightSizes));
        $fitPreference = $this->fitPreference($normalized);

        if ($matchingSizes !== []) {
            $recommendedSizes = $matchingSizes;

            if (count($matchingSizes) === 1) {
                $answer = sprintf(
                    'Với chiều cao %d cm và cân nặng %d kg, bạn **phù hợp size %s** theo bảng tham khảo.',
                    $height,
                    $weight,
                    $matchingSizes[0],
                );
            } elseif ($fitPreference !== null) {
                $selectedSize = $fitPreference === 'smaller'
                    ? $matchingSizes[0]
                    : $matchingSizes[array_key_last($matchingSizes)];
                $recommendedSizes = [$selectedSize];
                $answer = sprintf(
                    'Với chiều cao %d cm và cân nặng %d kg, số đo của bạn đang sát ranh giới size %s. Vì bạn thích mặc %s, MaxBall đề xuất **size %s**.',
                    $height,
                    $weight,
                    implode(' và ', $matchingSizes),
                    $fitPreference === 'smaller' ? 'ôm/gọn' : 'rộng/thoải mái',
                    $selectedSize,
                );
            } else {
                $answer = sprintf(
                    'Với chiều cao %d cm và cân nặng %d kg, bạn đang sát ranh giới size %s: thích mặc ôm/gọn chọn **%s**, thích rộng/thoải mái chọn **%s**.',
                    $height,
                    $weight,
                    implode(' và ', $matchingSizes),
                    $matchingSizes[0],
                    $matchingSizes[array_key_last($matchingSizes)],
                );
            }
        } else {
            $heightSize = $heightSizes[0] ?? $this->closestSize($height, 'height_cm', $chart);
            $weightSize = $weightSizes[0] ?? $this->closestSize($weight, 'weight_kg', $chart);
            $sizeOrder = array_flip(array_keys($chart));
            $heightIndex = $sizeOrder[$heightSize] ?? 0;
            $weightIndex = $sizeOrder[$weightSize] ?? 0;

            if ($heightIndex >= $weightIndex) {
                $selectedSize = $heightSize;
                $reason = $heightIndex > $weightIndex
                    ? 'ưu tiên theo chiều cao để đảm bảo chiều dài áo'
                    : 'phù hợp nhất khi đối chiếu cả hai thông số';
            } else {
                $selectedSize = $weightSize;
                $reason = 'ưu tiên theo cân nặng để đảm bảo độ rộng áo';
            }

            $recommendedSizes = [$selectedSize];
            $answer = sprintf(
                'Với chiều cao %d cm và cân nặng %d kg, MaxBall đề xuất **size %s** — %s.',
                $height,
                $weight,
                $selectedSize,
                $reason,
            );
        }

        $answer .= ' Bảng size chỉ mang tính tham khảo; form thực tế có thể khác nhau tùy mẫu áo.';

        return [
            'answer' => $answer,
            'height' => $height,
            'weight' => $weight,
            'recommended_sizes' => $recommendedSizes,
        ];
    }

    /** @param  array<string, array<string, array<int, int>>>  $chart */
    private function sizeChartAnswer(array $chart): string
    {
        $lines = ['**Hướng dẫn chọn size tại MaxBall:**'];

        foreach ($chart as $size => $measurement) {
            [$minHeight, $maxHeight] = $measurement['height_cm'] ?? [0, 0];
            [$minWeight, $maxWeight] = $measurement['weight_kg'] ?? [0, 0];
            $lines[] = sprintf(
                '- **Size %s:** Chiều cao %d-%d cm, cân nặng %d-%d kg',
                $size,
                $minHeight,
                $maxHeight,
                $minWeight,
                $maxWeight,
            );
        }

        $lines[] = '- Bảng size chỉ mang tính tham khảo; form thực tế có thể khác nhau tùy mẫu áo.';
        $hotline = data_get($this->knowledge(), 'contact.hotline');

        if ($hotline) {
            $lines[] = '- Cần xác nhận nhanh, bạn có thể gọi hotline '.$hotline.'.';
        }

        return implode("\n", $lines);
    }

    /** @param  array<string, array<string, array<int, int>>>  $chart */
    private function missingWeightAnswer(int $height, ?string $requestedSize, array $chart): string
    {
        if ($requestedSize !== null && isset($chart[$requestedSize])) {
            [$minHeight, $maxHeight] = $chart[$requestedSize]['height_cm'] ?? [0, 0];
            $assessment = $height >= $minHeight && $height <= $maxHeight
                ? sprintf('Chiều cao %d cm nằm trong khoảng tham khảo của size %s.', $height, $requestedSize)
                : sprintf('Chiều cao %d cm nằm ngoài khoảng %d-%d cm của size %s.', $height, $minHeight, $maxHeight, $requestedSize);

            return $assessment.' Bạn cho MaxBall xin thêm cân nặng để mình tư vấn chính xác hơn nhé.';
        }

        return sprintf(
            'MaxBall đã nhận chiều cao %d cm. Bạn cho MaxBall xin thêm cân nặng để mình tư vấn size chính xác hơn nhé.',
            $height,
        );
    }

    /**
     * @param  array<string, array<string, array<int, int>>>  $chart
     * @return array<int, string>
     */
    private function matchingSizes(int $value, string $measurementKey, array $chart): array
    {
        $sizes = [];

        foreach ($chart as $size => $measurement) {
            [$minimum, $maximum] = $measurement[$measurementKey] ?? [null, null];

            if ($minimum !== null && $maximum !== null && $value >= $minimum && $value <= $maximum) {
                $sizes[] = $size;
            }
        }

        return $sizes;
    }

    /** @param  array<string, array<string, array<int, int>>>  $chart */
    private function closestSize(int $value, string $measurementKey, array $chart): string
    {
        $closestSize = (string) array_key_first($chart);
        $smallestDistance = PHP_INT_MAX;

        foreach ($chart as $size => $measurement) {
            [$minimum, $maximum] = $measurement[$measurementKey] ?? [null, null];

            if ($minimum === null || $maximum === null) {
                continue;
            }

            $distance = $value < $minimum ? $minimum - $value : max(0, $value - $maximum);

            if ($distance < $smallestDistance) {
                $closestSize = $size;
                $smallestDistance = $distance;
            }
        }

        return $closestSize;
    }

    private function isSizeAdviceRequest(string $normalized, ?int $height, ?int $weight): bool
    {
        if ($this->isSizeChartRequest($normalized)) {
            return true;
        }

        if ($height !== null || $weight !== null) {
            return true;
        }

        $asksForFit = $this->containsAny($normalized, [
            'tu van size', 'tu van chon size', 'chon size', 'mac size', 'mac co nao',
            'size nao phu hop', 'size gi phu hop', 'nen mac size', 'chon co nao',
        ]);

        return $asksForFit && str_contains($normalized, 'size');
    }

    private function isSizeChartRequest(string $normalized): bool
    {
        return $this->containsAny($normalized, [
            'bang size', 'bang kich co', 'huong dan chon size', 'size theo chieu cao', 'size theo can nang',
        ]);
    }

    private function fitPreference(string $normalized): ?string
    {
        if ($this->containsAny($normalized, ['mac om', 'om gon', 'mac gon', 'body fit'])) {
            return 'smaller';
        }

        if ($this->containsAny($normalized, ['mac rong', 'thoai mai', 'oversize', 'oversized'])) {
            return 'larger';
        }

        return null;
    }

    private function extractHeight(string $normalized): ?int
    {
        if (preg_match('/(?:(?:chieu cao|cao)\s*)?(?:(\d)\s*m\s*(\d{1,2})|(\d{3})\s*cm)/', $normalized, $matches)) {
            if (($matches[1] ?? '') !== '') {
                $centimeters = (string) ($matches[2] ?? '0');

                return ((int) $matches[1] * 100)
                    + (strlen($centimeters) === 1 ? (int) $centimeters * 10 : (int) $centimeters);
            }

            return (int) ($matches[3] ?? 0);
        }

        return null;
    }

    private function extractWeight(string $normalized): ?int
    {
        if (preg_match('/(?:can nang|nang)\s*(\d{2,3})\s*(?:kg)?/', $normalized, $matches)) {
            return (int) $matches[1];
        }

        if (preg_match('/\b(\d{2,3})\s*kg\b/', $normalized, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private function extractRequestedSize(string $normalized): ?string
    {
        if (preg_match('/\bsize\s*[:\-]?\s*(3xl|xxxl|xxl|xl|l|m|s)\b/', $normalized, $matches)) {
            return $this->canonicalSize($matches[1]);
        }

        return null;
    }

    private function canonicalSize(string $size): string
    {
        return strtolower($size) === 'xxxl' ? '3XL' : strtoupper($size);
    }

    /** @return array<string, mixed> */
    private function knowledge(): array
    {
        $knowledge = config('chatbot.store_knowledge', []);

        return is_array($knowledge) ? $knowledge : [];
    }

    /** @param  array<int, string>  $phrases */
    private function containsAny(string $normalizedText, array $phrases): bool
    {
        return collect($phrases)->contains(
            fn (string $phrase): bool => str_contains($normalizedText, $phrase),
        );
    }

    private function normalize(string $value): string
    {
        return Str::of($value)
            ->lower()
            ->ascii()
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();
    }
}
