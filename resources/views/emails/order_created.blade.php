<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Xác nhận đơn hàng</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f5f7;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }
        .header {
            background: linear-gradient(135deg, #1a1a1a 0%, #333333 100%);
            color: #ffffff;
            text-align: center;
            padding: 40px 20px;
            position: relative;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .header p {
            margin: 10px 0 0;
            font-size: 16px;
            color: #e0e0e0;
        }
        .content {
            padding: 40px 30px;
            color: #333333;
        }
        .greeting {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 16px;
        }
        .intro {
            color: #555555;
            font-size: 15px;
            line-height: 1.7;
            margin: 0 0 16px;
        }
        .section-title {
            color: #1a1a1a;
            font-size: 18px;
            margin: 28px 0 14px;
        }
        .order-info {
            background-color: #f9fafb;
            border-left: 4px solid #d92525;
            padding: 15px 20px;
            border-radius: 4px;
            margin-bottom: 30px;
        }
        .order-info p {
            margin: 5px 0;
            font-size: 15px;
        }
        .table-container {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .table-container th {
            background-color: #f1f3f5;
            color: #495057;
            padding: 12px 15px;
            text-align: left;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #dee2e6;
        }
        .table-container td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }
        .product-name {
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 4px;
        }
        .product-variant {
            font-size: 13px;
            color: #6c757d;
        }
        .totals {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .totals td {
            padding: 10px 15px;
            text-align: right;
            font-size: 15px;
        }
        .totals .label {
            color: #6c757d;
        }
        .totals .value {
            font-weight: 600;
            color: #333333;
            width: 150px;
        }
        .totals .grand-total .label {
            font-size: 18px;
            font-weight: 700;
            color: #1a1a1a;
            border-top: 2px solid #dee2e6;
            padding-top: 20px;
        }
        .totals .grand-total .value {
            font-size: 22px;
            font-weight: 800;
            color: #d92525;
            border-top: 2px solid #dee2e6;
            padding-top: 20px;
        }
        .notice-box {
            background-color: #fff8e6;
            border: 1px solid #f4d58d;
            border-radius: 8px;
            color: #604800;
            font-size: 14px;
            line-height: 1.7;
            margin-top: 30px;
            padding: 16px 18px;
        }
        .support-box {
            background-color: #f7f8fa;
            border-radius: 8px;
            color: #555555;
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
            color: #d92525;
            font-weight: 600;
            text-decoration: none;
        }
        .closing {
            color: #555555;
            font-size: 15px;
            line-height: 1.7;
            margin-top: 28px;
        }
        .footer {
            background-color: #1a1a1a;
            color: #888888;
            text-align: center;
            padding: 20px;
            font-size: 13px;
        }
        .badge-pending { color: #856404; background-color: #fff3cd; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px; }
        .badge-paid { color: #155724; background-color: #d4edda; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px; }
    </style>
</head>
<body>
    <div style="display: none; max-height: 0; overflow: hidden; opacity: 0;">
        Đơn hàng #{{ $order->order_code }} của bạn đã được MaxBall ghi nhận thành công.
    </div>

    <div class="container">
        <div class="header">
            <h1>MAXBALL</h1>
            <p>Xác nhận đơn hàng thành công</p>
        </div>
        
        <div class="content">
            <div class="greeting">Kính gửi {{ $order->customer_name }},</div>
            <p class="intro">Cảm ơn bạn đã tin tưởng và lựa chọn mua sắm tại <strong>MaxBall</strong>!</p>
            <p class="intro">Đơn hàng của bạn đã được ghi nhận thành công và chúng tôi sẽ tiến hành xử lý trong thời gian sớm nhất.</p>

            <h2 class="section-title">Thông tin đơn hàng</h2>
            
            <div class="order-info">
                <p><strong>Mã đơn hàng:</strong> #{{ $order->order_code }}</p>
                <p><strong>Ngày đặt:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>Tổng thanh toán:</strong> <span style="color: #d92525; font-size: 17px; font-weight: 700;">{{ number_format($order->total_amount, 0, ',', '.') }}đ</span></p>
                <p><strong>Phương thức thanh toán:</strong> {{ strtoupper($order->payment_method) }}</p>
                <p>
                    <strong>Trạng thái thanh toán:</strong> 
                    @if($order->payment_status == 'paid')
                        <span class="badge-paid">Đã thanh toán</span>
                    @else
                        <span class="badge-pending">Chưa thanh toán</span>
                    @endif
                </p>
                <p><strong>Địa chỉ nhận hàng:</strong> {{ $order->customer_address }} (SĐT: {{ $order->customer_phone }})</p>
            </div>

            <h2 class="section-title">Chi tiết sản phẩm</h2>

            <table class="table-container">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th style="text-align: center;">SL</th>
                        <th style="text-align: right;">Giá</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->details as $detail)
                    <tr>
                        <td>
                            <div class="product-name">{{ $detail->variant->product->name }}</div>
                            <div class="product-variant">Phân loại: {{ $detail->variant->name }}</div>
                        </td>
                        <td style="text-align: center; font-weight: 600;">{{ $detail->quantity }}</td>
                        <td style="text-align: right; color: #555;">{{ number_format($detail->price, 0, ',', '.') }}đ</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="totals">
                <tr>
                    <td class="label">Tạm tính:</td>
                    <td class="value">{{ number_format($order->sub_total, 0, ',', '.') }}đ</td>
                </tr>
                <tr>
                    <td class="label">Phí vận chuyển:</td>
                    <td class="value">{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</td>
                </tr>
                @if($order->discount_amount > 0)
                <tr>
                    <td class="label">Giảm giá:</td>
                    <td class="value" style="color: #28a745;">-{{ number_format($order->discount_amount, 0, ',', '.') }}đ</td>
                </tr>
                @endif
                <tr class="grand-total">
                    <td class="label">Tổng cộng:</td>
                    <td class="value">{{ number_format($order->total_amount, 0, ',', '.') }}đ</td>
                </tr>
            </table>

            <div class="notice-box">
                @if($order->payment_method === 'cod' && $order->reservation_expires_at)
                    Sản phẩm trong đơn đang được giữ đến {{ $order->reservation_expires_at->format('H:i d/m/Y') }} để cửa hàng xác nhận. Nếu cửa hàng từ chối hoặc quá thời hạn này, đơn sẽ tự động hủy và hàng được trả lại kho khả dụng.
                @else
                    Chúng tôi sẽ gửi email thông báo khi đơn hàng được xác nhận, đóng gói và giao cho đơn vị vận chuyển.
                @endif
            </div>

            <div class="support-box">
                <p>Nếu có bất kỳ thắc mắc hoặc cần hỗ trợ, vui lòng liên hệ với chúng tôi qua:</p>
                <ul>
                    <li>Email: <a href="mailto:support@maxball.com">support@maxball.com</a></li>
                    <li>Hotline: <a href="tel:0123456789">0123 456 789</a></li>
                </ul>
            </div>

            <div class="closing">
                <p>Một lần nữa, xin chân thành cảm ơn bạn đã đồng hành cùng <strong>MaxBall</strong>. Chúc bạn có những trải nghiệm tuyệt vời với sản phẩm của chúng tôi!</p>
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
