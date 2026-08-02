<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DashboardReportRequest;
use App\Services\AdminDashboardReport;
use App\Services\DashboardReportExporter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DashboardController extends Controller
{
    public function index(
        DashboardReportRequest $request,
        AdminDashboardReport $dashboardReport
    ) {
        $report = $dashboardReport->build($request->validated());

        return view('admin.dashboard', compact('report'));
    }

    public function exportExcel(
        DashboardReportRequest $request,
        AdminDashboardReport $dashboardReport,
        DashboardReportExporter $exporter
    ): BinaryFileResponse {
        $report = $dashboardReport->build($request->validated());
        $path = $exporter->createExcel($report);
        $filename = $this->filename($report, 'xlsx');

        return response()->download(
            $path,
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    public function exportPdf(
        DashboardReportRequest $request,
        AdminDashboardReport $dashboardReport,
        DashboardReportExporter $exporter
    ) {
        $report = $dashboardReport->build($request->validated());
        $filename = $this->filename($report, 'pdf');

        return response($exporter->createPdf($report), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function filename(array $report, string $extension): string
    {
        return sprintf(
            'maxball-report-%s-%s.%s',
            $report['filter']['start']->format('Ymd'),
            $report['filter']['end']->format('Ymd'),
            $extension
        );
    }
}
