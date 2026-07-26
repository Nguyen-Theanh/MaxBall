@if($order->order_status === 'completed' && $detail->variant?->product)
    <div class="mt-3">
        @if($detail->review)
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm text-yellow-400" aria-label="{{ $detail->review->rating }} trên 5 sao">
                    @for($star = 1; $star <= 5; $star++)
                        <span class="{{ $star <= $detail->review->rating ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                    @endfor
                </span>
                <span class="text-xs font-bold text-green-600">Đã đánh giá</span>
            </div>
            @if(!($compact ?? false) && $detail->review->content)
                <p class="mt-1 max-w-xl text-sm italic text-gray-600">“{{ $detail->review->content }}”</p>
            @endif
        @else
            <button type="button"
                    data-product-review
                    data-order-detail-id="{{ $detail->id }}"
                    data-order-code="{{ $order->order_code }}"
                    data-product-name="{{ $detail->variant->product->name }}"
                    data-product-variant="{{ $detail->variant->name }}"
                    data-review-action="{{ route('client.orders.reviews.store', [$order, $detail]) }}"
                    class="rounded-lg border border-yellow-500 px-3 py-1.5 text-xs font-bold text-yellow-700 transition hover:bg-yellow-50">
                ★ Đánh giá sản phẩm
            </button>
        @endif
    </div>
@endif
