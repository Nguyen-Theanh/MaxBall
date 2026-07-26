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
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            width: 100%;
        }
        .order-info td {
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
            padding: 11px 14px;
        }
        .order-info tr:last-child td {
            border-bottom: 0;
        }
        .order-info .label {
            color: #6b7280;
            width: 42%;
        }
        .order-info .value {
            color: #111827;
            font-weight: 700;
            text-align: right;
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
            <table class="order-info" role="presentation">
                <tr>
                    <td class="label">Mã đơn hàng</td>
                    <td class="value">#{{ $order->order_code }}</td>
                </tr>
                <tr>
                    <td class="label">Ngày đặt</td>
                    <td class="value">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td class="label">Thời gian hủy</td>
                    <td class="value">{{ $order->cancelled_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td class="label">Tổng thanh toán</td>
                    <td class="value" style="color: #dc2626;">{{ number_format($order->total_amount, 0, ',', '.') }}đ</td>
                </tr>
                <tr>
                    <td class="label">Phương thức thanh toán</td>
                    <td class="value">{{ strtoupper($order->payment_method) }}</td>
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
