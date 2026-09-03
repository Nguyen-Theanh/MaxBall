<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu đóng hàng</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #eef1f5;
            color: #111827;
            font-family: Arial, "Helvetica Neue", sans-serif;
            font-size: 16px;
            line-height: 1.5;
        }

        .print-toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 12px;
            background: #ffffff;
            border-bottom: 1px solid #d1d5db;
        }

        .print-toolbar button {
            padding: 9px 18px;
            border: 0;
            border-radius: 6px;
            background: #111827;
            color: #ffffff;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        .packing-slip {
            width: 148mm;
            min-height: 210mm;
            margin: 18px auto;
            padding: 18mm 16mm;
            background: #ffffff;
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.12);
        }

        .order-code {
            margin: 0 0 24px;
            font-size: 24px;
            font-weight: 800;
        }

        .order-item {
            padding: 0 0 20px;
        }

        .order-item + .order-item {
            padding-top: 20px;
            border-top: 1px dashed #9ca3af;
        }

        .product-name {
            margin-bottom: 5px;
            font-size: 18px;
            font-weight: 700;
        }

        .item-line {
            margin: 2px 0;
        }

        @page {
            size: A5 portrait;
            margin: 0;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .print-toolbar {
                display: none;
            }

            .packing-slip {
                width: 148mm;
                min-height: 210mm;
                margin: 0;
                box-shadow: none;
                break-after: page;
                page-break-after: always;
            }

            .packing-slip:last-child {
                break-after: auto;
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>
    <div class="print-toolbar">
        <button type="button" onclick="window.print()">
            In {{ $packingOrders->count() }} phiếu đóng hàng
        </button>
    </div>

    @foreach($packingOrders as $packingOrder)
        <section class="packing-slip">
            <h1 class="order-code">Đơn #{{ $packingOrder['code'] }}</h1>

            @foreach($packingOrder['items'] as $item)
                <article class="order-item">
                    <div class="product-name">{{ $item['name'] }}</div>

                    @foreach($item['options'] as $optionName => $optionValue)
                        <div class="item-line">
                            {{ $optionName }}: {{ $optionValue }}
                        </div>
                    @endforeach

                    <div class="item-line">
                        Số lượng: {{ $item['quantity'] }}
                    </div>
                </article>
            @endforeach
        </section>
    @endforeach

    <script>
        window.addEventListener('load', function () {
            window.setTimeout(function () {
                window.print();
            }, 200);
        });
    </script>
</body>
</html>
