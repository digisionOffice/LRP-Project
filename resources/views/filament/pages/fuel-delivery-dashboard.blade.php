<x-filament-panels::page>
  {{-- <script src="https://cdn.tailwindcss.com"></script> --}}

    <div class="space-y-6">
        <!-- Enhanced KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-shopping-cart class="h-6 w-6 text-gray-400" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Total Pesanan Penjualan
                                </dt>
                                <dd class="text-lg font-medium text-gray-400 dark:text-white">
                                    {{ \App\Models\TransaksiPenjualan::count() }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-truck class="h-6 w-6 text-gray-400" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Pengiriman Aktif
                                </dt>
                                <dd class="text-lg font-medium text-gray-500 dark:text-white">
                                    {{ \App\Models\DeliveryOrder::whereIn('status_muat', ['pending', 'muat'])->count() }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-check-circle class="h-6 w-6 text-gray-400" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Pengiriman Selesai
                                </dt>
                                <dd class="text-lg font-medium text-gray-500 dark:text-white">
                                    {{ \App\Models\DeliveryOrder::where('status_muat', 'selesai')->count() }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-banknotes class="h-6 w-6 text-gray-400" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Pembayaran Tertunda
                                </dt>
                                <dd class="text-lg font-medium text-gray-500 dark:text-white">
                                    {{ \App\Models\DeliveryOrder::whereIn('payment_status', ['pending', 'partial', 'overdue'])->count() }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-user-group class="h-6 w-6 text-gray-400" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Sopir Aktif
                                </dt>
                                <dd class="text-lg font-medium text-gray-500 dark:text-white">
                                    {{ \App\Models\User::whereHas('jabatan', function ($q) {$q->where('nama', 'like', '%driver%');})->where('is_active', true)->count() }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- <!-- Performance Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Daily Performance Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Daily Performance Trend</h3>
                <div class="h-64">
                    <canvas id="dailyPerformanceChart"></canvas>
                </div>
            </div>

            <!-- Status Distribution Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Delivery Status Distribution</h3>
                <div class="h-64">
                    <canvas id="statusDistributionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Revenue and Volume Analysis -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Revenue & Volume Analysis</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                        {{ number_format(\App\Models\TransaksiPenjualan::whereMonth('tanggal', now()->month)->count()) }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Monthly Orders</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                        {{ number_format(\Illuminate\Support\Facades\DB::table('delivery_order')->where('status_muat', 'selesai')->whereMonth('tanggal_delivery', now()->month)->count()) }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Completed Deliveries</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                        {{ number_format(\Illuminate\Support\Facades\DB::table('transaksi_penjualan')->join('penjualan_detail', 'transaksi_penjualan.id', '=', 'penjualan_detail.id_transaksi_penjualan')->whereMonth('transaksi_penjualan.tanggal', now()->month)->sum('penjualan_detail.volume_item'), 0) }}
                        L
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Monthly Volume</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                        {{ number_format(\Illuminate\Support\Facades\DB::table('delivery_order')->whereIn('payment_status', ['pending', 'partial', 'overdue'])->count()) }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Pending Payments</div>
                </div>
            </div>
        </div> --}}

        <div class="space-y-6">
            <!-- Tab Navigation -->
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex space-x-12 justify-center items-center" aria-label="Tabs">
                    <button wire:click="$set('activeTab', 'sales')"
                        class="@if ($activeTab === 'sales') border-primary-500 text-primary-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        <div class="flex items-center space-x-2">
                            <x-heroicon-o-shopping-cart class="w-5 h-5" />
                            <span>Penjualan</span>
                        </div>
                    </button>

                    <button wire:click="$set('activeTab', 'operations')"
                        class="@if ($activeTab === 'operations') border-primary-500 text-primary-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        <div class="flex items-center space-x-2">
                            <x-heroicon-o-truck class="w-5 h-5" />
                            <span>Operasional</span>
                        </div>
                    </button>

                    <button wire:click="$set('activeTab', 'administration')"
                        class="@if ($activeTab === 'administration') border-primary-500 text-primary-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        <div class="flex items-center space-x-2">
                            <x-heroicon-o-clipboard-document-list class="w-5 h-5" />
                            <span>Administrasi</span>
                        </div>
                    </button>

                    <button wire:click="$set('activeTab', 'driver')"
                        class="@if ($activeTab === 'driver') border-primary-500 text-primary-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        <div class="flex items-center space-x-2">
                            <x-heroicon-o-user class="w-5 h-5" />
                            <span>Sopir</span>
                        </div>
                    </button>

                    <button wire:click="$set('activeTab', 'finance')"
                        class="@if ($activeTab === 'finance') border-primary-500 text-primary-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        <div class="flex items-center space-x-2">
                            <x-heroicon-o-banknotes class="w-5 h-5" />
                            <span>Keuangan</span>
                        </div>
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="mt-6">
                @if ($activeTab === 'sales')
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Pesanan Penjualan</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Kelola data pesanan penjualan dengan informasi pelanggan, jenis BBM, dan lokasi
                                pengiriman.
                            </p>
                        </div>
                        <div class="p-6">
                            {{ $this->table }}
                        </div>
                    </div>
                @elseif($activeTab === 'operations')
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Operasional</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Lacak data pengiriman operasional termasuk penugasan truk dan status muat.
                            </p>
                        </div>
                        <div class="p-6">
                            {{ $this->table }}
                        </div>
                    </div>
                @elseif($activeTab === 'administration')
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Administrasi</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Kelola data administratif pesanan pengiriman termasuk segel, penandatangan, dan
                                tunjangan.
                            </p>
                        </div>
                        <div class="p-6">
                            {{ $this->table }}
                        </div>
                    </div>
                @elseif($activeTab === 'driver')
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Aktivitas Sopir</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Lacak aktivitas pengiriman sopir termasuk pembacaan totalizer dan foto pengiriman.
                            </p>
                        </div>
                        <div class="p-6">
                            {{ $this->table }}
                        </div>
                    </div>
                @elseif($activeTab === 'finance')
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Keuangan & Faktur</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Kelola data keuangan dan faktur termasuk status pembayaran dan pelacakan faktur.
                            </p>
                        </div>
                        <div class="p-6">
                            {{ $this->table }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Daily Performance Chart
            const dailyCtx = document.getElementById('dailyPerformanceChart').getContext('2d');
            new Chart(dailyCtx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Sales Orders',
                        data: [12, 19, 3, 5, 2, 3, 9],
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.1
                    }, {
                        label: 'Completed Deliveries',
                        data: [8, 15, 2, 4, 1, 2, 7],
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Status Distribution Chart
            const statusCtx = document.getElementById('statusDistributionChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'In Progress', 'Pending'],
                    datasets: [{
                        data: [{{ \Illuminate\Support\Facades\DB::table('delivery_order')->where('status_muat', 'selesai')->count() }},
                            {{ \Illuminate\Support\Facades\DB::table('delivery_order')->where('status_muat', 'muat')->count() }},
                            {{ \Illuminate\Support\Facades\DB::table('delivery_order')->where('status_muat', 'pending')->count() }}
                        ],
                        backgroundColor: [
                            'rgba(34, 197, 94, 0.8)',
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(251, 191, 36, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        </script>
    @endpush
</x-filament-panels::page>
