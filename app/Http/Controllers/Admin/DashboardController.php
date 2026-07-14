<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Thống kê tổng quan
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_amount');
        $totalOrders = Order::count();
        $pendingOrders = Order::where('order_status', 'pending')->count();
        $totalCustomers = User::where('role', 'customer')->count();

        // 2. Dữ liệu biểu đồ doanh thu trong 30 ngày gần nhất
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $revenueData = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $data = [];
        
        // Fill missing days with 0
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::now()->subDays($i)->format('d/m');
            
            $dayData = $revenueData->firstWhere('date', $date);
            $data[] = $dayData ? $dayData->total : 0;
        }

        // 3. Đơn hàng mới nhất cần xử lý
        $recentOrders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalRevenue',
            'totalOrders',
            'pendingOrders',
            'totalCustomers',
            'labels',
            'data',
            'recentOrders'
        ));
    }
}
