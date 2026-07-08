<!DOCTYPE html>
<html>
<head>
    <title>Liên Hệ Mới</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f9f9f9; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .header { text-align: center; border-bottom: 2px solid #d92525; padding-bottom: 20px; margin-bottom: 20px; }
        .header h1 { color: #10271d; margin: 0; }
        .info-group { margin-bottom: 15px; }
        .info-group strong { display: inline-block; width: 120px; color: #555; }
        .message-box { background: #fcfaf6; padding: 20px; border-left: 4px solid #d92525; border-radius: 0 5px 5px 0; margin-top: 20px; white-space: pre-line; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>MAXBALL</h1>
            <p>Thông báo: Có một liên hệ mới từ Website</p>
        </div>
        
        <div class="info-group">
            <strong>Họ và tên:</strong> {{ $data['name'] }}
        </div>
        <div class="info-group">
            <strong>Số điện thoại:</strong> {{ $data['phone'] }}
        </div>
        <div class="info-group">
            <strong>Email:</strong> {{ $data['email'] }}
        </div>
        
        <h3 style="margin-top: 30px; margin-bottom: 10px; color: #10271d;">Nội dung tin nhắn:</h3>
        <div class="message-box">
            {{ $data['message'] }}
        </div>

        <p style="text-align: center; margin-top: 40px; font-size: 12px; color: #999;">Email này được gửi tự động từ hệ thống Website MaxBall.</p>
    </div>
</body>
</html>
