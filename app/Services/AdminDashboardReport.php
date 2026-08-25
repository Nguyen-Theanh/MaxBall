<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminDashboardReport
{
    private const PERIOD_LABELS = [
        'today' => 'Hôm nay',
        'last_7_days' => '7 ngày gần nhất',
        'this_month' => 'Tháng này',
        'this_year' => 'Năm nay',
        'custom' => 'Khoảng ngày tùy chọn',
    ];

    private const GRANULARITY_LABELS = [
        'day' => 'Theo ngày',
        'week' => 'Theo tuần',
        'month' => 'Theo tháng',
        'year' => 'Theo năm',
    ];

    private const ORDER_STATUS_LABELS = [
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'processing' => 'Đã xác nhận',
        'shipping' => 'Đang giao',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ];

    public function build(array $filters): array
    {
        $filter = $this->resolveFilter($filters);
        $orders = Order::query()
            ->with('user')
            ->whereBetween('created_at', [$filter['start'], $filter['end']])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $successfulOrders = $orders->filter(
            fn (Order $order) => $order->order_status === 'completed'
                && $order->payment_status === 'paid'
        )->values();
        $customerStatistics = $this->customerStatistics($successfulOrders, $filter);
        $productStatistics = $this->productStatistics($filter);
        $totalOrders = $orders->count();
        $successfulOrderCount = $successfulOrders->count();
        $totalRevenue = (float) $successfulOrders->sum(
            fn (Order $order) => $this->orderRevenue($order)
        );

        return [
            'filter' => $filter,
            'summary' => [
                'total_revenue' => $totalRevenue,
                'shipping_collected' => (float) $successfulOrders->sum('shipping_fee'),
                'successful_orders' => $successfulOrderCount,
                'purchasing_customers' => $customerStatistics['customers']->count(),
                'average_order_value' => $successfulOrderCount > 0
                    ? $totalRevenue / $successfulOrderCount
                    : 0.0,
                'cancelled_orders' => $orders->where('order_status', 'cancelled')->count(),
                'completion_rate' => $totalOrders > 0
                    ? round(($successfulOrderCount / $totalOrders) * 100, 1)
                    : 0.0,
                'total_orders' => $totalOrders,
            ],
            'revenue_chart' => $this->revenueChart($successfulOrders, $filter),
            'products' => $productStatistics,
            'categories' => $this->categoryStatistics($productStatistics['all_sales']),
            'order_statuses' => collect(self::ORDER_STATUS_LABELS)->map(
                fn (string $label, string $status) => [
                    'status' => $status,
                    'label' => $label,
                    'count' => $orders->where('order_status', $status)->count(),
                ]
            )->values(),
            'customers' => $customerStatistics,
            'recent_orders' => $orders->take(10)->values(),
            'orders' => $orders,
        ];
    }

    private function resolveFilter(array $filters): array
    {
        $now = CarbonImmutable::now();
        $period = $filters['period'] ?? 'this_month';

        [$start, $end] = match ($period) {
            'today' => [$now->startOfDay(), $now->endOfDay()],
            'last_7_days' => [$now->subDays(6)->startOfDay(), $now->endOfDay()],
            'this_year' => [$now->startOfYear(), $now->endOfDay()],
            'custom' => [
                CarbonImmutable::parse($filters['start_date'])->startOfDay(),
                CarbonImmutable::parse($filters['end_date'])->endOfDay(),
            ],
            default => [$now->startOfMonth(), $now->endOfDay()],
        };

        $granularity = $filters['chart_granularity'] ?? ($period === 'this_year' ? 'month' : 'day');

        return [
            'period' => $period,
            'period_label' => self::PERIOD_LABELS[$period],
            'chart_granularity' => $granularity,
            'chart_granularity_label' => self::GRANULARITY_LABELS[$granularity],
            'start' => $start,
            'end' => $end,
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'range_label' => $start->format('d/m/Y').' - '.$end->format('d/m/Y'),
        ];
    }

    private function revenueChart(Collection $orders, array $filter): array
    {
        $granularity = $filter['chart_granularity'];
        $totals = $orders->groupBy(
            fn (Order $order) => $this->bucketKey(
                CarbonImmutable::instance($order->created_at),
                $granularity
            )
        )->map(fn (Collection $items) => (float) $items->sum(
            fn (Order $order) => $this->orderRevenue($order)
        ));

        $labels = [];
        $values = [];
        $cursor = $this->bucketStart($filter['start'], $granularity);
        $lastBucket = $this->bucketStart($filter['end'], $granularity);

        while ($cursor->lessThanOrEqualTo($lastBucket)) {
            $key = $this->bucketKey($cursor, $granularity);
            $labels[] = $this->bucketLabel($cursor, $granularity);
            $values[] = $totals->get($key, 0.0);
            $cursor = $this->nextBucket($cursor, $granularity);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    private function productStatistics(array $filter): array
    {
        $sales = DB::table('order_details')
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_details.product_variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->where('orders.order_status', 'completed')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$filter['start'], $filter['end']])
            ->groupBy('products.id', 'products.name', 'categories.id', 'categories.name')
            ->select([
                'products.id as product_id',
                'products.name as product_name',
                'categories.name as category_name',
                DB::raw('SUM(order_details.quantity) as sold_quantity'),
                DB::raw('SUM((order_details.quantity * order_details.price) * ((orders.total_amount - orders.shipping_fee) / NULLIF(orders.sub_total, 0))) as revenue'),
            ])
            ->get()
            ->map(fn ($row) => [
                'product_id' => (int) $row->product_id,
                'product_name' => $row->product_name,
                'category_name' => $row->category_name,
                'sold_quantity' => (int) $row->sold_quantity,
                'revenue' => (float) $row->revenue,
            ]);

        $salesByProduct = $sales->keyBy('product_id');
        $products = Product::query()
            ->with(['category.parent'])
            ->where('status', true)
            ->orderBy('name')
            ->get();
        $slowSelling = $products->map(function (Product $product) use ($salesByProduct) {
            $sale = $salesByProduct->get($product->id);

            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'category_name' => $product->category?->name ?? 'Chưa phân loại',
                'sold_quantity' => $sale['sold_quantity'] ?? 0,
                'revenue' => $sale['revenue'] ?? 0.0,
            ];
        })->sortBy([
            ['sold_quantity', 'asc'],
            ['revenue', 'asc'],
            ['product_name', 'asc'],
        ])->take(10)->values();

        return [
            'top_selling' => $sales->sortByDesc('sold_quantity')->take(10)->values(),
            'top_revenue' => $sales->sortByDesc('revenue')->take(10)->values(),
            'slow_selling' => $slowSelling,
            'all_sales' => $sales,
        ];
    }

    private function categoryStatistics(Collection $productSales): Collection
    {
        $salesByProduct = $productSales->keyBy('product_id');
        $rootCategories = Category::query()
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();
        $statistics = $rootCategories->map(fn (Category $category) => [
            'category_id' => $category->id,
            'category_name' => $category->name,
            'sold_quantity' => 0,
            'revenue' => 0.0,
        ])->keyBy('category_id');

        Product::query()->with(['category.parent'])->get()->each(
            function (Product $product) use ($salesByProduct, $statistics): void {
                $rootCategory = $product->category?->parent ?? $product->category;
                $sale = $salesByProduct->get($product->id);

                if (! $rootCategory || ! $sale) {
                    return;
                }

                $current = $statistics->get($rootCategory->id, [
                    'category_id' => $rootCategory->id,
                    'category_name' => $rootCategory->name,
                    'sold_quantity' => 0,
                    'revenue' => 0.0,
                ]);
                $current['sold_quantity'] += $sale['sold_quantity'];
                $current['revenue'] += $sale['revenue'];
                $statistics->put($rootCategory->id, $current);
            }
        );

        return $statistics->sortByDesc('revenue')->values();
    }

    private function customerStatistics(Collection $successfulOrders, array $filter): array
    {
        $customers = $successfulOrders
            ->groupBy(fn (Order $order) => $order->user_id
                ? 'user-'.$order->user_id
                : 'email-'.mb_strtolower((string) $order->customer_email))
            ->map(function (Collection $orders) {
                /** @var Order $latestOrder */
                $latestOrder = $orders->first();

                return [
                    'customer_name' => $latestOrder->user?->name ?? $latestOrder->customer_name,
                    'customer_email' => $latestOrder->user?->email ?? $latestOrder->customer_email,
                    'order_count' => $orders->count(),
                    'total_spent' => (float) $orders->sum('total_amount'),
                ];
            })
            ->values();

        return [
            'new_customers' => User::query()
                ->where('role', 'customer')
                ->whereBetween('created_at', [$filter['start'], $filter['end']])
                ->count(),
            'top_buyer' => $customers->sortByDesc('order_count')->first(),
            'top_spender' => $customers->sortByDesc('total_spent')->first(),
            'customers' => $customers->sortByDesc('total_spent')->take(10)->values(),
        ];
    }

    private function orderRevenue(Order $order): float
    {
        return max(0.0, (float) $order->total_amount - (float) $order->shipping_fee);
    }

    private function bucketStart(CarbonImmutable $date, string $granularity): CarbonImmutable
    {
        return match ($granularity) {
            'week' => $date->startOfWeek(),
            'month' => $date->startOfMonth(),
            'year' => $date->startOfYear(),
            default => $date->startOfDay(),
        };
    }

    private function bucketKey(CarbonImmutable $date, string $granularity): string
    {
        return match ($granularity) {
            'week' => $date->startOfWeek()->format('Y-m-d'),
            'month' => $date->format('Y-m'),
            'year' => $date->format('Y'),
            default => $date->format('Y-m-d'),
        };
    }

    private function bucketLabel(CarbonImmutable $date, string $granularity): string
    {
        return match ($granularity) {
            'week' => $date->format('d/m').' - '.$date->endOfWeek()->format('d/m'),
            'month' => $date->format('m/Y'),
            'year' => $date->format('Y'),
            default => $date->format('d/m/Y'),
        };
    }

    private function nextBucket(CarbonImmutable $date, string $granularity): CarbonImmutable
    {
        return match ($granularity) {
            'week' => $date->addWeek(),
            'month' => $date->addMonth(),
            'year' => $date->addYear(),
            default => $date->addDay(),
        };
    }
}
