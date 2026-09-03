<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\User;
use App\Models\ProductVariant;
use Illuminate\Support\Str;
use Carbon\Carbon;

class GenerateFakeOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-fake-orders {--count=150} {--days=30}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tạo dữ liệu đơn hàng ảo cho trang thống kê';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->option('count');
        $days = (int) $this->option('days');

        $users = User::pluck('id')->toArray();
        $variants = ProductVariant::with('product')->get();

        if (empty($users)) {
            $this->error('Không tìm thấy người dùng nào.');
            return;
        }

        if ($variants->isEmpty()) {
            $this->error('Không tìm thấy biến thể sản phẩm nào.');
            return;
        }

        $statuses = ['pending', 'confirmed', 'shipping', 'completed', 'canceled'];
        $paymentMethods = ['cod', 'wallet', 'vietqr'];
        $paymentStatuses = ['unpaid', 'paid'];

        $bar = $this->output->createProgressBar($count);

        $vietnameseNames = [
            'Nguyễn Văn An', 'Trần Thị Bình', 'Lê Hoàng Khang', 'Phạm Thị Dung', 'Hoàng Thanh Hải',
            'Võ Trọng Nghĩa', 'Đặng Kim Oanh', 'Bùi Văn Phú', 'Đỗ Thị Thu', 'Ngô Quốc Toàn',
            'Dương Mỹ Linh', 'Lý Bảo Nam', 'Hồ Tuyết Mai', 'Phùng Xuân Bắc', 'Vũ Hải Yến',
            'Châu Ngọc Sơn', 'Đinh Công Trí', 'Trương Thị Hoa', 'Lâm Nhật Minh', 'Phan Tấn Phát',
            'Đào Phương Trinh', 'Trịnh Xuân Trường', 'Cao Minh Đức', 'Đoàn Thanh Tùng', 'Đỗ Gia Bảo'
        ];

        for ($i = 0; $i < $count; $i++) {
            $date = Carbon::now()->subDays(rand(0, $days))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
            
            // 60% tỉ lệ hoàn thành để biểu đồ đẹp
            $rand = rand(1, 100);
            if ($rand <= 60) {
                $status = 'completed';
            } elseif ($rand <= 75) {
                $status = 'canceled';
            } elseif ($rand <= 85) {
                $status = 'pending';
            } elseif ($rand <= 95) {
                $status = 'confirmed';
            } else {
                $status = 'shipping';
            }
            
            if ($status === 'completed' || $status === 'shipping') {
                $paymentStatus = 'paid';
            } else {
                $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];
            }
            
            $method = $paymentMethods[array_rand($paymentMethods)];
            if ($method === 'cod' && $status !== 'completed' && $status !== 'shipping') {
                $paymentStatus = 'unpaid';
            }

            $order = new Order();
            $order->user_id = $users[array_rand($users)];
            $order->order_code = strtoupper(Str::random(10));
            $order->customer_name = $vietnameseNames[array_rand($vietnameseNames)];
            $order->customer_phone = '098' . rand(1000000, 9999999);
            $order->customer_address = 'Số ' . rand(1, 100) . ' Đường ABC, Hà Nội';
            $order->sub_total = 0;
            $order->shipping_fee = rand(0, 1) ? 30000 : 0;
            $order->discount_amount = 0;
            $order->total_amount = 0;
            $order->payment_method = $method;
            $order->payment_status = $paymentStatus;
            $order->order_status = $status;
            
            if ($status === 'canceled') {
                $order->cancellation_reason = 'other';
                $order->cancellation_note = 'Tự động hủy (Dữ liệu ảo)';
                $order->cancelled_at = $date->copy()->addHours(1);
            }
            
            $order->created_at = $date;
            $order->updated_at = $date;
            $order->save();

            $numItems = rand(1, 3);
            $subTotal = 0;
            for ($j = 0; $j < $numItems; $j++) {
                $variant = $variants->random();
                $price = $variant->discount_price ?: $variant->base_price;
                $quantity = rand(1, 2);
                
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                    'price' => $price,
                ]);
                $subTotal += $price * $quantity;
            }
            
            if ($subTotal >= 500000) {
                $order->shipping_fee = 0;
            }
            
            $order->sub_total = $subTotal;
            $order->total_amount = $subTotal + $order->shipping_fee;
            
            // We need to bypass timestamps update to keep the random created_at
            $order->timestamps = false;
            $order->save();
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Đã tạo thành công {$count} đơn hàng ảo trong {$days} ngày qua.");
    }
}
