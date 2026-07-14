@extends('admin.layouts.app')

@section('title', 'Tổng quan (Dashboard)')

@push('styles')
<style>
    .card-metric {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        border-radius: 15px;
        overflow: hidden;
    }
    .card-metric:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .metric-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }
    .bg-gradient-primary { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); color: white; }
    .bg-gradient-success { background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%); color: white; }
    .bg-gradient-info { background: linear-gradient(135deg, #36b9cc 0%, #258391 100%); color: white; }
    .bg-gradient-warning { background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%); color: white; }

    /* Animations */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-up {
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
    }
    .delay-100 { animation-delay: 0.1s; }
    .delay-200 { animation-delay: 0.2s; }
    .delay-300 { animation-delay: 0.3s; }
    .delay-400 { animation-delay: 0.4s; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 animate-fade-up">
        <h1 class="h3 mb-0 text-gray-800 fw-bold">Tổng quan hệ thống</h1>
        <a href="#" class="btn btn-sm btn-primary shadow-sm"><i class="fas fa-download fa-sm text-white-50"></i> Xuất Báo Cáo</a>
    </div>

    <!-- Metrics Row -->
    <div class="row mb-4">
        <!-- Doanh thu -->
        <div class="col-xl-3 col-md-6 mb-4 animate-fade-up delay-100">
            <div class="card card-metric h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Tổng Doanh Thu</div>
                            <div class="h4 mb-0 fw-bold text-gray-800">{{ number_format($totalRevenue, 0, ',', '.') }}đ</div>
                        </div>
                        <div class="metric-icon bg-gradient-success shadow">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tổng đơn hàng -->
        <div class="col-xl-3 col-md-6 mb-4 animate-fade-up delay-200">
            <div class="card card-metric h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">Tổng Đơn Hàng</div>
                            <div class="h4 mb-0 fw-bold text-gray-800">{{ number_format($totalOrders) }}</div>
                        </div>
                        <div class="metric-icon bg-gradient-primary shadow">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Đơn hàng chờ xác nhận -->
        <div class="col-xl-3 col-md-6 mb-4 animate-fade-up delay-300">
            <div class="card card-metric h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">Chờ Xác Nhận</div>
                            <div class="h4 mb-0 fw-bold text-gray-800">{{ number_format($pendingOrders) }}</div>
                        </div>
                        <div class="metric-icon bg-gradient-warning shadow">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Khách hàng -->
        <div class="col-xl-3 col-md-6 mb-4 animate-fade-up delay-400">
            <div class="card card-metric h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">Tổng Khách Hàng</div>
                            <div class="h4 mb-0 fw-bold text-gray-800">{{ number_format($totalCustomers) }}</div>
                        </div>
                        <div class="metric-icon bg-gradient-info shadow">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7 animate-fade-up delay-200">
            <div class="card shadow border-0 h-100" style="border-radius: 15px;">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="m-0 fw-bold text-primary">Biểu Đồ Doanh Thu (30 Ngày)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 350px;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="col-xl-4 col-lg-5 animate-fade-up delay-300">
            <div class="card shadow border-0 h-100" style="border-radius: 15px;">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="m-0 fw-bold text-primary">Đơn Hàng Mới Nhất</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush border-top-0">
                        @forelse($recentOrders as $order)
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="list-group-item list-group-item-action p-3 border-bottom-0 border-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 fw-bold text-dark">#{{ $order->order_code }}</h6>
                                        <p class="mb-0 text-muted small"><i class="fas fa-user-circle me-1"></i> {{ $order->customer_name }}</p>
                                    </div>
                                    <div class="text-end">
                                        <div class="text-danger fw-bold">{{ number_format($order->total_amount, 0, ',', '.') }}đ</div>
                                        <small class="text-muted">{{ $order->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="p-4 text-center text-muted">
                                Không có đơn hàng nào gần đây.
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="card-footer bg-white text-center border-0" style="border-radius: 0 0 15px 15px;">
                    <a href="{{ route('admin.orders.index') }}" class="text-primary text-decoration-none fw-bold small">Xem tất cả đơn hàng <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        
        // Gradient cho background
        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(78, 115, 223, 0.5)');
        gradient.addColorStop(1, 'rgba(78, 115, 223, 0.05)');

        const dataLabels = {!! json_encode($labels) !!};
        const dataValues = {!! json_encode($data) !!};

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: dataLabels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: dataValues,
                    backgroundColor: gradient,
                    borderColor: 'rgba(78, 115, 223, 1)',
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: 'rgba(78, 115, 223, 1)',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4 // Làm cong đường đồ thị
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#858796',
                        bodyColor: '#5a5c69',
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        padding: 15,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            maxTicksLimit: 7
                        }
                    },
                    y: {
                        grid: {
                            color: "rgb(234, 236, 244)",
                            zeroLineColor: "rgb(234, 236, 244)",
                            drawBorder: false,
                            borderDash: [2],
                            zeroLineBorderDash: [2]
                        },
                        ticks: {
                            maxTicksLimit: 5,
                            padding: 10,
                            callback: function(value, index, values) {
                                if (value >= 1000000) {
                                    return (value / 1000000) + 'Tr';
                                } else if (value >= 1000) {
                                    return (value / 1000) + 'k';
                                }
                                return value;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
