<?php

namespace App\Services;

use App\Exceptions\GeminiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeminiService
{
    /**
     * @param  array<int, array{role: string, parts: array<int, array{text: string}>}>  $contents
     */
    public function generate(array $contents, string $systemInstruction): string
    {
        $apiKey = trim((string) config('services.gemini.api_key'));
        $model = trim((string) config('services.gemini.model'));

        if ($apiKey === '') {
            Log::warning('Gemini chưa được cấu hình API key.');

            throw new GeminiException('Gemini API key is not configured.');
        }

        if ($model === '' || ! preg_match('/^[a-zA-Z0-9._-]+$/', $model)) {
            Log::warning('Tên model Gemini không hợp lệ.', ['model' => $model]);

            throw new GeminiException('Gemini model is invalid.');
        }

        $endpoint = sprintf(
            '%s/v1beta/models/%s:generateContent',
            rtrim((string) config('services.gemini.base_url'), '/'),
            rawurlencode($model),
        );

        $caBundle = trim((string) config('services.gemini.ca_bundle'));
        $thinkingLevel = trim((string) config('services.gemini.thinking_level', 'low'));

        if ($caBundle !== '' && ! is_file($caBundle)) {
            Log::warning('Không tìm thấy CA bundle dùng cho Gemini API.', [
                'ca_bundle' => $caBundle,
            ]);

            throw new GeminiException('Gemini CA bundle does not exist.');
        }

        if (! in_array($thinkingLevel, ['minimal', 'low', 'medium', 'high'], true)) {
            Log::warning('Mức suy nghĩ Gemini không hợp lệ.', [
                'thinking_level' => $thinkingLevel,
            ]);

            throw new GeminiException('Gemini thinking level is invalid.');
        }

        try {
            $request = Http::asJson()
                ->acceptJson()
                ->withHeaders(['x-goog-api-key' => $apiKey])
                ->connectTimeout((int) config('services.gemini.connect_timeout', 5))
                ->timeout((int) config('services.gemini.timeout', 15));

            if ($caBundle !== '') {
                $request = $request->withOptions(['verify' => $caBundle]);
            }

            $response = $request->post($endpoint, [
                'systemInstruction' => [
                    'parts' => [
                        ['text' => $systemInstruction],
                    ],
                ],
                'contents' => $contents,
                'generationConfig' => [
                    'maxOutputTokens' => 512,
                    'thinkingConfig' => [
                        'thinkingLevel' => $thinkingLevel,
                    ],
                ],
            ]);
        } catch (ConnectionException $exception) {
            Log::warning('Không thể kết nối Gemini API.', [
                'model' => $model,
                'error_type' => $exception::class,
            ]);

            throw new GeminiException('Gemini connection failed.', previous: $exception);
        } catch (Throwable $exception) {
            Log::error('Lỗi không mong đợi khi gọi Gemini API.', [
                'model' => $model,
                'error_type' => $exception::class,
            ]);

            throw new GeminiException('Gemini request failed.', previous: $exception);
        }

        if ($response->failed()) {
            Log::warning('Gemini API trả về lỗi.', [
                'model' => $model,
                'status' => $response->status(),
                'api_error' => data_get($response->json(), 'error.status'),
            ]);

            throw new GeminiException('Gemini API returned an error.');
        }

        $parts = data_get($response->json(), 'candidates.0.content.parts', []);
        $answer = collect(is_array($parts) ? $parts : [])
            ->pluck('text')
            ->filter(fn ($text) => is_string($text) && trim($text) !== '')
            ->implode("\n");

        if (trim($answer) === '') {
            Log::warning('Gemini API trả về nội dung không hợp lệ.', [
                'model' => $model,
                'block_reason' => data_get($response->json(), 'promptFeedback.blockReason'),
                'finish_reason' => data_get($response->json(), 'candidates.0.finishReason'),
            ]);

            throw new GeminiException('Gemini response is empty.');
        }

        return trim($answer);
    }
}
