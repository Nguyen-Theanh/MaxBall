@extends('admin.layouts.app')

@section('title', 'Tổng quan (Dashboard)')

@section('content')
<div class="space-y-6">
    <!-- Welcome Header -->
    <div class="flex justify-between items-end animate-slide-up">
        <div>
            <h1 class="text-3xl text-gray-800 font-light mb-1">
                Good Morning, <span class="font-bold">{{ auth()->user()->name ?? 'Admin' }}</span>
            </h1>
            <p class="text-gray-500 text-sm">Your performance summary this week</p>
        </div>
        <div class="hidden sm:flex gap-3">
            <button class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors shadow-sm text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
                Share
            </button>
            <button class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors shadow-sm text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print
            </button>
            <button class="flex items-center gap-2 px-4 py-2 bg-blue-600 border border-blue-600 rounded-xl text-white hover:bg-blue-700 transition-colors shadow-sm text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                Export
            </button>
        </div>
    </div>

    <!-- Divider / Tabs -->
    <div class="border-b border-gray-200 animate-slide-up delay-100">
        <nav class="-mb-px flex space-x-8">
            <a href="#" class="border-blue-600 text-blue-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Overview</a>
            <a href="#" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Audiences</a>
            <a href="#" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Demographics</a>
            <a href="#" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">More</a>
        </nav>
    </div>

    <!-- Metrics Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 animate-slide-up delay-200">
        
        <!-- Doanh thu -->
        <div class="bg-white rounded-2xl p-6 card-shadow border border-gray-100/50 hover:-translate-y-1 transition-transform duration-300">
            <h3 class="text-gray-500 text-sm font-medium mb-1">Tổng Doanh Thu</h3>
            <div class="text-3xl font-bold text-gray-800 mb-2">{{ number_format($totalRevenue, 0, ',', '.') }}đ</div>
            <div class="flex items-center text-sm">
                <svg class="w-4 h-4 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                <span class="text-green-500 font-medium">+5.2%</span>
                <span class="text-gray-400 ml-2">vs last week</span>
            </div>
        </div>

        <!-- Tổng đơn hàng -->
        <div class="bg-white rounded-2xl p-6 card-shadow border border-gray-100/50 hover:-translate-y-1 transition-transform duration-300">
            <h3 class="text-gray-500 text-sm font-medium mb-1">Tổng Đơn Hàng</h3>
            <div class="text-3xl font-bold text-gray-800 mb-2">{{ number_format($totalOrders) }}</div>
            <div class="flex items-center text-sm">
                <svg class="w-4 h-4 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                <span class="text-green-500 font-medium">+1.8%</span>
                <span class="text-gray-400 ml-2">vs last week</span>
            </div>
        </div>

        <!-- Chờ xác nhận -->
        <div class="bg-white rounded-2xl p-6 card-shadow border border-gray-100/50 hover:-translate-y-1 transition-transform duration-300">
            <h3 class="text-gray-500 text-sm font-medium mb-1">Chờ Xác Nhận</h3>
            <div class="text-3xl font-bold text-gray-800 mb-2">{{ number_format($pendingOrders) }}</div>
            <div class="flex items-center text-sm">
                <svg class="w-4 h-4 text-red-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                <span class="text-red-500 font-medium">-0.4%</span>
                <span class="text-gray-400 ml-2">vs last week</span>
            </div>
        </div>

        <!-- Tổng khách hàng -->
        <div class="bg-white rounded-2xl p-6 card-shadow border border-gray-100/50 hover:-translate-y-1 transition-transform duration-300">
            <h3 class="text-gray-500 text-sm font-medium mb-1">Tổng Khách Hàng</h3>
            <div class="text-3xl font-bold text-gray-800 mb-2">{{ number_format($totalCustomers) }}</div>
            <div class="flex items-center text-sm">
                <svg class="w-4 h-4 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                <span class="text-green-500 font-medium">+12.5%</span>
                <span class="text-gray-400 ml-2">vs last month</span>
            </div>
        </div>

    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-slide-up delay-300">
        
        <!-- Main Chart -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 card-shadow border border-gray-100/50">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Performance Line Chart</h2>
                    <p class="text-sm text-gray-500 mt-1">Biểu đồ doanh thu trong 30 ngày qua</p>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-blue-600"></span>
                        <span class="text-gray-500">Doanh thu</span>
                    </div>
                </div>
            </div>
            <div class="relative h-72 w-full">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Status Summary -->
        <div class="bg-blue-600 rounded-3xl p-6 shadow-lg relative overflow-hidden text-white flex flex-col justify-between group cursor-default">
            <!-- Decorative circle -->
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-2xl group-hover:scale-110 transition-transform duration-700"></div>
            
            <div class="relative z-10">
                <h2 class="text-xl font-bold mb-2">Status Summary</h2>
                <p class="text-blue-100 text-sm mb-6">Tổng quan trạng thái xử lý đơn hàng</p>
                
                <div class="space-y-4">
                    <div>
                        <div class="text-blue-200 text-sm mb-1">Chờ xác nhận</div>
                        <div class="text-4xl font-light">{{ number_format($pendingOrders) }}</div>
                    </div>
                    <div class="h-px w-full bg-white/20 my-2"></div>
                    <div>
                        <div class="text-blue-200 text-sm mb-1">Đã hoàn thành</div>
                        <div class="text-2xl font-light">{{ App\Models\Order::where('order_status', 'completed')->count() }}</div>
                    </div>
                </div>
            </div>

            <!-- Mini decorative wave SVG -->
            <div class="absolute bottom-0 left-0 right-0 h-24 opacity-50 pointer-events-none">
                <svg viewBox="0 0 1440 320" class="w-full h-full" preserveAspectRatio="none">
                    <path fill="none" stroke="rgba(255,255,255,0.4)" stroke-width="3" d="M0,160L48,170.7C96,181,192,203,288,208C384,213,480,203,576,170.7C672,139,768,85,864,85.3C960,85,1056,139,1152,149.3C1248,160,1344,128,1392,112L1440,96"></path>
                </svg>
            </div>
        </div>

    </div>

    <!-- Recent Orders Table -->
    <div class="bg-white rounded-3xl p-6 card-shadow border border-gray-100/50 animate-slide-up delay-400">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-900">Đơn Hàng Mới Nhất</h2>
            <a href="{{ route('admin.orders.index') }}" class="text-sm text-blue-600 font-medium hover:text-blue-800 transition-colors bg-blue-50 px-4 py-2 rounded-lg">Xem tất cả</a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-gray-400 text-sm uppercase border-b border-gray-100">
                        <th class="pb-3 font-medium px-4">Mã Đơn</th>
                        <th class="pb-3 font-medium px-4">Khách Hàng</th>
                        <th class="pb-3 font-medium px-4">Tổng Tiền</th>
                        <th class="pb-3 font-medium px-4">Trạng Thái</th>
                        <th class="pb-3 font-medium px-4 text-right">Thời Gian</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($recentOrders as $order)
                        <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors group">
                            <td class="py-4 px-4 font-semibold text-gray-800">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="hover:text-blue-600 transition-colors">#{{ $order->order_code }}</a>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                                        {{ substr($order->customer_name, 0, 1) }}
                                    </div>
                                    <span class="font-medium text-gray-700">{{ $order->customer_name }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4 font-bold text-gray-900">{{ number_format($order->total_amount, 0, ',', '.') }}đ</td>
                            <td class="py-4 px-4">
                                @if($order->order_status == 'pending')
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Chờ xác nhận</span>
                                @elseif($order->order_status == 'processing')
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Đang xử lý</span>
                                @elseif($order->order_status == 'shipped')
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Đang giao</span>
                                @elseif($order->order_status == 'completed')
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Hoàn thành</span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Đã hủy</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right text-gray-500">{{ $order->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-500">Không có đơn hàng nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        
        // Gradient cho background
        let gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(37, 99, 235, 0.2)'); // blue-600 with opacity
        gradient.addColorStop(1, 'rgba(37, 99, 235, 0)');

        const dataLabels = {!! json_encode($labels) !!};
        const dataValues = {!! json_encode($data) !!};

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: dataLabels,
                datasets: [{
                    label: 'Doanh thu',
                    data: dataValues,
                    backgroundColor: gradient,
                    borderColor: '#2563eb', // blue-600
                    borderWidth: 3,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#2563eb',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4 // Làm cong đường đồ thị (bezier curve)
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
                        backgroundColor: '#ffffff',
                        titleColor: '#1f2937',
                        bodyColor: '#4b5563',
                        borderColor: '#e5e7eb',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                let value = context.parsed.y;
                                return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
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
                            color: '#9ca3af',
                            font: {
                                family: "'Inter', sans-serif",
                                size: 11
                            }
                        }
                    },
                    y: {
                        grid: {
                            color: '#f3f4f6',
                            drawBorder: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            color: '#9ca3af',
                            font: {
                                family: "'Inter', sans-serif",
                                size: 11
                            },
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return (value / 1000000) + 'Tr';
                                } else if (value >= 1000) {
                                    return (value / 1000) + 'k';
                                }
                                return value;
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
            }
        });
    });
</script>
@endpush
