<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo thống kê MaxBall</title>
    <style>
        @page { margin: 24px 28px; }
        body { font-family: "DejaVu Sans", sans-serif; color: #1f2937; font-size: 10px; line-height: 1.45; }
        h1, h2, p { margin: 0; }
        h1 { color: #1e3a8a; font-size: 21px; }
        h2 { margin-bottom: 8px; color: #111827; font-size: 14px; }
        .header { margin-bottom: 18px; border-bottom: 2px solid #2563eb; padding-bottom: 12px; }
        .header-table, .metrics, .two-columns { width: 100%; border-collapse: separate; border-spacing: 8px; }
        .header-table td { vertical-align: bottom; }
        .muted { color: #6b7280; }
        .text-right { text-align: right; }
        .metric { width: 16.66%; border: 1px solid #dbeafe; background: #eff6ff; padding: 10px; vertical-align: top; }
        .metric-label { color: #64748b; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .metric-value { margin-top: 5px; color: #0f172a; font-size: 15px; font-weight: bold; }
        .section { margin-top: 18px; page-break-inside: avoid; }
        .section.break { page-break-before: always; }
        .section.flow { page-break-inside: auto; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background: #2563eb; color: white; padding: 6px 7px; text-align: left; font-size: 8px; text-transform: uppercase; }
        table.data td { border-bottom: 1px solid #e5e7eb; padding: 6px 7px; vertical-align: top; }
        table.data tr:nth-child(even) td { background: #f8fafc; }
        table.data .number { text-align: right; white-space: nowrap; }
        .column { width: 50%; vertical-align: top; }
        .badge { display: inline-block; border-radius: 9px; background: #e0e7ff; color: #3730a3; padding: 2px 7px; font-size: 8px; font-weight: bold; }
        .footer { position: fixed; right: 0; bottom: -14px; left: 0; color: #94a3b8; text-align: center; font-size: 8px; }
    </style>
</head>
<body>
    <div class="footer">MaxBall · Báo cáo được tạo lúc {{ now()->format('d/m/Y H:i') }}</div>

    <div class="header">
        <table class="header-table">
            <tr>
                <td>
                    <h1>BÁO CÁO THỐNG KÊ KINH DOANH</h1>
                    <p class="muted">MAXBALL</p>
                </td>
                <td class="text-right">
                    <strong>{{ $report['filter']['period_label'] }}</strong><br>
                    <span class="muted">{{ $report['filter']['range_label'] }}</span>
                </td>
            </tr>
        </table>
    </div>

    <table class="metrics">
        <tr>
            <td class="metric"><div class="metric-label">Tổng doanh thu</div><div class="metric-value">{{ number_format($report['summary']['total_revenue'], 0, ',', '.') }}đ</div></td>
            <td class="metric"><div class="metric-label">Đơn thành công</div><div class="metric-value">{{ number_format($report['summary']['successful_orders']) }}</div></td>
            <td class="metric"><div class="metric-label">Khách đã mua</div><div class="metric-value">{{ number_format($report['summary']['purchasing_customers']) }}</div></td>
            <td class="metric"><div class="metric-label">Giá trị đơn TB</div><div class="metric-value">{{ number_format($report['summary']['average_order_value'], 0, ',', '.') }}đ</div></td>
            <td class="metric"><div class="metric-label">Đơn bị hủy</div><div class="metric-value">{{ number_format($report['summary']['cancelled_orders']) }}</div></td>
            <td class="metric"><div class="metric-label">Tỷ lệ hoàn thành</div><div class="metric-value">{{ number_format($report['summary']['completion_rate'], 1, ',', '.') }}%</div></td>
        </tr>
    </table>
    <p class="muted">Doanh thu và giá trị đơn trung bình không bao gồm {{ number_format($report['summary']['shipping_collected'], 0, ',', '.') }}đ phí vận chuyển đã thu trong kỳ.</p>

    <table class="two-columns section">
        <tr>
            <td class="column">
                <h2>Trạng thái đơn hàng</h2>
                <table class="data">
                    <thead><tr><th>Trạng thái</th><th class="number">Số đơn</th></tr></thead>
                    <tbody>
                        @foreach($report['order_statuses'] as $status)
                            <tr><td>{{ $status['label'] }}</td><td class="number">{{ number_format($status['count']) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
            <td class="column">
                <h2>Khách hàng nổi bật</h2>
                <table class="data">
                    <thead><tr><th>Chỉ số</th><th>Khách hàng</th><th class="number">Giá trị</th></tr></thead>
                    <tbody>
                        <tr><td>Khách hàng mới</td><td>Trong kỳ</td><td class="number">{{ number_format($report['customers']['new_customers']) }}</td></tr>
                        <tr><td>Mua nhiều nhất</td><td>{{ $report['customers']['top_buyer']['customer_name'] ?? '—' }}</td><td class="number">{{ number_format($report['customers']['top_buyer']['order_count'] ?? 0) }} đơn</td></tr>
                        <tr><td>Chi tiêu nhiều nhất</td><td>{{ $report['customers']['top_spender']['customer_name'] ?? '—' }}</td><td class="number">{{ number_format($report['customers']['top_spender']['total_spent'] ?? 0, 0, ',', '.') }}đ</td></tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <div class="section flow">
        <h2>Doanh thu {{ mb_strtolower($report['filter']['chart_granularity_label']) }}</h2>
        <table class="data">
            <thead><tr><th>Thời gian</th><th class="number">Doanh thu</th></tr></thead>
            <tbody>
                @foreach($report['revenue_chart']['labels'] as $index => $label)
                    <tr><td>{{ $label }}</td><td class="number">{{ number_format($report['revenue_chart']['values'][$index], 0, ',', '.') }}đ</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <table class="two-columns section break">
        <tr>
            <td class="column">
                <h2>Top 10 sản phẩm bán chạy</h2>
                <table class="data">
                    <thead><tr><th>Sản phẩm</th><th class="number">Đã bán</th><th class="number">Doanh thu</th></tr></thead>
                    <tbody>
                        @forelse($report['products']['top_selling'] as $product)
                            <tr><td>{{ $product['product_name'] }}</td><td class="number">{{ number_format($product['sold_quantity']) }}</td><td class="number">{{ number_format($product['revenue'], 0, ',', '.') }}đ</td></tr>
                        @empty
                            <tr><td colspan="3">Chưa có dữ liệu</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
            <td class="column">
                <h2>Top 10 sản phẩm doanh thu cao</h2>
                <table class="data">
                    <thead><tr><th>Sản phẩm</th><th class="number">Đã bán</th><th class="number">Doanh thu</th></tr></thead>
                    <tbody>
                        @forelse($report['products']['top_revenue'] as $product)
                            <tr><td>{{ $product['product_name'] }}</td><td class="number">{{ number_format($product['sold_quantity']) }}</td><td class="number">{{ number_format($product['revenue'], 0, ',', '.') }}đ</td></tr>
                        @empty
                            <tr><td colspan="3">Chưa có dữ liệu</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <table class="two-columns section">
        <tr>
            <td class="column">
                <h2>Sản phẩm bán chậm</h2>
                <table class="data">
                    <thead><tr><th>Sản phẩm</th><th class="number">Đã bán</th><th class="number">Doanh thu</th></tr></thead>
                    <tbody>
                        @forelse($report['products']['slow_selling'] as $product)
                            <tr><td>{{ $product['product_name'] }}</td><td class="number">{{ number_format($product['sold_quantity']) }}</td><td class="number">{{ number_format($product['revenue'], 0, ',', '.') }}đ</td></tr>
                        @empty
                            <tr><td colspan="3">Chưa có dữ liệu</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
            <td class="column">
                <h2>Doanh thu theo danh mục</h2>
                <table class="data">
                    <thead><tr><th>Danh mục</th><th class="number">Đã bán</th><th class="number">Doanh thu</th></tr></thead>
                    <tbody>
                        @forelse($report['categories'] as $category)
                            <tr><td>{{ $category['category_name'] }}</td><td class="number">{{ number_format($category['sold_quantity']) }}</td><td class="number">{{ number_format($category['revenue'], 0, ',', '.') }}đ</td></tr>
                        @empty
                            <tr><td colspan="3">Chưa có dữ liệu</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <div class="section break">
        <h2>Danh sách đơn hàng trong kỳ</h2>
        <table class="data">
            <thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Email</th><th class="number">Khách trả</th><th class="number">Phí ship</th><th class="number">Doanh thu</th><th>Thanh toán</th><th>Trạng thái</th><th class="number">Ngày đặt</th></tr></thead>
            <tbody>
                @forelse($report['orders'] as $order)
                    @php
                        $orderStatus = match($order->order_status) {
                            'pending' => 'Chờ xác nhận',
                            'processing' => 'Đã xác nhận',
                            'shipping' => 'Đang giao',
                            'completed' => 'Hoàn thành',
                            default => 'Đã hủy',
                        };
                        $paymentStatus = match($order->payment_status) {
                            'paid' => 'Đã thanh toán',
                            'failed' => 'Thất bại',
                            default => 'Chờ thanh toán',
                        };
                    @endphp
                    <tr>
                        <td><strong>#{{ $order->order_code }}</strong></td>
                        <td>{{ $order->customer_name }}</td>
                        <td>{{ $order->customer_email ?: '—' }}</td>
                        <td class="number">{{ number_format($order->total_amount, 0, ',', '.') }}đ</td>
                        <td class="number">{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</td>
                        <td class="number">{{ number_format(max(0, $order->total_amount - $order->shipping_fee), 0, ',', '.') }}đ</td>
                        <td>{{ $paymentStatus }}</td>
                        <td><span class="badge">{{ $orderStatus }}</span></td>
                        <td class="number">{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9">Không có đơn hàng trong khoảng thời gian này.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
