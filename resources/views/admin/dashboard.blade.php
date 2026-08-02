@extends('admin.layouts.app')

@section('title', 'Thống kê doanh thu - MaxBall')

@section('content')
@php
    $baseFilterQuery = [
        'period' => $report['filter']['period'],
        'chart_granularity' => $report['filter']['chart_granularity'],
    ];

    if ($report['filter']['period'] === 'custom') {
        $baseFilterQuery['start_date'] = $report['filter']['start_date'];
        $baseFilterQuery['end_date'] = $report['filter']['end_date'];
    }

    $periodOptions = [
        'today' => 'Hôm nay',
        'last_7_days' => '7 ngày gần nhất',
        'this_month' => 'Tháng này',
        'this_year' => 'Năm nay',
    ];
    $granularityOptions = [
        'day' => 'Ngày',
        'week' => 'Tuần',
        'month' => 'Tháng',
        'year' => 'Năm',
    ];
    $statusLegendColors = [
        'pending' => 'bg-amber-500',
        'processing' => 'bg-blue-500',
        'shipping' => 'bg-cyan-500',
        'completed' => 'bg-emerald-500',
        'cancelled' => 'bg-red-500',
    ];
    $categoryRevenueTotal = max(1, $report['categories']->sum('revenue'));
@endphp

<div class="space-y-6">
    <header class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <p class="text-sm text-slate-500">
            Kỳ báo cáo: {{ $report['filter']['range_label'] }} · Doanh thu không bao gồm phí vận chuyển
        </p>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.dashboard.export.excel', $baseFilterQuery) }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m3 6V7m3 10v-4M5 3h10l4 4v14H5V3z"/></svg>
                Xuất Excel
            </a>
            <a href="{{ route('admin.dashboard.export.pdf', $baseFilterQuery) }}"
               class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m3 6V7m3 10v-4M5 3h10l4 4v14H5V3z"/></svg>
                Xuất PDF
            </a>
        </div>
    </header>

    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                @foreach($periodOptions as $periodValue => $periodLabel)
                    <a href="{{ route('admin.dashboard', ['period' => $periodValue, 'chart_granularity' => $report['filter']['chart_granularity']]) }}"
                       class="rounded-xl border px-4 py-2 text-sm font-semibold shadow-sm transition {{ $report['filter']['period'] === $periodValue ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-200 bg-slate-50 text-slate-600 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700' }}">
                        {{ $periodLabel }}
                    </a>
                @endforeach

                <button type="button" id="custom-period-toggle"
                        class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold shadow-sm transition {{ $report['filter']['period'] === 'custom' ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-200 bg-slate-50 text-slate-600 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M5 5h14v16H5V5z"/></svg>
                    Chọn khoảng ngày
                </button>
            </div>

            <p class="text-sm text-slate-500">Tổng <span class="font-bold text-slate-800">{{ number_format($report['summary']['total_orders']) }}</span> đơn trong kỳ</p>
        </div>

        <form method="GET" action="{{ route('admin.dashboard') }}" id="custom-period-form"
              class="mt-4 grid grid-cols-1 gap-3 rounded-xl border border-blue-100 bg-blue-50/60 p-4 sm:grid-cols-[1fr_1fr_auto] sm:items-end {{ $report['filter']['period'] === 'custom' ? '' : 'hidden' }}">
            <input type="hidden" name="period" value="custom">
            <input type="hidden" name="chart_granularity" value="{{ $report['filter']['chart_granularity'] }}">
            <div>
                <label for="start_date" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Từ ngày</label>
                <input type="date" id="start_date" name="start_date" required value="{{ $report['filter']['start_date'] }}"
                       class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            </div>
            <div>
                <label for="end_date" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Đến ngày</label>
                <input type="date" id="end_date" name="end_date" required value="{{ $report['filter']['end_date'] }}"
                       class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            </div>
            <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-bold text-white transition hover:bg-blue-700">Áp dụng</button>
        </form>
    </section>

    <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Tổng doanh thu</p><p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($report['summary']['total_revenue'], 0, ',', '.') }}đ</p><p class="mt-2 text-xs text-slate-400">Đã loại {{ number_format($report['summary']['shipping_collected'], 0, ',', '.') }}đ phí vận chuyển</p></div>
                <div class="rounded-xl bg-blue-50 p-3 text-blue-600"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l5-5 4 4 7-8m0 0v5m0-5h-5"/></svg></div>
            </div>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Đơn thành công</p><p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($report['summary']['successful_orders']) }}</p><p class="mt-2 text-xs text-slate-400">Trên tổng {{ number_format($report['summary']['total_orders']) }} đơn</p></div>
                <div class="rounded-xl bg-emerald-50 p-3 text-emerald-600"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            </div>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Khách đã mua</p><p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($report['summary']['purchasing_customers']) }}</p><p class="mt-2 text-xs text-slate-400">{{ number_format($report['customers']['new_customers']) }} khách mới trong kỳ</p></div>
                <div class="rounded-xl bg-violet-50 p-3 text-violet-600"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20H7v-2a5 5 0 0110 0v2zM12 12a4 4 0 100-8 4 4 0 000 8z"/></svg></div>
            </div>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Giá trị đơn trung bình</p><p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($report['summary']['average_order_value'], 0, ',', '.') }}đ</p><p class="mt-2 text-xs text-slate-400">Trên mỗi đơn thành công</p></div>
                <div class="rounded-xl bg-cyan-50 p-3 text-cyan-600"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 8h12M6 12h8M6 16h4M5 4h14v16H5V4z"/></svg></div>
            </div>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Đơn bị hủy</p><p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($report['summary']['cancelled_orders']) }}</p><p class="mt-2 text-xs text-slate-400">Trong khoảng thời gian đã chọn</p></div>
                <div class="rounded-xl bg-red-50 p-3 text-red-600"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></div>
            </div>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Tỷ lệ hoàn thành</p><p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($report['summary']['completion_rate'], 1, ',', '.') }}%</p><p class="mt-2 text-xs text-slate-400">Đơn hoàn thành trên tổng đơn</p></div>
                <div class="rounded-xl bg-amber-50 p-3 text-amber-700"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 8h.01M16 16h.01M7 17L17 7"/></svg></div>
            </div>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-2">
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div><h2 class="font-bold text-slate-900">Doanh thu theo thời gian</h2><p class="mt-1 text-sm text-slate-500">Chỉ tính đơn hoàn thành, không gồm phí vận chuyển</p></div>
                <div class="inline-flex self-start rounded-xl bg-slate-100 p-1">
                    @foreach($granularityOptions as $granularityValue => $granularityLabel)
                        <a href="{{ route('admin.dashboard', array_merge($baseFilterQuery, ['chart_granularity' => $granularityValue])) }}"
                           class="rounded-lg px-3 py-1.5 text-xs font-bold transition {{ $report['filter']['chart_granularity'] === $granularityValue ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                            {{ $granularityLabel }}
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="h-80"><canvas id="revenueChart"></canvas></div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div><h2 class="font-bold text-slate-900">Trạng thái đơn hàng</h2><p class="mt-1 text-sm text-slate-500">Phân bổ {{ number_format($report['summary']['total_orders']) }} đơn trong kỳ báo cáo</p></div>
            <div class="mx-auto h-72 max-w-md"><canvas id="orderStatusChart"></canvas></div>
            <div class="flex flex-wrap justify-center gap-x-4 gap-y-2 text-xs">
                @foreach($report['order_statuses'] as $status)
                    <span class="inline-flex items-center gap-1.5 text-slate-500"><span class="h-2.5 w-2.5 rounded-full {{ $statusLegendColors[$status['status']] }}"></span>{{ $status['label'] }} <strong class="text-slate-800">{{ $status['count'] }}</strong></span>
                @endforeach
            </div>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-2">
        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-5"><h2 class="font-bold text-slate-900">Top 10 sản phẩm bán chạy</h2><p class="mt-1 text-sm text-slate-500">Xếp theo số lượng sản phẩm đã bán</p></div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-400"><tr><th class="px-4 py-3 text-center">#</th><th class="px-4 py-3">Sản phẩm</th><th class="px-4 py-3">Danh mục</th><th class="px-4 py-3 text-right">Đã bán</th><th class="px-4 py-3 text-right">Doanh thu</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($report['products']['top_selling'] as $index => $product)
                            <tr class="hover:bg-slate-50"><td class="px-4 py-3 text-center text-slate-400">{{ $index + 1 }}</td><td class="max-w-[220px] truncate px-4 py-3 font-semibold text-slate-800" title="{{ $product['product_name'] }}">{{ $product['product_name'] }}</td><td class="px-4 py-3 text-slate-500">{{ $product['category_name'] }}</td><td class="px-4 py-3 text-right font-semibold">{{ number_format($product['sold_quantity']) }}</td><td class="whitespace-nowrap px-4 py-3 text-right font-bold text-blue-600">{{ number_format($product['revenue'], 0, ',', '.') }}đ</td></tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-12 text-center text-slate-400">Chưa có dữ liệu bán hàng</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5"><h2 class="font-bold text-slate-900">Doanh thu theo danh mục</h2><p class="mt-1 text-sm text-slate-500">Tỷ trọng đóng góp của từng nhóm hàng</p></div>
            <div class="space-y-5">
                @forelse($report['categories'] as $category)
                    @php $categoryPercent = round(($category['revenue'] / $categoryRevenueTotal) * 100, 1); @endphp
                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3"><span class="font-semibold text-slate-700">{{ $category['category_name'] }}</span><span class="text-sm font-bold text-blue-600">{{ number_format($categoryPercent, 1, ',', '.') }}%</span></div>
                        <div class="h-2 overflow-hidden rounded-full bg-blue-100"><div class="h-full rounded-full bg-blue-600" style="width: {{ min(100, $categoryPercent) }}%"></div></div>
                        <div class="mt-1.5 flex items-center justify-between text-xs text-slate-400"><span>{{ number_format($category['sold_quantity']) }} sản phẩm</span><span>{{ number_format($category['revenue'], 0, ',', '.') }}đ</span></div>
                    </div>
                @empty
                    <div class="py-12 text-center text-sm text-slate-400">Chưa có dữ liệu danh mục</div>
                @endforelse
            </div>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-2">
        @foreach([
            ['title' => 'Sản phẩm doanh thu cao nhất', 'subtitle' => 'Xếp theo doanh thu thực thu', 'items' => $report['products']['top_revenue'], 'color' => 'text-emerald-600'],
            ['title' => 'Sản phẩm bán chậm', 'subtitle' => 'Ưu tiên sản phẩm chưa phát sinh bán hàng', 'items' => $report['products']['slow_selling'], 'color' => 'text-amber-600'],
        ] as $productGroup)
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5"><h2 class="font-bold text-slate-900">{{ $productGroup['title'] }}</h2><p class="mt-1 text-sm text-slate-500">{{ $productGroup['subtitle'] }}</p></div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-400"><tr><th class="px-4 py-3 text-center">#</th><th class="px-4 py-3">Sản phẩm</th><th class="px-4 py-3">Danh mục</th><th class="px-4 py-3 text-right">Đã bán</th><th class="px-4 py-3 text-right">Doanh thu</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($productGroup['items'] as $index => $product)
                                <tr class="hover:bg-slate-50"><td class="px-4 py-3 text-center text-slate-400">{{ $index + 1 }}</td><td class="max-w-[220px] truncate px-4 py-3 font-semibold text-slate-800" title="{{ $product['product_name'] }}">{{ $product['product_name'] }}</td><td class="px-4 py-3 text-slate-500">{{ $product['category_name'] }}</td><td class="px-4 py-3 text-right font-semibold">{{ number_format($product['sold_quantity']) }}</td><td class="whitespace-nowrap px-4 py-3 text-right font-bold {{ $productGroup['color'] }}">{{ number_format($product['revenue'], 0, ',', '.') }}đ</td></tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-12 text-center text-slate-400">Chưa có dữ liệu</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        @endforeach
    </section>

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-2">
        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-5"><h2 class="font-bold text-slate-900">Thống kê khách hàng</h2><p class="mt-1 text-sm text-slate-500">Khách mua hàng thành công trong kỳ</p></div>
            <div class="grid grid-cols-3 gap-3 p-5">
                <div class="rounded-xl bg-blue-50 p-4"><p class="text-xs text-slate-500">Tổng khách</p><p class="mt-2 text-xl font-black text-slate-900">{{ number_format($report['summary']['purchasing_customers']) }}</p></div>
                <div class="rounded-xl bg-emerald-50 p-4"><p class="text-xs text-slate-500">Khách mới</p><p class="mt-2 text-xl font-black text-slate-900">{{ number_format($report['customers']['new_customers']) }}</p></div>
                <div class="rounded-xl bg-violet-50 p-4"><p class="text-xs text-slate-500">Chi tiêu cao nhất</p><p class="mt-2 truncate text-sm font-bold text-slate-900">{{ $report['customers']['top_spender']['customer_name'] ?? '—' }}</p></div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-400"><tr><th class="px-5 py-3">Khách hàng</th><th class="px-5 py-3 text-right">Số đơn</th><th class="px-5 py-3 text-right">Chi tiêu</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($report['customers']['customers'] as $customer)
                            <tr><td class="px-5 py-3"><p class="font-semibold text-slate-800">{{ $customer['customer_name'] }}</p><p class="text-xs text-slate-400">{{ $customer['customer_email'] ?: '—' }}</p></td><td class="px-5 py-3 text-right font-semibold">{{ $customer['order_count'] }}</td><td class="px-5 py-3 text-right font-bold text-emerald-600">{{ number_format($customer['total_spent'], 0, ',', '.') }}đ</td></tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-12 text-center text-slate-400">Chưa có khách hàng mua thành công</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5"><div><h2 class="font-bold text-slate-900">Đơn hàng gần đây</h2><p class="mt-1 text-sm text-slate-500">Cập nhật mới nhất trong kỳ báo cáo</p></div><a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">Xem tất cả</a></div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-400"><tr><th class="px-4 py-3">Mã đơn</th><th class="px-4 py-3">Khách hàng</th><th class="px-4 py-3">Ngày</th><th class="px-4 py-3 text-center">Trạng thái</th><th class="px-4 py-3 text-right">Doanh thu</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($report['recent_orders'] as $order)
                            @php
                                [$statusText, $statusClass] = match($order->order_status) {
                                    'pending' => ['Chờ xác nhận', 'bg-amber-50 text-amber-700'],
                                    'processing' => ['Đã xác nhận', 'bg-blue-50 text-blue-700'],
                                    'shipping' => ['Đang giao', 'bg-cyan-50 text-cyan-700'],
                                    'completed' => ['Hoàn thành', 'bg-emerald-50 text-emerald-700'],
                                    default => ['Đã hủy', 'bg-red-50 text-red-700'],
                                };
                                $orderRevenue = max(0, $order->total_amount - $order->shipping_fee);
                            @endphp
                            <tr><td class="px-4 py-3"><a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-blue-600">#{{ $order->order_code }}</a></td><td class="max-w-[140px] truncate px-4 py-3 font-semibold text-slate-700">{{ $order->customer_name }}</td><td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $order->created_at?->format('d/m/Y') }}</td><td class="px-4 py-3 text-center"><span class="whitespace-nowrap rounded-full px-2.5 py-1 text-[11px] font-bold {{ $statusClass }}">{{ $statusText }}</span></td><td class="whitespace-nowrap px-4 py-3 text-right font-bold text-slate-800">{{ number_format($orderRevenue, 0, ',', '.') }}đ</td></tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-12 text-center text-slate-400">Không có đơn hàng trong kỳ</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const customPeriodToggle = document.getElementById('custom-period-toggle');
        const customPeriodForm = document.getElementById('custom-period-form');

        customPeriodToggle.addEventListener('click', () => customPeriodForm.classList.toggle('hidden'));

        const currency = (value) => new Intl.NumberFormat('vi-VN', {
            style: 'currency', currency: 'VND', maximumFractionDigits: 0,
        }).format(value);

        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: @json($report['revenue_chart']['labels']),
                datasets: [{
                    label: 'Doanh thu',
                    data: @json($report['revenue_chart']['values']),
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.13)',
                    fill: true,
                    borderWidth: 3,
                    pointRadius: 2,
                    pointHoverRadius: 5,
                    tension: 0.32,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (context) => `Doanh thu: ${currency(context.parsed.y)}` } },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#94a3b8', maxRotation: 0, autoSkip: true, maxTicksLimit: 10 } },
                    y: { beginAtZero: true, grid: { color: '#e2e8f0', borderDash: [4, 4] }, ticks: { color: '#94a3b8', callback: (value) => currency(value) } },
                },
            },
        });

        const statusLabelPlugin = typeof ChartDataLabels === 'undefined' ? [] : [ChartDataLabels];
        new Chart(document.getElementById('orderStatusChart'), {
            type: 'doughnut',
            plugins: statusLabelPlugin,
            data: {
                labels: @json($report['order_statuses']->pluck('label')),
                datasets: [{
                    data: @json($report['order_statuses']->pluck('count')),
                    backgroundColor: ['#f59e0b', '#3b82f6', '#06b6d4', '#10b981', '#ef4444'],
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 5,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '66%',
                layout: { padding: { top: 38, right: 62, bottom: 38, left: 62 } },
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        display: (context) => Number(context.dataset.data[context.dataIndex]) > 0 ? 'auto' : false,
                        anchor: 'end', align: 'end', offset: 7, clamp: true, clip: false,
                        color: '#475569',
                        backgroundColor: 'rgba(255,255,255,.97)',
                        borderColor: (context) => context.dataset.backgroundColor[context.dataIndex],
                        borderWidth: 1,
                        borderRadius: 5,
                        padding: { top: 4, right: 6, bottom: 4, left: 6 },
                        textAlign: 'center',
                        font: { size: 9, weight: '600' },
                        formatter: (value, context) => {
                            const total = context.dataset.data.reduce((sum, item) => sum + Number(item), 0);
                            const percentage = total > 0 ? ((Number(value) / total) * 100).toFixed(1) : '0.0';
                            return `${context.chart.data.labels[context.dataIndex]}\n${percentage}%`;
                        },
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => {
                                const total = context.dataset.data.reduce((sum, value) => sum + Number(value), 0);
                                const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : '0.0';
                                return `${context.label}: ${context.parsed} đơn (${percentage}%)`;
                            },
                        },
                    },
                },
            },
        });
    });
</script>
@endpush
