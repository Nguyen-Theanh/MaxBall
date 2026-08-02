<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;

class DashboardReportExporter
{
    public function createExcel(array $report): string
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setCreator('MaxBall')
            ->setTitle('Báo cáo thống kê kinh doanh MaxBall')
            ->setSubject($report['filter']['range_label']);
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        $this->buildOverviewSheet($spreadsheet, $report);
        $this->buildRevenueSheet($spreadsheet, $report);
        $this->buildProductSheet($spreadsheet, $report);
        $this->buildCategorySheet($spreadsheet, $report);
        $this->buildCustomerSheet($spreadsheet, $report);
        $this->buildOrderSheet($spreadsheet, $report);
        $spreadsheet->setActiveSheetIndex(0);

        $path = tempnam(sys_get_temp_dir(), 'maxball-report-');

        if ($path === false) {
            throw new RuntimeException('Không thể tạo tệp báo cáo tạm thời.');
        }

        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        return $path;
    }

    public function createPdf(array $report): string
    {
        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('admin.reports.dashboard-pdf', compact('report'))->render(), 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $dompdf->output();
    }

    private function buildRevenueSheet(Spreadsheet $spreadsheet, array $report): void
    {
        $sheet = $spreadsheet->createSheet()->setTitle('Doanh thu');
        $sheet->setCellValue('A1', $report['filter']['chart_granularity_label']);
        $sheet->setCellValue('B1', 'Doanh thu');

        foreach ($report['revenue_chart']['labels'] as $index => $label) {
            $row = $index + 2;
            $sheet->setCellValue("A{$row}", $label);
            $sheet->setCellValue("B{$row}", $report['revenue_chart']['values'][$index]);
        }

        $lastRow = max(2, count($report['revenue_chart']['labels']) + 1);
        $this->styleHeader($sheet, 'A1:B1');
        $sheet->freezePane('A2');
        $sheet->getStyle("B2:B{$lastRow}")->getNumberFormat()->setFormatCode('#,##0 "đ"');
        $this->autoSize($sheet, ['A', 'B']);
    }

    private function buildOverviewSheet(Spreadsheet $spreadsheet, array $report): void
    {
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Tổng quan');
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', 'BÁO CÁO THỐNG KÊ KINH DOANH MAXBALL');
        $sheet->mergeCells('A2:D2');
        $sheet->setCellValue('A2', 'Khoảng thời gian: '.$report['filter']['range_label']);
        $sheet->fromArray([
            ['Chỉ số', 'Giá trị'],
            ['Tổng doanh thu', $report['summary']['total_revenue']],
            ['Đơn hàng thành công', $report['summary']['successful_orders']],
            ['Khách hàng đã mua', $report['summary']['purchasing_customers']],
            ['Giá trị đơn trung bình', $report['summary']['average_order_value']],
            ['Phí vận chuyển đã loại trừ', $report['summary']['shipping_collected']],
            ['Đơn bị hủy', $report['summary']['cancelled_orders']],
            ['Tỷ lệ hoàn thành', $report['summary']['completion_rate'] / 100],
        ], null, 'A4');

        $statusStart = 4;
        $sheet->fromArray(['Trạng thái đơn', 'Số lượng'], null, "C{$statusStart}");
        foreach ($report['order_statuses'] as $index => $status) {
            $row = $statusStart + $index + 1;
            $sheet->setCellValue("C{$row}", $status['label']);
            $sheet->setCellValue("D{$row}", $status['count']);
        }

        $this->styleTitle($sheet, 'A1:D1');
        $this->styleHeader($sheet, 'A4:B4');
        $this->styleHeader($sheet, "C{$statusStart}:D{$statusStart}");
        $sheet->getStyle('B5:B5')->getNumberFormat()->setFormatCode('#,##0 "đ"');
        $sheet->getStyle('B8:B8')->getNumberFormat()->setFormatCode('#,##0 "đ"');
        $sheet->getStyle('B9:B9')->getNumberFormat()->setFormatCode('#,##0 "đ"');
        $sheet->getStyle('B11:B11')->getNumberFormat()->setFormatCode('0.0%');
        $this->autoSize($sheet, ['A', 'B', 'C', 'D']);
    }

    private function buildProductSheet(Spreadsheet $spreadsheet, array $report): void
    {
        $sheet = $spreadsheet->createSheet()->setTitle('Sản phẩm');
        $sheet->fromArray(['Nhóm thống kê', 'Sản phẩm', 'Đã bán', 'Doanh thu'], null, 'A1');
        $row = 2;

        foreach ([
            'Top bán chạy' => $report['products']['top_selling'],
            'Top doanh thu' => $report['products']['top_revenue'],
            'Bán chậm' => $report['products']['slow_selling'],
        ] as $group => $products) {
            foreach ($products as $product) {
                $sheet->fromArray([
                    $group,
                    $product['product_name'],
                    $product['sold_quantity'],
                    $product['revenue'],
                ], null, "A{$row}");
                $row++;
            }
        }

        $this->styleHeader($sheet, 'A1:D1');
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:D'.max(1, $row - 1));
        $sheet->getStyle('D2:D'.max(2, $row - 1))->getNumberFormat()->setFormatCode('#,##0 "đ"');
        $this->autoSize($sheet, ['A', 'B', 'C', 'D']);
    }

    private function buildCategorySheet(Spreadsheet $spreadsheet, array $report): void
    {
        $sheet = $spreadsheet->createSheet()->setTitle('Danh mục');
        $sheet->fromArray(['Danh mục', 'Số lượng bán', 'Doanh thu'], null, 'A1');

        foreach ($report['categories'] as $index => $category) {
            $sheet->fromArray([
                $category['category_name'],
                $category['sold_quantity'],
                $category['revenue'],
            ], null, 'A'.($index + 2));
        }

        $lastRow = max(2, $report['categories']->count() + 1);
        $this->styleHeader($sheet, 'A1:C1');
        $sheet->getStyle("C2:C{$lastRow}")->getNumberFormat()->setFormatCode('#,##0 "đ"');
        $this->autoSize($sheet, ['A', 'B', 'C']);
    }

    private function buildCustomerSheet(Spreadsheet $spreadsheet, array $report): void
    {
        $sheet = $spreadsheet->createSheet()->setTitle('Khách hàng');
        $sheet->setCellValue('A1', 'Khách hàng mới');
        $sheet->setCellValue('B1', $report['customers']['new_customers']);
        $sheet->fromArray(['Khách hàng', 'Email', 'Số đơn thành công', 'Tổng chi tiêu'], null, 'A3');

        foreach ($report['customers']['customers'] as $index => $customer) {
            $sheet->fromArray([
                $customer['customer_name'],
                $customer['customer_email'],
                $customer['order_count'],
                $customer['total_spent'],
            ], null, 'A'.($index + 4));
        }

        $lastRow = max(4, $report['customers']['customers']->count() + 3);
        $this->styleHeader($sheet, 'A3:D3');
        $sheet->getStyle("D4:D{$lastRow}")->getNumberFormat()->setFormatCode('#,##0 "đ"');
        $this->autoSize($sheet, ['A', 'B', 'C', 'D']);
    }

    private function buildOrderSheet(Spreadsheet $spreadsheet, array $report): void
    {
        $sheet = $spreadsheet->createSheet()->setTitle('Đơn hàng');
        $sheet->fromArray([
            'Mã đơn',
            'Khách hàng',
            'Email',
            'Khách thanh toán',
            'Phí vận chuyển',
            'Doanh thu',
            'Thanh toán',
            'Trạng thái',
            'Ngày đặt',
        ], null, 'A1');

        foreach ($report['orders'] as $index => $order) {
            $sheet->fromArray([
                $order->order_code,
                $order->customer_name,
                $order->customer_email,
                (float) $order->total_amount,
                (float) $order->shipping_fee,
                max(0.0, (float) $order->total_amount - (float) $order->shipping_fee),
                $this->paymentStatusLabel($order->payment_status),
                $this->orderStatusLabel($order->order_status),
                $order->created_at?->format('d/m/Y H:i'),
            ], null, 'A'.($index + 2));
        }

        $lastRow = max(2, $report['orders']->count() + 1);
        $this->styleHeader($sheet, 'A1:I1');
        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:I{$lastRow}");
        $sheet->getStyle("D2:F{$lastRow}")->getNumberFormat()->setFormatCode('#,##0 "đ"');
        $this->autoSize($sheet, ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I']);
    }

    private function styleTitle($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);
    }

    private function styleHeader($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DBEAFE']],
            ],
        ]);
    }

    private function autoSize($sheet, array $columns): void
    {
        foreach ($columns as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    private function orderStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Chờ xác nhận',
            'processing' => 'Đã xác nhận',
            'shipping' => 'Đang giao',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            default => $status,
        };
    }

    private function paymentStatusLabel(string $status): string
    {
        return match ($status) {
            'paid' => 'Đã thanh toán',
            'failed' => 'Thất bại',
            default => 'Chờ thanh toán',
        };
    }
}
