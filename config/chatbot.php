<?php

return [
    'store_knowledge' => [
        'contact' => [
            'store_name' => 'MaxBall',
            'hotline' => '0987.654.321',
            'hotline_hours' => 'Hỗ trợ 24/7',
            'email' => 'tuankaka554@gmail.com',
            'address' => '123 Đường Bóng Đá, Quận Thể Thao, TP. Hà Nội',
        ],

        'payment_methods' => [
            'Thanh toán khi nhận hàng (COD). Đơn được giữ hàng tối đa 24 giờ để cửa hàng xác nhận.',
            'Chuyển khoản bằng VietQR qua ứng dụng ngân hàng hoặc MoMo.',
            'Thanh toán bằng Ví MaxBall khi số dư đủ.',
        ],

        'size_guide' => [
            'chart' => [
                'S' => ['height_cm' => [155, 164], 'weight_kg' => [42, 50]],
                'M' => ['height_cm' => [160, 169], 'weight_kg' => [50, 58]],
                'L' => ['height_cm' => [165, 175], 'weight_kg' => [58, 68]],
                'XL' => ['height_cm' => [170, 180], 'weight_kg' => [68, 78]],
                'XXL' => ['height_cm' => [175, 185], 'weight_kg' => [78, 88]],
                '3XL' => ['height_cm' => [180, 190], 'weight_kg' => [88, 100]],
            ],
            'notes' => [
                'Bảng size chỉ mang tính tham khảo; form thực tế có thể khác nhau tùy mẫu áo.',
                'Với sản phẩm cụ thể, size được tư vấn phải có trong các biến thể đang bán của sản phẩm.',
            ],
            'rules' => [
                'Luôn dùng cả chiều cao và cân nặng khi khách cung cấp đủ hai thông số.',
                'Nếu cả hai thông số cùng thuộc một size, ưu tiên size đó.',
                'Nếu người cao nhưng nhẹ cân, ưu tiên size theo chiều cao để bảo đảm chiều dài áo.',
                'Nếu người thấp nhưng cân nặng cao, ưu tiên size theo cân nặng để bảo đảm độ rộng áo.',
                'Nếu ở sát ranh giới, khách thích mặc ôm/gọn chọn size nhỏ hơn; thích rộng/thoải mái chọn size lớn hơn.',
                'Nếu thiếu chiều cao hoặc cân nặng, hỏi thêm thông số còn thiếu trước khi chốt size.',
                'Không nói số đo không khớp hoàn toàn chỉ vì hai thông số thuộc hai khoảng size khác nhau.',
            ],
        ],
    ],
];
