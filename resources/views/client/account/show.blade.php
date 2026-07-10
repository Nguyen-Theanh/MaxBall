@extends('client.layouts.app')

@section('title', 'Tài khoản của tôi - MaxBall')

@section('content')
<!-- Header Background Spacer -->
<div class="bg-[#10271d] h-[90px] md:h-[110px] w-full absolute top-0 left-0 z-0"></div>

<div class="relative z-10 max-w-7xl mx-auto px-4 pt-32 pb-12 md:pb-16">
    <div class="flex flex-col md:flex-row gap-8">
        
        <!-- Sidebar Navigation -->
        <div class="w-full md:w-64 shrink-0">
            <!-- User Info -->
            <div class="flex items-center gap-4 mb-8 py-4 border-b border-gray-100">
                <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 overflow-hidden border border-gray-300">
                    <i class="fa-solid fa-user text-xl"></i>
                </div>
                <div>
                    <div class="font-bold text-[#10271d]">{{ $user->name }}</div>
                    <button type="button" onclick="switchTab('profile')" class="text-sm text-gray-500 hover:text-[#d92525] flex items-center gap-1 mt-1">
                        <i class="fa-solid fa-pen"></i> Sửa Hồ Sơ
                    </button>
                </div>
            </div>
            
            <!-- Menu Items -->
            <div class="space-y-2">
                <div>
                    <button onclick="switchTab('profile')" id="nav-profile-group" class="w-full text-left flex items-center gap-3 px-2 py-2 rounded transition-colors group">
                        <i class="fa-regular fa-user w-5 text-center text-blue-600"></i>
                        <span class="font-bold text-[#10271d] group-hover:text-[#d92525]">Tài Khoản Của Tôi</span>
                    </button>
                    <div class="ml-10 flex flex-col gap-3 mt-2 mb-4 text-sm">
                        <button onclick="switchTab('profile')" id="nav-profile" class="text-left text-gray-600 hover:text-[#d92525] transition-colors">Hồ sơ</button>
                        <button onclick="switchTab('password')" id="nav-password" class="text-left text-gray-600 hover:text-[#d92525] transition-colors">Đổi mật khẩu</button>
                    </div>
                </div>
                
                <button onclick="switchTab('address')" id="nav-address" class="w-full text-left flex items-center gap-3 px-2 py-2 rounded transition-colors group">
                    <i class="fa-solid fa-location-dot w-5 text-center text-orange-500"></i>
                    <span class="font-bold text-gray-700 group-hover:text-[#d92525]">Địa Chỉ</span>
                </button>
                
                <button onclick="switchTab('orders')" id="nav-orders" class="w-full text-left flex items-center gap-3 px-2 py-2 rounded transition-colors group">
                    <i class="fa-solid fa-clipboard-list w-5 text-center text-blue-500"></i>
                    <span class="font-bold text-gray-700 group-hover:text-[#d92525]">Đơn Mua</span>
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 bg-white rounded-sm shadow-sm border border-gray-100 min-h-[500px]">
            
            <!-- TAB: PROFILE -->
            <div id="tab-profile" class="tab-content p-6 md:p-8 hidden">
                <div class="border-b pb-4 mb-6">
                    <h2 class="text-xl font-medium text-gray-900">Hồ Sơ Của Tôi</h2>
                    <p class="text-sm text-gray-500 mt-1">Quản lý thông tin hồ sơ để bảo mật tài khoản</p>
                </div>

                <div class="max-w-2xl">
                    <form method="POST" action="{{ route('account.update') }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="flex items-center">
                            <label class="w-1/4 text-sm text-gray-500 text-right pr-6">Tên đăng nhập</label>
                            <div class="w-3/4">
                                <span class="text-gray-900 font-medium">{{ $user->email }}</span>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <label for="name" class="w-1/4 text-sm text-gray-500 text-right pr-6">Họ tên</label>
                            <div class="w-3/4">
                                <input id="name" name="name" value="{{ old('name', $user->name) }}" class="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-[#d92525]" required>
                                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex items-center">
                            <label for="email" class="w-1/4 text-sm text-gray-500 text-right pr-6">Email</label>
                            <div class="w-3/4 flex items-center gap-4">
                                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-[#d92525]" required>
                                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex items-center">
                            <label for="phone" class="w-1/4 text-sm text-gray-500 text-right pr-6">Số điện thoại</label>
                            <div class="w-3/4">
                                <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-[#d92525]">
                                @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex items-start">
                            <label for="address" class="w-1/4 text-sm text-gray-500 text-right pr-6 mt-2">Địa chỉ</label>
                            <div class="w-3/4">
                                <textarea id="address" name="address" rows="3" class="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-[#d92525]">{{ old('address', $user->address) }}</textarea>
                                @error('address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex items-center">
                            <div class="w-1/4"></div>
                            <div class="w-3/4">
                                <button type="submit" class="bg-[#d92525] text-white px-6 py-2 rounded text-sm font-medium hover:bg-red-700 transition">
                                    Lưu
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TAB: PASSWORD -->
            <div id="tab-password" class="tab-content p-6 md:p-8 hidden">
                <div class="border-b pb-4 mb-6">
                    <h2 class="text-xl font-medium text-gray-900">Đổi Mật Khẩu</h2>
                    <p class="text-sm text-gray-500 mt-1">Để bảo mật tài khoản, vui lòng không chia sẻ mật khẩu cho người khác</p>
                </div>

                <div class="max-w-2xl">
                    <form method="POST" action="{{ route('account.password.update') }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="flex items-center">
                            <label for="current_password" class="w-1/3 text-sm text-gray-500 text-right pr-6">Mật khẩu hiện tại</label>
                            <div class="w-2/3">
                                <input type="password" id="current_password" name="current_password" class="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-[#d92525]" required>
                                @error('current_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex items-center">
                            <label for="password" class="w-1/3 text-sm text-gray-500 text-right pr-6">Mật khẩu mới</label>
                            <div class="w-2/3">
                                <input type="password" id="password" name="password" class="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-[#d92525]" required>
                                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex items-center">
                            <label for="password_confirmation" class="w-1/3 text-sm text-gray-500 text-right pr-6">Xác nhận mật khẩu</label>
                            <div class="w-2/3">
                                <input type="password" id="password_confirmation" name="password_confirmation" class="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-[#d92525]" required>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <div class="w-1/3"></div>
                            <div class="w-2/3">
                                <button type="submit" class="bg-[#d92525] text-white px-6 py-2 rounded text-sm font-medium hover:bg-red-700 transition">
                                    Xác nhận
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TAB: ADDRESS -->
            <div id="tab-address" class="tab-content p-6 md:p-8 hidden">
                <div class="border-b pb-4 mb-6 flex justify-between items-center">
                    <h2 class="text-xl font-medium text-gray-900">Địa Chỉ Của Tôi</h2>
                    <button type="button" onclick="openAddressModal()" class="bg-[#d92525] text-white px-4 py-2 rounded text-sm font-medium hover:bg-red-700 transition flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i> Thêm địa chỉ mới
                    </button>
                </div>

                <div class="space-y-6">
                    @forelse ($addresses as $address)
                        <div class="flex items-start justify-between border-b border-gray-100 pb-6 last:border-0 last:pb-0">
                            <div class="space-y-1">
                                <div class="flex items-center gap-3">
                                    <span class="font-bold text-gray-900 text-base">{{ $address->receiver_name }}</span>
                                    <span class="text-gray-300">|</span>
                                    <span class="text-gray-500">{{ $address->receiver_phone }}</span>
                                </div>
                                <p class="text-sm text-gray-600 mt-2">{{ $address->address_detail }}</p>
                                @if($address->is_default)
                                    <div class="mt-2"><span class="inline-block border border-[#d92525] text-[#d92525] text-xs px-2 py-0.5 rounded">Mặc định</span></div>
                                @endif
                            </div>
                            <div class="flex flex-col items-end gap-2 text-sm">
                                <div class="flex items-center gap-3">
                                    <button type="button" onclick='openAddressModal(@json($address))' class="text-blue-600 hover:text-blue-800">Cập nhật</button>
                                    @if(!$address->is_default)
                                        <form action="{{ route('account.addresses.destroy', $address->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Bạn có chắc muốn xóa địa chỉ này?')" class="text-gray-500 hover:text-red-600">Xóa</button>
                                        </form>
                                    @endif
                                </div>
                                @if(!$address->is_default)
                                    <form action="{{ route('account.addresses.setDefault', $address->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="border border-gray-300 rounded px-3 py-1 text-xs text-gray-600 hover:bg-gray-50 transition mt-2">Thiết lập mặc định</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 text-gray-500 flex flex-col items-center justify-center">
                            <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                <i class="fa-solid fa-location-dot text-4xl text-gray-300"></i>
                            </div>
                            <p>Bạn chưa có địa chỉ giao hàng nào.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- TAB: ORDERS -->
            <div id="tab-orders" class="tab-content p-6 md:p-8 hidden bg-gray-50">
                <div class="flex items-center gap-8 border-b bg-white px-6 py-4 mb-4 rounded shadow-sm">
                    <button class="text-[#d92525] border-b-2 border-[#d92525] pb-4 -mb-4 font-medium">Tất cả</button>
                    <button class="text-gray-600 hover:text-[#d92525] pb-4 -mb-4 font-medium">Chờ thanh toán</button>
                    <button class="text-gray-600 hover:text-[#d92525] pb-4 -mb-4 font-medium">Vận chuyển</button>
                    <button class="text-gray-600 hover:text-[#d92525] pb-4 -mb-4 font-medium">Hoàn thành</button>
                    <button class="text-gray-600 hover:text-[#d92525] pb-4 -mb-4 font-medium">Đã hủy</button>
                </div>

                <div class="space-y-4">
                    @forelse ($orders as $order)
                        <div class="bg-white rounded shadow-sm border border-gray-100 p-6">
                            <div class="flex justify-between items-center border-b border-gray-100 pb-3 mb-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-[#10271d]">#{{ $order->order_code }}</span>
                                    <span class="text-xs text-gray-500">| {{ $order->created_at?->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-medium text-gray-500 uppercase">{{ $order->payment_method }}</span>
                                    <span class="text-sm font-bold text-[#d92525] uppercase">
                                        @if($order->order_status == 'pending')
                                            CHỜ XÁC NHẬN
                                        @elseif($order->order_status == 'processing')
                                            ĐANG XỬ LÝ
                                        @elseif($order->order_status == 'shipping')
                                            ĐANG GIAO HÀNG
                                        @elseif($order->order_status == 'completed')
                                            HOÀN THÀNH
                                        @elseif($order->order_status == 'cancelled')
                                            ĐÃ HỦY
                                        @else
                                            {{ $order->order_status }}
                                        @endif
                                    </span>
                                </div>
                            </div>

                            @if ($order->details->isNotEmpty())
                                <div class="divide-y divide-gray-50 mb-4">
                                    @foreach ($order->details as $detail)
                                        <div class="flex items-start py-3 gap-4">
                                            <div class="w-20 h-20 bg-gray-100 rounded border border-gray-200 overflow-hidden shrink-0">
                                                @php
                                                    $thumbnail = $detail->variant?->product?->thumbnail_url ?? null;
                                                    if (!$thumbnail && !empty($detail->variant?->product?->thumbnail)) {
                                                        $thumbnail = str_starts_with($detail->variant->product->thumbnail, 'http') ? $detail->variant->product->thumbnail : asset('storage/' . $detail->variant->product->thumbnail);
                                                    }
                                                    if (!$thumbnail) {
                                                        $thumbnail = 'https://via.placeholder.com/150';
                                                    }
                                                @endphp
                                                <img src="{{ $thumbnail }}" class="w-full h-full object-cover">
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="font-medium text-gray-900">{{ $detail->variant?->product?->name ?? 'Sản phẩm' }}</h4>
                                                <p class="text-sm text-gray-500 mt-1">Phân loại hàng: {{ $detail->variant?->name ?? '' }}</p>
                                                <p class="text-sm text-gray-900 mt-1">x{{ $detail->quantity }}</p>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-[#d92525] font-medium">{{ number_format($detail->price, 0, ',', '.') }}đ</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="bg-red-50/50 -mx-6 -mb-6 px-6 py-4 flex justify-between items-center rounded-b border-t border-gray-100">
                                <div class="text-xs text-gray-500">
                                    SĐT Người nhận: {{ $order->customer_phone }}
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-gray-600">Thành tiền:</span>
                                    <span class="text-xl font-black text-[#d92525]">{{ number_format($order->total_amount, 0, ',', '.') }}đ</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded border border-gray-100 py-16 text-center flex flex-col items-center justify-center shadow-sm">
                            <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                <i class="fa-solid fa-clipboard-list text-4xl text-gray-300"></i>
                            </div>
                            <p class="text-gray-500 font-medium">Chưa có đơn hàng</p>
                        </div>
                    @endforelse
                    
                    <div class="mt-5">
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal thêm/sửa địa chỉ -->
<div id="addressModal" class="fixed inset-0 z-[100] hidden items-center justify-center">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeAddressModal()"></div>
    
    <!-- Modal Content -->
    <div class="relative z-10 w-full max-w-lg rounded-sm bg-white p-6 shadow-2xl transition-all">
        <div class="mb-5 flex items-center justify-between border-b pb-4">
            <h3 class="text-xl font-medium text-gray-900" id="addressModalTitle">Địa chỉ mới</h3>
            <button type="button" onclick="closeAddressModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <form id="addressForm" method="POST" action="{{ route('account.addresses.store') }}">
            @csrf
            <input type="hidden" name="_method" id="addressMethod" value="POST">
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <input type="text" id="receiver_name" name="receiver_name" placeholder="Họ và tên" class="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-[#d92525]" required>
                    </div>
                    <div>
                        <input type="text" id="receiver_phone" name="receiver_phone" placeholder="Số điện thoại" class="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-[#d92525]" required>
                    </div>
                </div>
                
                <div>
                    <textarea id="address_detail" name="address_detail" rows="3" placeholder="Địa chỉ cụ thể" class="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-[#d92525]" required></textarea>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" id="is_default" name="is_default" class="h-4 w-4 rounded border-gray-300 text-[#d92525] focus:ring-[#d92525]">
                    <label for="is_default" class="text-sm text-gray-700">Đặt làm địa chỉ mặc định</label>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <button type="button" onclick="closeAddressModal()" class="px-5 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50 rounded">
                    Trở lại
                </button>
                <button type="submit" class="bg-[#d92525] text-white px-5 py-2 text-sm font-medium rounded hover:bg-red-700 shadow-sm">
                    Hoàn thành
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Tab switching logic
    function switchTab(tabId) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(el => {
            el.classList.add('hidden');
        });
        
        // Show target tab
        document.getElementById('tab-' + tabId).classList.remove('hidden');

        // Reset nav styles
        document.getElementById('nav-profile-group').classList.remove('text-[#d92525]');
        document.getElementById('nav-profile-group').querySelector('span').classList.remove('text-[#d92525]');
        document.getElementById('nav-profile-group').querySelector('span').classList.add('text-[#10271d]');
        
        document.getElementById('nav-profile').classList.remove('text-[#d92525]');
        document.getElementById('nav-profile').classList.add('text-gray-600');
        
        document.getElementById('nav-password').classList.remove('text-[#d92525]');
        document.getElementById('nav-password').classList.add('text-gray-600');
        
        document.getElementById('nav-address').querySelector('span').classList.remove('text-[#d92525]');
        document.getElementById('nav-address').querySelector('span').classList.add('text-gray-700');
        
        document.getElementById('nav-orders').querySelector('span').classList.remove('text-[#d92525]');
        document.getElementById('nav-orders').querySelector('span').classList.add('text-gray-700');

        // Apply active styles
        if (tabId === 'profile' || tabId === 'password') {
            document.getElementById('nav-profile-group').querySelector('span').classList.remove('text-[#10271d]');
            document.getElementById('nav-profile-group').querySelector('span').classList.add('text-[#d92525]');
            
            if (tabId === 'profile') {
                document.getElementById('nav-profile').classList.remove('text-gray-600');
                document.getElementById('nav-profile').classList.add('text-[#d92525]');
            } else {
                document.getElementById('nav-password').classList.remove('text-gray-600');
                document.getElementById('nav-password').classList.add('text-[#d92525]');
            }
        } else if (tabId === 'address') {
            document.getElementById('nav-address').querySelector('span').classList.remove('text-gray-700');
            document.getElementById('nav-address').querySelector('span').classList.add('text-[#d92525]');
        } else if (tabId === 'orders') {
            document.getElementById('nav-orders').querySelector('span').classList.remove('text-gray-700');
            document.getElementById('nav-orders').querySelector('span').classList.add('text-[#d92525]');
        }
    }

    // Initialize default tab (or based on URL hash)
    document.addEventListener('DOMContentLoaded', () => {
        let hash = window.location.hash.replace('#', '');
        if (['profile', 'password', 'address', 'orders'].includes(hash)) {
            switchTab(hash);
        } else {
            switchTab('profile'); // default
        }
    });

    // Update URL hash when clicking
    document.querySelectorAll('[id^="nav-"]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            let tab = this.id.replace('nav-', '');
            if (tab === 'profile-group') tab = 'profile';
            window.history.pushState(null, null, '#' + tab);
        });
    });

    function openAddressModal(address = null) {
        const modal = document.getElementById('addressModal');
        const form = document.getElementById('addressForm');
        const title = document.getElementById('addressModalTitle');
        const methodInput = document.getElementById('addressMethod');
        
        if (address) {
            title.textContent = 'Cập nhật địa chỉ';
            form.action = `/account/addresses/${address.id}`;
            methodInput.value = 'PUT';
            document.getElementById('receiver_name').value = address.receiver_name;
            document.getElementById('receiver_phone').value = address.receiver_phone;
            document.getElementById('address_detail').value = address.address_detail;
            document.getElementById('is_default').checked = address.is_default == 1;
        } else {
            title.textContent = 'Địa chỉ mới';
            form.action = '{{ route('account.addresses.store') }}';
            methodInput.value = 'POST';
            form.reset();
        }
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeAddressModal() {
        const modal = document.getElementById('addressModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endpush
