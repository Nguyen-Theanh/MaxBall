<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Review;
use App\Services\ReviewMediaUploader;
use App\Support\ReviewMediaRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReviewController extends Controller
{
    public function store(
        Request $request,
        Order $order,
        OrderDetail $orderDetail,
        ReviewMediaUploader $mediaUploader
    ) {
        abort_unless($order->user_id === $request->user()->id, 404);
        abort_unless($orderDetail->order_id === $order->id, 404);

        if ($order->order_status !== 'completed') {
            return back()
                ->withInput()
                ->with('error', 'Bạn chỉ có thể đánh giá sản phẩm sau khi đơn hàng đã hoàn thành.');
        }

        $product = $orderDetail->variant?->product;

        if (! $product) {
            return back()
                ->withInput()
                ->with('error', 'Sản phẩm này không còn tồn tại để đánh giá.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'content' => ['nullable', 'string', 'max:1000'],
            ...ReviewMediaRules::rules(),
        ], [
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'rating.between' => 'Số sao đánh giá phải từ 1 đến 5.',
            'content.max' => 'Nội dung đánh giá không được vượt quá 1.000 ký tự.',
            ...ReviewMediaRules::messages(),
        ]);

        if ($orderDetail->review()->exists()) {
            return back()
                ->withInput()
                ->with('error', 'Sản phẩm này trong đơn hàng đã được đánh giá.');
        }

        $storedPaths = [];

        try {
            DB::transaction(function () use (
                $request,
                $order,
                $orderDetail,
                $product,
                $validated,
                $mediaUploader,
                &$storedPaths
            ): void {
                $review = Review::create([
                    'user_id' => $request->user()->id,
                    'product_id' => $product->id,
                    'order_id' => $order->id,
                    'order_detail_id' => $orderDetail->id,
                    'rating' => $validated['rating'],
                    'content' => filled($validated['content'] ?? null)
                        ? trim($validated['content'])
                        : null,
                ]);

                $storedPaths = $mediaUploader->store($review, $request->file('media', []));
            });
        } catch (Throwable $exception) {
            $mediaUploader->delete($storedPaths);

            if ($orderDetail->review()->exists()) {
                return back()
                    ->withInput()
                    ->with('error', 'Sản phẩm này trong đơn hàng đã được đánh giá.');
            }

            throw $exception;
        }

        return $this->redirectAfterReview($request, $order)
            ->with('success', 'Cảm ơn bạn đã đánh giá sản phẩm!');
    }

    private function redirectAfterReview(Request $request, Order $order): RedirectResponse
    {
        return match ($request->input('review_context')) {
            'account' => redirect()->to(route('account.show').'#orders'),
            'orders.index' => redirect()->route('client.orders.index'),
            default => redirect()->route('client.orders.show', $order),
        };
    }
}
