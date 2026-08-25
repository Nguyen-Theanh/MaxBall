<?php

namespace App\Http\Controllers\Client;

use App\Exceptions\GeminiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChatbotMessageRequest;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ChatbotController extends Controller
{
    public function __invoke(ChatbotMessageRequest $request, ChatbotService $chatbot): JsonResponse
    {
        $validated = $request->validated();
        $conversationId = $validated['conversation_id'] ?? 'default';
        $sessionKey = 'chatbot.conversations.'.hash('sha256', $conversationId);
        $conversation = $request->session()->get($sessionKey, [
            'history' => [],
            'product_ids' => [],
        ]);

        try {
            $result = $chatbot->reply(
                $validated['message'],
                is_array($conversation['history'] ?? null) ? $conversation['history'] : [],
                is_array($conversation['product_ids'] ?? null) ? $conversation['product_ids'] : [],
            );

            $history = [
                ...(is_array($conversation['history'] ?? null) ? $conversation['history'] : []),
                ['role' => 'user', 'text' => $this->redactSensitiveData($validated['message'])],
                ['role' => 'model', 'text' => $this->redactSensitiveData($result['message'])],
            ];

            $request->session()->put($sessionKey, [
                'history' => array_slice($history, -10),
                'product_ids' => array_slice($result['product_ids'], 0, 10),
            ]);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'products' => $result['products'],
                'conversation_id' => $conversationId,
            ]);
        } catch (GeminiException) {
            return response()->json([
                'success' => false,
                'message' => 'Hiện tại trợ lý AI đang bận, vui lòng thử lại sau.',
            ], 503);
        } catch (Throwable $exception) {
            Log::error('Chatbot MaxBall xử lý thất bại.', [
                'error_type' => $exception::class,
                'message' => Str::limit($exception->getMessage(), 300),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Hiện tại trợ lý AI đang bận, vui lòng thử lại sau.',
            ], 503);
        }
    }

    private function redactSensitiveData(string $text): string
    {
        $text = preg_replace(
            '/\b(api[_\s-]?key|access[_\s-]?token|token|password|mat\s*khau|mật\s*khẩu)\s*[:=]\s*\S+/iu',
            '$1: [đã ẩn]',
            $text,
        ) ?? $text;

        return preg_replace('/\b(?:\d[ -]*?){13,19}\b/', '[đã ẩn]', $text) ?? $text;
    }
}
