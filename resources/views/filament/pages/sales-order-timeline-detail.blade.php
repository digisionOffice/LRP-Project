

<x-filament-panels::page>
    <div class="space-y-8">
        <!-- Sales Order Header -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                Pesanan Penjualan: <span
                                    class="text-blue-600 dark:text-blue-400">{{ $record->kode }}</span>
                            </h1>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 text-lg">
                            Timeline lengkap proses pengiriman dari pesanan hingga selesai
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div
                            class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow border border-gray-200 dark:border-gray-600">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Tanggal Pesanan</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $record->tanggal->format('d M Y') }}
                            </p>
                        </div>
                        <div
                            class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow border border-gray-200 dark:border-gray-600">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Pelanggan</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $record->pelanggan->nama ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sales Order Summary -->
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Ringkasan Pesanan
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div
                        class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-700">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 bg-blue-500 rounded-lg">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <dt class="text-sm font-medium text-blue-700 dark:text-blue-300">Pelanggan</dt>
                        </div>
                        <dd class="text-lg font-semibold text-blue-900 dark:text-blue-100">
                            {{ $record->pelanggan->nama ?? 'N/A' }}</dd>
                    </div>
                    <div
                        class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-200 dark:border-green-700">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 bg-green-500 rounded-lg">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <dt class="text-sm font-medium text-green-700 dark:text-green-300">Jenis BBM</dt>
                        </div>
                        <dd class="text-lg font-semibold text-green-900 dark:text-green-100">
                            @php
                                $fuelTypes = $record->penjualanDetails->pluck('item.name')->unique();
                            @endphp
                            @if ($fuelTypes->count() > 1)
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($fuelTypes as $fuel)
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-200 dark:bg-green-800 text-green-800 dark:text-green-200">
                                            {{ $fuel }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                {{ $fuelTypes->first() ?? 'N/A' }}
                            @endif
                        </dd>
                    </div>
                    <div
                        class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 border border-purple-200 dark:border-purple-700">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 bg-purple-500 rounded-lg">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                </svg>
                            </div>
                            <dt class="text-sm font-medium text-purple-700 dark:text-purple-300">Total Volume</dt>
                        </div>
                        <dd class="text-lg font-semibold text-purple-900 dark:text-purple-100">
                            {{ number_format($record->penjualanDetails->sum('volume_item'), 2) }} <span
                                class="text-sm font-normal">Liter</span>
                        </dd>
                    </div>
                    <div
                        class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4 border border-orange-200 dark:border-orange-700">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 bg-orange-500 rounded-lg">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <dt class="text-sm font-medium text-orange-700 dark:text-orange-300">Lokasi TBBM</dt>
                        </div>
                        <dd class="text-lg font-semibold text-orange-900 dark:text-orange-100">
                            {{ $record->tbbm->nama ?? 'N/A' }}</dd>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-gray-100 dark:bg-gray-700 rounded-lg">
                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Timeline Pengiriman</h3>
                        <p class="text-gray-600 dark:text-gray-300">
                            Tampilan kronologis semua kejadian terkait pesanan penjualan ini
                        </p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                @php
                    $events = $this->getTimelineEvents();
                @endphp

                @if ($events->count() > 0)
                    <div class="flow-root">
                        <ul role="list" class="space-y-6">
                            @foreach ($events as $index => $event)
                                <li class="relative">
                                    <div>
                                        @if (!$loop->last)
                                            <span
                                                class="absolute top-10 left-5 -ml-px h-full w-0.5 bg-gray-300 dark:bg-gray-600"
                                                aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex items-start space-x-4">
                                            <div class="relative flex-shrink-0">
                                                @php
                                                    $colorClasses = [
                                                        'blue' => 'bg-blue-500',
                                                        'indigo' => 'bg-indigo-500',
                                                        'yellow' => 'bg-yellow-500',
                                                        'green' => 'bg-green-500',
                                                        'purple' => 'bg-purple-500',
                                                        'orange' => 'bg-orange-500',
                                                        'teal' => 'bg-teal-500',
                                                        'emerald' => 'bg-emerald-500',
                                                    ];
                                                    $bgColor = $colorClasses[$event['color']] ?? 'bg-gray-500';
                                                @endphp
                                                <div
                                                    class="h-10 w-10 rounded-full {{ $bgColor }} flex items-center justify-center ring-4 ring-white dark:ring-gray-800">
                                                    @if ($event['icon'] === 'heroicon-o-document-plus')
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="h-5 w-5 text-white" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                        </svg>
                                                    @elseif($event['icon'] === 'heroicon-o-truck')
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="h-5 w-5 text-white" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0M15 17a2 2 0 104 0" />
                                                        </svg>
                                                    @elseif($event['icon'] === 'heroicon-o-arrow-down-on-square')
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="h-5 w-5 text-white" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                                        </svg>
                                                    @elseif($event['icon'] === 'heroicon-o-check-circle')
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="h-5 w-5 text-white" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                    @elseif($event['icon'] === 'heroicon-o-banknotes')
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="h-5 w-5 text-white" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                                        </svg>
                                                    @elseif($event['icon'] === 'heroicon-o-arrow-right')
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="h-5 w-5 text-white" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                                        </svg>
                                                    @elseif($event['icon'] === 'heroicon-o-map-pin')
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="h-5 w-5 text-white" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                    @elseif($event['icon'] === 'heroicon-o-check-badge')
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="h-5 w-5 text-white" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                                        </svg>
                                                    @else
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="h-5 w-5 text-white" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div
                                                    class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                                    <div class="flex items-start justify-between mb-3">
                                                        <div>
                                                            <h4
                                                                class="text-lg font-semibold text-gray-900 dark:text-white">
                                                                {{ $event['title'] }}</h4>
                                                            <div class="flex items-center gap-2 mt-1">
                                                                <svg class="w-4 h-4 text-gray-400" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                                <p
                                                                    class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                                                                    {{ $event['timestamp']->format('d M Y H:i') }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        @php
                                                            $statusColors = [
                                                                'blue' =>
                                                                    'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300',
                                                                'indigo' =>
                                                                    'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-300',
                                                                'yellow' =>
                                                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300',
                                                                'green' =>
                                                                    'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300',
                                                                'purple' =>
                                                                    'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300',
                                                                'orange' =>
                                                                    'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300',
                                                                'teal' =>
                                                                    'bg-teal-100 text-teal-800 dark:bg-teal-900/50 dark:text-teal-300',
                                                                'emerald' =>
                                                                    'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300',
                                                            ];
                                                            $statusColor =
                                                                $statusColors[$event['color']] ??
                                                                'bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-300';
                                                        @endphp
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $statusColor }}">
                                                            {{ ucwords(str_replace('_', ' ', $event['type'])) }}
                                                        </span>
                                                    </div>

                                                    <p class="text-gray-700 dark:text-gray-300 mb-3 leading-relaxed">
                                                        {{ $event['description'] }}</p>

                                                    @if (!empty($event['data']))
                                                        <div
                                                            class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-600">
                                                            <h5
                                                                class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                                                <svg class="w-4 h-4 text-gray-500" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                                Detail Kejadian
                                                            </h5>
                                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                                @foreach ($event['data'] as $key => $value)
                                                                    <div
                                                                        class="bg-gray-50 dark:bg-gray-700 rounded p-2">
                                                                        <dt
                                                                            class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                                                            {{ ucwords(str_replace('_', ' ', $key)) }}
                                                                        </dt>
                                                                        <dd
                                                                            class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                                                            {{ $value }}
                                                                        </dd>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="text-center py-12">
                        <div
                            class="bg-gray-100 dark:bg-gray-700 rounded-full w-16 h-16 mx-auto flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Tidak Ada Kejadian
                            Timeline</h3>
                        <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                            Belum ada kejadian yang tercatat untuk pesanan penjualan ini. Kejadian timeline akan muncul
                            di sini seiring berjalannya proses pengiriman.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
