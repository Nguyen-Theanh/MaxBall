<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductVariant;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderInventoryService
{
    public function __construct(private readonly OrderVoucherService $voucherService) {}

    public function reserveCod(Order $order): Order
    {
        if ($order->payment_method !== 'cod') {
            return $order;
        }

        return DB::transaction(function () use ($order): Order {
            $lockedOrder = $this->lockedOrder($order);

            if ($lockedOrder->hasActiveReservation() || $lockedOrder->inventory_committed_at) {
                return $lockedOrder;
            }

            $quantities = $this->detailQuantities($lockedOrder);
            $variants = $this->lockedVariants($quantities);

            foreach ($quantities as $variantId => $quantity) {
                $variant = $this->variantOrFail($variants, $variantId);

                if ($variant->available_stock < $quantity) {
                    throw new DomainException($this->insufficientStockMessage($variant));
                }
            }

            foreach ($quantities as $variantId => $quantity) {
                $variant = $variants->get($variantId);
                $variant->reserved_stock = (int) $variant->reserved_stock + $quantity;
                $variant->save();
            }

            $reservedAt = now();
            $lockedOrder->update([
                'reserved_at' => $reservedAt,
                'reservation_expires_at' => $reservedAt->copy()->addHours(
                    max(1, (int) config('orders.cod_confirmation_hours', 24))
                ),
                'inventory_released_at' => null,
            ]);

            return $lockedOrder->refresh();
        });
    }

    public function confirmCod(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            $lockedOrder = $this->lockedOrder($order);

            if ($lockedOrder->payment_method !== 'cod' || $lockedOrder->order_status !== 'pending') {
                throw new DomainException('Chỉ có thể xác nhận đơn COD đang chờ xác nhận.');
            }

            if ($lockedOrder->reservation_expires_at?->isPast()) {
                $this->releaseActiveReservation($lockedOrder);
                $lockedOrder->update($this->timeoutCancellationData());
                $this->voucherService->restoreForCancelledOrder($lockedOrder);

                return $lockedOrder->refresh();
            }

            $quantities = $this->detailQuantities($lockedOrder);
            $variants = $this->lockedVariants($quantities);

            foreach ($quantities as $variantId => $quantity) {
                $variant = $this->variantOrFail($variants, $variantId);

                if ((int) $variant->stock < $quantity) {
                    throw new DomainException($this->insufficientStockMessage($variant));
                }

                if ($lockedOrder->hasActiveReservation() && (int) $variant->reserved_stock < $quantity) {
                    throw new DomainException('Dữ liệu giữ hàng của đơn không còn hợp lệ. Vui lòng kiểm tra lại tồn kho.');
                }
            }

            foreach ($quantities as $variantId => $quantity) {
                $variant = $variants->get($variantId);

                if ($lockedOrder->hasActiveReservation()) {
                    $variant->reserved_stock = max(0, (int) $variant->reserved_stock - $quantity);
                }

                $variant->stock = (int) $variant->stock - $quantity;
                $variant->save();
            }

            $lockedOrder->update([
                'order_status' => 'confirmed',
                'inventory_committed_at' => now(),
            ]);

            return $lockedOrder->refresh();
        });
    }

    public function cancel(Order $order, array $cancellationData): Order
    {
        return DB::transaction(function () use ($order, $cancellationData): Order {
            $lockedOrder = $this->lockedOrder($order);

            if (in_array($lockedOrder->order_status, ['completed', 'cancelled'], true)) {
                throw new DomainException('Không thể hủy đơn hàng đã hoàn thành hoặc đã hủy.');
            }

            if ($lockedOrder->hasActiveReservation()) {
                $this->releaseActiveReservation($lockedOrder);
            } elseif ($this->shouldRestoreCommittedStock($lockedOrder)) {
                $this->restoreCommittedStock($lockedOrder);
            }

            $lockedOrder->update($cancellationData + [
                'order_status' => 'cancelled',
                'cancelled_at' => now(),
            ]);
            $this->voucherService->restoreForCancelledOrder($lockedOrder);

            return $lockedOrder->refresh();
        });
    }

    public function expireCod(Order $order): ?Order
    {
        return DB::transaction(function () use ($order): ?Order {
            $lockedOrder = $this->lockedOrder($order);

            if (
                $lockedOrder->payment_method !== 'cod'
                || $lockedOrder->order_status !== 'pending'
                || ! $lockedOrder->hasActiveReservation()
                || ! $lockedOrder->reservation_expires_at?->lte(now())
            ) {
                return null;
            }

            $this->releaseActiveReservation($lockedOrder);
            $lockedOrder->update($this->timeoutCancellationData());
            $this->voucherService->restoreForCancelledOrder($lockedOrder);

            return $lockedOrder->refresh();
        });
    }

    private function releaseActiveReservation(Order $order): void
    {
        if (! $order->hasActiveReservation()) {
            return;
        }

        $quantities = $this->detailQuantities($order);
        $variants = $this->lockedVariants($quantities);

        foreach ($quantities as $variantId => $quantity) {
            $variant = $variants->get($variantId);

            if (! $variant) {
                continue;
            }

            $variant->reserved_stock = max(0, (int) $variant->reserved_stock - $quantity);
            $variant->save();
        }

        $order->update(['inventory_released_at' => now()]);
    }

    private function restoreCommittedStock(Order $order): void
    {
        $quantities = $this->detailQuantities($order);
        $variants = $this->lockedVariants($quantities);

        foreach ($quantities as $variantId => $quantity) {
            $variant = $variants->get($variantId);

            if (! $variant) {
                continue;
            }

            $variant->stock = (int) $variant->stock + $quantity;
            $variant->save();
        }

        $order->update(['inventory_released_at' => now()]);
    }

    private function shouldRestoreCommittedStock(Order $order): bool
    {
        if ($order->inventory_released_at) {
            return false;
        }

        if ($order->inventory_committed_at) {
            return true;
        }

        if ($order->payment_method === 'cod') {
            return in_array($order->order_status, ['pending', 'processing', 'confirmed', 'shipping'], true);
        }

        return $order->payment_method === 'vietqr' && $order->payment_status === 'paid';
    }

    /**
     * @return Collection<int, int>
     */
    private function detailQuantities(Order $order): Collection
    {
        return $order->details()
            ->select('product_variant_id', DB::raw('SUM(quantity) as reserved_quantity'))
            ->groupBy('product_variant_id')
            ->pluck('reserved_quantity', 'product_variant_id')
            ->map(fn ($quantity): int => (int) $quantity);
    }

    /**
     * @param  Collection<int, int>  $quantities
     * @return Collection<int, ProductVariant>
     */
    private function lockedVariants(Collection $quantities): Collection
    {
        return ProductVariant::with('product')
            ->whereIn('id', $quantities->keys()->sort()->values())
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    private function lockedOrder(Order $order): Order
    {
        return Order::query()->lockForUpdate()->findOrFail($order->id);
    }

    private function variantOrFail(Collection $variants, int $variantId): ProductVariant
    {
        $variant = $variants->get($variantId);

        if (! $variant) {
            throw new DomainException('Một sản phẩm trong đơn không còn tồn tại.');
        }

        return $variant;
    }

    private function insufficientStockMessage(ProductVariant $variant): string
    {
        $name = trim(($variant->product?->name ?? 'Sản phẩm').' - '.($variant->name ?? ''));

        return "Sản phẩm \"{$name}\" không đủ số lượng có thể bán.";
    }

    private function timeoutCancellationData(): array
    {
        return [
            'order_status' => 'cancelled',
            'cancelled_by' => 'system',
            'cancellation_reason' => 'confirmation_timeout',
            'cancellation_note' => null,
            'cancelled_at' => now(),
        ];
    }
}
