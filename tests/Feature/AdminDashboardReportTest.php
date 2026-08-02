<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\AdminDashboardReport;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminDashboardReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        CarbonImmutable::setTestNow('2026-08-02 15:00:00');
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_dashboard_applies_the_selected_range_and_calculates_business_metrics(): void
    {
        [$admin, $customerOne, $customerTwo, $variant] = $this->reportFixtures();

        $firstOrder = $this->createOrder($customerOne, 'completed', 'paid', 500000, '2026-08-02 09:00:00');
        $firstOrder->details()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'price' => 250000,
        ]);
        $secondOrder = $this->createOrder($customerTwo, 'completed', 'paid', 300000, '2026-08-02 10:00:00');
        $secondOrder->details()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'price' => 300000,
        ]);
        $this->createOrder($customerOne, 'cancelled', 'pending', 200000, '2026-08-02 11:00:00');
        $this->createOrder($customerOne, 'pending', 'pending', 100000, '2026-08-02 12:00:00');
        $this->createOrder($customerOne, 'completed', 'paid', 900000, '2026-07-01 12:00:00');

        $response = $this->actingAs($admin)->get(route('admin.dashboard', [
            'period' => 'custom',
            'start_date' => '2026-08-02',
            'end_date' => '2026-08-02',
            'chart_granularity' => 'day',
        ]))->assertOk();
        $report = $response->viewData('report');

        $this->assertSame(800000.0, $report['summary']['total_revenue']);
        $this->assertSame(60000.0, $report['summary']['shipping_collected']);
        $this->assertSame(2, $report['summary']['successful_orders']);
        $this->assertSame(2, $report['summary']['purchasing_customers']);
        $this->assertSame(400000.0, $report['summary']['average_order_value']);
        $this->assertSame(1, $report['summary']['cancelled_orders']);
        $this->assertSame(50.0, $report['summary']['completion_rate']);
        $this->assertSame([800000.0], $report['revenue_chart']['values']);
        $this->assertSame(3, $report['products']['top_selling']->first()['sold_quantity']);
        $this->assertSame('Áo đấu', $report['products']['top_selling']->first()['category_name']);
        $this->assertSame(800000.0, $report['products']['top_revenue']->first()['revenue']);
        $this->assertSame(3, $report['categories']->first()['sold_quantity']);
        $this->assertSame(2, $report['customers']['new_customers']);
        $this->assertCount(4, $report['orders']);

        $response
            ->assertDontSee('Thống kê doanh thu MaxBall')
            ->assertSee('Khu vực quản trị')
            ->assertSee('Thống kê kinh doanh')
            ->assertDontSee('placeholder="Tìm kiếm đơn hàng, khách hàng..."', false)
            ->assertSee('Xuất Excel')
            ->assertSee('Xuất PDF')
            ->assertSee('Hôm nay')
            ->assertSee('7 ngày gần nhất')
            ->assertSee('Chọn khoảng ngày')
            ->assertSee('Doanh thu theo thời gian')
            ->assertSee('Ngày')
            ->assertSee('Tuần')
            ->assertSee('Tháng')
            ->assertSee('Năm')
            ->assertSee('chartjs-plugin-datalabels@2')
            ->assertSee('context.chart.data.labels[context.dataIndex]', false)
            ->assertSee('Top 10 sản phẩm bán chạy')
            ->assertSee('Đơn hàng gần đây');
    }

    public function test_custom_range_rejects_an_end_date_before_the_start_date(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->from(route('admin.dashboard'))
            ->get(route('admin.dashboard', [
                'period' => 'custom',
                'start_date' => '2026-08-02',
                'end_date' => '2026-08-01',
                'chart_granularity' => 'day',
            ]))
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHasErrors('end_date');
    }

    public function test_revenue_chart_can_group_data_by_week_month_and_year(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'status' => true,
        ]);
        $this->createOrder($customer, 'completed', 'paid', 100000, '2025-01-15 10:00:00');
        $this->createOrder($customer, 'completed', 'paid', 200000, '2026-01-15 10:00:00');
        $this->createOrder($customer, 'completed', 'paid', 300000, '2026-08-01 10:00:00');
        $service = app(AdminDashboardReport::class);

        $weekly = $service->build([
            'period' => 'custom',
            'start_date' => '2026-07-27',
            'end_date' => '2026-08-02',
            'chart_granularity' => 'week',
        ]);
        $monthly = $service->build([
            'period' => 'this_year',
            'chart_granularity' => 'month',
        ]);
        $yearly = $service->build([
            'period' => 'custom',
            'start_date' => '2025-01-01',
            'end_date' => '2026-08-02',
            'chart_granularity' => 'year',
        ]);

        $this->assertSame([300000.0], $weekly['revenue_chart']['values']);
        $this->assertCount(8, $monthly['revenue_chart']['values']);
        $this->assertSame(200000.0, $monthly['revenue_chart']['values'][0]);
        $this->assertSame(300000.0, $monthly['revenue_chart']['values'][7]);
        $this->assertSame([100000.0, 500000.0], $yearly['revenue_chart']['values']);
    }

    public function test_admin_can_export_the_filtered_report_to_excel_and_pdf(): void
    {
        [$admin, $customerOne, , $variant] = $this->reportFixtures();
        $order = $this->createOrder($customerOne, 'completed', 'paid', 450000, '2026-08-02 09:00:00');
        $order->details()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'price' => 450000,
        ]);
        $query = [
            'period' => 'today',
            'chart_granularity' => 'day',
        ];

        $this->actingAs($admin)
            ->get(route('admin.dashboard.export.excel', $query))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertDownload('maxball-report-20260802-20260802.xlsx');

        $pdfResponse = $this->get(route('admin.dashboard.export.pdf', $query))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload('maxball-report-20260802-20260802.pdf');

        $this->assertStringStartsWith('%PDF-', $pdfResponse->getContent());
    }

    public function test_non_admin_cannot_access_dashboard_exports(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'status' => true,
        ]);

        $this->actingAs($customer)
            ->get(route('admin.dashboard.export.excel'))
            ->assertForbidden();
    }

    private function reportFixtures(): array
    {
        $admin = $this->admin();
        $customerOne = User::factory()->create([
            'role' => 'customer',
            'status' => true,
            'created_at' => '2026-08-02 08:00:00',
        ]);
        $customerTwo = User::factory()->create([
            'role' => 'customer',
            'status' => true,
            'created_at' => '2026-08-02 08:30:00',
        ]);
        $category = Category::create([
            'name' => 'Áo đấu',
            'slug' => 'ao-dau-'.Str::lower(Str::random(5)),
            'status' => true,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Áo MU sân nhà',
            'slug' => 'ao-mu-san-nha-'.Str::lower(Str::random(5)),
            'status' => true,
            'base_price' => 300000,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Đỏ - M',
            'sku' => 'MU-M-'.Str::upper(Str::random(5)),
            'base_price' => 300000,
            'stock' => 20,
        ]);

        return [$admin, $customerOne, $customerTwo, $variant];
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'status' => true,
            'created_at' => '2026-01-01 08:00:00',
        ]);
    }

    private function createOrder(
        User $customer,
        string $orderStatus,
        string $paymentStatus,
        int $total,
        string $createdAt
    ): Order {
        $order = Order::create([
            'user_id' => $customer->id,
            'order_code' => 'REPORT-'.Str::upper(Str::random(10)),
            'customer_name' => $customer->name,
            'customer_phone' => '0901234567',
            'customer_email' => $customer->email,
            'customer_address' => '123 Nguyễn Huệ, TP. Hồ Chí Minh',
            'sub_total' => $total,
            'shipping_fee' => 30000,
            'discount_amount' => 0,
            'total_amount' => $total + 30000,
            'payment_method' => 'cod',
            'payment_status' => $paymentStatus,
            'order_status' => $orderStatus,
        ]);

        $order->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->save();

        return $order;
    }
}
