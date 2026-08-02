<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đơn hàng đã được hủy</title>
    <style>
        body {
            background-color: #f4f5f7;
            color: #333333;
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
            margin: 32px auto;
            max-width: 620px;
            overflow: hidden;
        }
        .header {
            background-color: #7f1d1d;
            color: #ffffff;
            padding: 34px 24px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            letter-spacing: 1px;
            margin: 0;
        }
        .header p {
            color: #fecaca;
            font-size: 16px;
            margin: 10px 0 0;
        }
        .content {
            padding: 34px 30px;
        }
        .greeting {
            color: #1f2937;
            font-size: 19px;
            font-weight: 700;
            margin: 0 0 18px;
        }
        .paragraph {
            color: #4b5563;
            font-size: 15px;
            line-height: 1.75;
            margin: 0 0 16px;
        }
        .case-message {
            background-color: #fef2f2;
            border-left: 4px solid #dc2626;
            border-radius: 6px;
            color: #7f1d1d;
            font-size: 15px;
            line-height: 1.7;
            margin: 20px 0 26px;
            padding: 16px 18px;
        }
        .section-title {
            color: #111827;
            font-size: 18px;
            margin: 26px 0 14px;
        }
        .order-info {
            background-color: #f9fafb;
            border-left: 4px solid #d92525;
            border-radius: 4px;
            margin-bottom: 30px;
            padding: 15px 20px;
        }
        .order-info p {
            font-size: 15px;
            margin: 5px 0;
        }
        .badge-pending {
            background-color: #fff3cd;
            border-radius: 4px;
            color: #856404;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 8px;
        }
        .badge-paid {
            background-color: #d4edda;
            border-radius: 4px;
            color: #155724;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 8px;
        }
        .product-table {
            border-collapse: collapse;
            margin-bottom: 18px;
            width: 100%;
        }
        .product-table th {
            background-color: #f3f4f6;
            border-bottom: 2px solid #d1d5db;
            color: #4b5563;
            font-size: 12px;
            letter-spacing: 0.3px;
            padding: 11px 10px;
            text-align: left;
            text-transform: uppercase;
        }
        .product-table td {
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
            font-size: 13px;
            padding: 13px 10px;
            vertical-align: top;
        }
        .product-name {
            color: #111827;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .product-variant {
            color: #6b7280;
            font-size: 12px;
            line-height: 1.5;
        }
        .totals {
            border-collapse: collapse;
            margin: 0 0 26px auto;
            width: 100%;
        }
        .totals td {
            color: #4b5563;
            font-size: 14px;
            padding: 7px 10px;
            text-align: right;
        }
        .totals .value {
            color: #111827;
            font-weight: 700;
            width: 145px;
        }
        .totals .discount .value {
            color: #15803d;
        }
        .totals .grand-total td {
            border-top: 2px solid #d1d5db;
            color: #111827;
            font-size: 16px;
            font-weight: 700;
            padding-top: 13px;
        }
        .totals .grand-total .value {
            color: #dc2626;
            font-size: 19px;
        }
        .reason-box {
            background-color: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: 8px;
            color: #7c2d12;
            font-size: 15px;
            line-height: 1.7;
            padding: 16px 18px;
        }
        .reason-box p {
            margin: 0;
        }
        .reason-box .note {
            border-top: 1px solid #fed7aa;
            margin-top: 10px;
            padding-top: 10px;
            white-space: pre-line;
        }
        .refund-box {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            color: #1e3a8a;
            font-size: 14px;
            line-height: 1.7;
            margin-top: 24px;
            padding: 16px 18px;
        }
        .support-box {
            background-color: #f9fafb;
            border-radius: 8px;
            color: #4b5563;
            font-size: 14px;
            line-height: 1.8;
            margin-top: 24px;
            padding: 18px 20px;
        }
        .support-box p {
            margin: 0 0 8px;
        }
        .support-box ul {
            margin: 0;
            padding-left: 20px;
        }
        .support-box a {
            color: #dc2626;
            font-weight: 700;
            text-decoration: none;
        }
        .closing {
            color: #4b5563;
            font-size: 15px;
            line-height: 1.7;
            margin-top: 26px;
        }
        .footer {
            background-color: #111827;
            color: #9ca3af;
            font-size: 13px;
            padding: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div style="display: none; max-height: 0; overflow: hidden; opacity: 0;">
        Đơn hàng #{{ $order->order_code }} của bạn đã được hủy.
    </div>

    <div class="container">
        <div class="header">
            <h1>MAXBALL</h1>
            <p>Thông báo hủy đơn hàng</p>
        </div>

        <div class="content">
            <p class="greeting">Kính gửi {{ $order->customer_name }},</p>
            <p class="paragraph">Chúng tôi xin thông báo rằng đơn hàng <strong>#{{ $order->order_code }}</strong> của bạn đã được <strong>hủy thành công</strong>.</p>

            <div class="case-message">{{ $cancellationMessage }}</div>

            <h2 class="section-title">Thông tin đơn hàng</h2>
            <div class="order-info">
                <p><strong>Mã đơn hàng:</strong> #{{ $order->order_code }}</p>
                <p><strong>Ngày đặt:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>Tổng thanh toán:</strong> <span style="color: #d92525; font-size: 17px; font-weight: 700;">{{ number_format($order->total_amount, 0, ',', '.') }}đ</span></p>
                <p><strong>Phương thức thanh toán:</strong> {{ strtoupper($order->payment_method) }}</p>
                <p>
                    <strong>Trạng thái thanh toán:</strong>
                    @if($order->payment_status === 'paid')
                        <span class="badge-paid">Đã thanh toán</span>
                    @else
                        <span class="badge-pending">Chưa thanh toán</span>
                    @endif
                </p>
                <p><strong>Địa chỉ nhận hàng:</strong> {{ $order->customer_address }} (SĐT: {{ $order->customer_phone }})</p>
            </div>

            <h2 class="section-title">Chi tiết sản phẩm đã hủy</h2>
            <table class="product-table" role="presentation">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th style="text-align: center;">SL</th>
                        <th style="text-align: right;">Đơn giá</th>
                        <th style="text-align: right;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->details as $detail)
                        <tr>
                            <td>
                                <div class="product-name">{{ $detail->variant?->product?->name ?? 'Sản phẩm' }}</div>
                                @if($detail->variant?->name)
                                    <div class="product-variant">Phân loại: {{ $detail->variant->name }}</div>
                                @endif
                                @if($detail->print_name || $detail->print_number)
                                    <div class="product-variant">
                                        In áo:
                                        {{ $detail->print_name ?: 'Không tên' }}
                                        {{ $detail->print_number ? ' - Số '.$detail->print_number : '' }}
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: center; font-weight: 700;">{{ $detail->quantity }}</td>
                            <td style="text-align: right; white-space: nowrap;">{{ number_format($detail->price, 0, ',', '.') }}đ</td>
                            <td style="text-align: right; font-weight: 700; white-space: nowrap;">{{ number_format($detail->price * $detail->quantity, 0, ',', '.') }}đ</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="totals" role="presentation">
                <tr>
                    <td>Tạm tính:</td>
                    <td class="value">{{ number_format($order->sub_total, 0, ',', '.') }}đ</td>
                </tr>
                <tr>
                    <td>Phí vận chuyển:</td>
                    <td class="value">{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</td>
                </tr>
                @if($order->discount_amount > 0)
                    <tr class="discount">
                        <td>Giảm giá:</td>
                        <td class="value">-{{ number_format($order->discount_amount, 0, ',', '.') }}đ</td>
                    </tr>
                @endif
                <tr class="grand-total">
                    <td>Tổng giá trị đơn đã hủy:</td>
                    <td class="value">{{ number_format($order->total_amount, 0, ',', '.') }}đ</td>
                </tr>
            </table>

            <h2 class="section-title">Lý do hủy đơn hàng</h2>
            <div class="reason-box">
                <p><strong>{{ $order->cancellation_reason_label }}</strong></p>
                @if($order->cancellation_note)
                    <p class="note"><strong>Ghi chú:</strong> {{ $order->cancellation_note }}</p>
                @endif
            </div>

            @if($order->payment_status === 'paid')
                <div class="refund-box">
                    Đơn hàng đã được ghi nhận thanh toán. Chúng tôi sẽ tiến hành hoàn tiền theo phương thức thanh toán ban đầu (nếu áp dụng). Thời gian hoàn tiền có thể khác nhau tùy theo ngân hàng hoặc đơn vị thanh toán.
                </div>
            @else
                <div class="refund-box">
                    Đơn hàng chưa được ghi nhận thanh toán nên không phát sinh yêu cầu hoàn tiền.
                </div>
            @endif

            <p class="paragraph" style="margin-top: 24px;">Chúng tôi rất tiếc vì sự bất tiện này và hy vọng sẽ có cơ hội được phục vụ bạn trong những lần mua sắm tiếp theo.</p>

            <div class="support-box">
                <p>Nếu bạn cần hỗ trợ hoặc muốn đặt lại đơn hàng, vui lòng liên hệ với chúng tôi:</p>
                <ul>
                    <li>Email: <a href="mailto:support@maxball.com">support@maxball.com</a></li>
                    <li>Hotline: <a href="tel:0123456789">0123 456 789</a></li>
                </ul>
            </div>

            <div class="closing">
                <p>Xin chân thành cảm ơn bạn đã quan tâm và ủng hộ <strong>MaxBall</strong>.</p>
                <p>Trân trọng,</p>
                <p><strong>Đội ngũ MaxBall</strong></p>
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} MaxBall. All rights reserved.
        </div>
    </div>
</body>
</html>
