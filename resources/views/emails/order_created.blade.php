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
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(30px);
        }
        @keyframes slideUp {
            to { opacity: 1; transform: translateY(0); }
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
            animation: fadeIn 1s ease-in-out forwards;
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
            margin-bottom: 20px;
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
    <div class="container">
        <div class="header">
            <h1>MAXBALL</h1>
            <p>Cảm ơn bạn đã mua sắm tại cửa hàng của chúng tôi!</p>
        </div>
        
        <div class="content">
            <div class="greeting">Xin chào {{ $order->customer_name }},</div>
            <p style="line-height: 1.6; color: #555;">Đơn hàng của bạn đã được hệ thống ghi nhận thành công. Dưới đây là thông tin chi tiết về đơn hàng của bạn.</p>
            
            <div class="order-info">
                <p><strong>Mã đơn hàng:</strong> #{{ $order->order_code }}</p>
                <p><strong>Ngày đặt:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
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
            
            <p style="margin-top: 40px; text-align: center; color: #777; font-size: 14px;">Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi qua email support@maxball.com.</p>
        </div>
        
        <div class="footer">
            &copy; {{ date('Y') }} MaxBall. All rights reserved.
        </div>
    </div>
</body>
</html>
