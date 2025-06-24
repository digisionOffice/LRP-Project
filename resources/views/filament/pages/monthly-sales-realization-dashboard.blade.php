<x-filament-panels::page>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <div class="space-y-6">
        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Filter Laporan</h3>
            {{ $this->form }}
        </div>

        <!-- KPI Cards -->
        @php
            $kpiData = $this->getKpiData();
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-shopping-cart class="h-6 w-6 text-blue-400" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Total Pesanan Penjualan
                                </dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ number_format($kpiData['total_so']) }}
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
                            <x-heroicon-o-check-circle class="h-6 w-6 text-green-400" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Pengiriman Selesai
                                </dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ number_format($kpiData['completed_deliveries']) }}
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
                            <x-heroicon-o-clock class="h-6 w-6 text-yellow-400" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Tingkat Penyelesaian
                                </dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ $kpiData['completion_rate'] }}%
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
                            <x-heroicon-o-beaker class="h-6 w-6 text-purple-400" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Realisasi Volume
                                </dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ $kpiData['volume_realization_rate'] }}%
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Daily Trend Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Tren Harian Pesanan vs Pengiriman
                </h3>
                <div class="h-64">
                    <canvas id="dailyTrendChart"></canvas>
                </div>
            </div>

            <!-- Customer Performance Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">10 Kinerja Pelanggan Teratas</h3>
                <div class="h-64">
                    <canvas id="customerPerformanceChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Volume Analysis -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Analisis Volume</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                        {{ number_format($kpiData['total_volume'], 0) }} L
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Volume Dipesan</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                        {{ number_format($kpiData['delivered_volume'], 0) }} L
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Volume Terkirim</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                        {{ number_format($kpiData['total_volume'] - $kpiData['delivered_volume'], 0) }} L
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Volume Tertunda</div>
                </div>
            </div>
        </div>

        <!-- Customer Performance Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Detail Kinerja Pelanggan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Pelanggan</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Total Pesanan</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Selesai</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Tingkat Penyelesaian</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($this->getCustomerPerformanceData() as $customer)
                            <tr>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $customer->customer_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ $customer->total_orders }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ $customer->completed_orders }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if ($customer->completion_rate >= 90) bg-green-100 text-green-800
                                    @elseif($customer->completion_rate >= 70) bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800 @endif">
                                        {{ $customer->completion_rate }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Daily Trend Chart
            const dailyTrendData = @json($this->getDailyTrendData());
            const dailyTrendCtx = document.getElementById('dailyTrendChart').getContext('2d');
            new Chart(dailyTrendCtx, {
                type: 'line',
                data: {
                    labels: dailyTrendData.map(d => d.day),
                    datasets: [{
                        label: 'Pesanan',
                        data: dailyTrendData.map(d => d.orders),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.1
                    }, {
                        label: 'Pengiriman',
                        data: dailyTrendData.map(d => d.deliveries),
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

            // Customer Performance Chart
            const customerData = @json($this->getCustomerPerformanceData());
            const customerCtx = document.getElementById('customerPerformanceChart').getContext('2d');
            new Chart(customerCtx, {
                type: 'bar',
                data: {
                    labels: customerData.map(c => c.customer_name.substring(0, 15) + '...'),
                    datasets: [{
                        label: 'Tingkat Penyelesaian (%)',
                        data: customerData.map(c => c.completion_rate),
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderColor: 'rgb(34, 197, 94)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        </script>
    @endpush
</x-filament-panels::page>
