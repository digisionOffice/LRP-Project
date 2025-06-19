<x-filament-panels::page>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    @if ($this->record)
        <div class="space-y-6">
            <!-- Header Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div
                                class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-truck class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                            </div>
                        </div>
                        <div>
                            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                                {{ $this->record->kode }}
                            </h1>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $this->record->transaksi->pelanggan->nama }}
                            </p>
                        </div>
                    </div>

                    <!-- Status Badge -->
                    <div class="flex flex-wrap gap-2">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if ($this->record->status_muat === 'pending') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                        @elseif($this->record->status_muat === 'muat') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                        @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @endif">
                            @if ($this->record->status_muat === 'pending')
                                Menunggu
                            @elseif($this->record->status_muat === 'muat')
                                Sedang Muat
                            @else
                                Selesai
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <!-- Update Status -->
                @if ($this->record->status_muat !== 'selesai')
                    <button type="button" onclick="toggleSection('status-section')"
                        class="flex flex-col items-center p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg hover:bg-yellow-100 dark:hover:bg-yellow-900/30 transition-colors">
                        <x-heroicon-o-pencil-square class="w-6 h-6 text-yellow-600 dark:text-yellow-400 mb-2" />
                        <span class="text-sm font-medium text-yellow-900 dark:text-yellow-100">Update Status</span>
                    </button>
                @endif

                <!-- Totalisator -->
                <button type="button" onclick="toggleSection('totalisator-section')"
                    class="flex flex-col items-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                    <x-heroicon-o-calculator class="w-6 h-6 text-blue-600 dark:text-blue-400 mb-2" />
                    <span class="text-sm font-medium text-blue-900 dark:text-blue-100">Totalisator</span>
                </button>

                <!-- Approve Allowance -->
                @if ($this->record->uangJalan && $this->record->uangJalan->canBeApproved())
                    <button type="button" onclick="$wire.approveAllowance()"
                        class="flex flex-col items-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                        <x-heroicon-o-banknotes class="w-6 h-6 text-green-600 dark:text-green-400 mb-2" />
                        <span class="text-sm font-medium text-green-900 dark:text-green-100">ACC Uang Jalan</span>
                    </button>
                @endif

                <!-- Approve Delivery -->
                @if ($this->record->pengirimanDriver && $this->record->pengirimanDriver->canBeApproved())
                    <button type="button" onclick="$wire.approveDelivery()"
                        class="flex flex-col items-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors">
                        <x-heroicon-o-truck class="w-6 h-6 text-purple-600 dark:text-purple-400 mb-2" />
                        <span class="text-sm font-medium text-purple-900 dark:text-purple-100">ACC Pengiriman</span>
                    </button>
                @endif
            </div>

            <!-- Collapsible Sections -->

            <!-- Status Update Section -->
            @if ($this->record->status_muat !== 'selesai')
                <div id="status-section" class="hidden bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            Update Status Pengiriman
                        </h3>
                        <form wire:submit="updateStatus">
                            {{ $this->statusForm }}
                            <div class="mt-4 flex justify-end space-x-3">
                                <button type="button" onclick="toggleSection('status-section')"
                                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    Batal
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Totalisator Section -->
            <div id="totalisator-section" class="hidden bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        Data Totalisator & Waktu
                    </h3>
                    <form wire:submit="updateTotalisator">
                        {{ $this->totalisatorForm }}
                        <div class="mt-4 flex justify-end space-x-3">
                            <button type="button" onclick="toggleSection('totalisator-section')"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delivery Information -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        Informasi Pengiriman
                    </h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Kode DO:</dt>
                            <dd class="text-sm text-gray-900 dark:text-white font-mono">{{ $this->record->kode }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal:</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">
                                {{ $this->record->tanggal_delivery ? \Carbon\Carbon::parse($this->record->tanggal_delivery)->format('d/m/Y') : 'N/A' }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Volume:</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">
                                {{ number_format($this->record->volume_do, 0, ',', '.') }} L</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Kendaraan:</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">
                                {{ $this->record->kendaraan->nomor_polisi ?? 'N/A' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Driver:</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">{{ $this->record->user->name ?? 'N/A' }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Customer Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        Informasi Pelanggan
                    </h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama:</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">
                                {{ $this->record->transaksi->pelanggan->nama }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Alamat:</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">
                                {{ $this->record->transaksi->pelanggan->alamatPelanggan->first()->alamat ?? 'Alamat tidak tersedia' }}
                            </dd>
                        </div>
                        @if ($this->record->transaksi->pelanggan->alamatPelanggan->first()?->kota)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Kota:</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">
                                    {{ $this->record->transaksi->pelanggan->alamatPelanggan->first()->kota }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Current Totalisator Data -->
            @if ($this->record->pengirimanDriver)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        Data Totalisator Saat Ini
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <div class="flex items-center">
                                <x-heroicon-o-play class="w-5 h-5 text-blue-500 mr-2" />
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Totalisator Awal</p>
                                    <p class="text-lg font-semibold text-blue-600 dark:text-blue-400">
                                        {{ $this->record->pengirimanDriver->totalisator_awal ? number_format($this->record->pengirimanDriver->totalisator_awal, 0, ',', '.') . ' km' : 'Belum diisi' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <div class="flex items-center">
                                <x-heroicon-o-map-pin class="w-5 h-5 text-green-500 mr-2" />
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Totalisator Tiba</p>
                                    <p class="text-lg font-semibold text-green-600 dark:text-green-400">
                                        {{ $this->record->pengirimanDriver->totalisator_tiba ? number_format($this->record->pengirimanDriver->totalisator_tiba, 0, ',', '.') . ' km' : 'Belum diisi' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <div class="flex items-center">
                                <x-heroicon-o-home class="w-5 h-5 text-purple-500 mr-2" />
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Totalisator Kembali</p>
                                    <p class="text-lg font-semibold text-purple-600 dark:text-purple-400">
                                        {{ $this->record->pengirimanDriver->totalisator_pool_return ? number_format($this->record->pengirimanDriver->totalisator_pool_return, 0, ',', '.') . ' km' : 'Belum diisi' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($this->record->pengirimanDriver->totalisator_awal && $this->record->pengirimanDriver->totalisator_pool_return)
                        <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-blue-900 dark:text-blue-100">Total Jarak
                                    Tempuh:</span>
                                <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                    {{ number_format($this->record->pengirimanDriver->totalisator_pool_return - $this->record->pengirimanDriver->totalisator_awal, 0, ',', '.') }}
                                    km
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Approval Status -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    Status Persetujuan
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Uang Jalan Status -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <x-heroicon-o-banknotes class="w-5 h-5 text-gray-400" />
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Uang Jalan</p>
                                @if ($this->record->uangJalan)
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Rp {{ number_format($this->record->uangJalan->nominal, 0, ',', '.') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <span
                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                        @if ($this->record->uangJalan?->approval_status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                        @elseif($this->record->uangJalan?->approval_status === 'approved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                        @elseif($this->record->uangJalan?->approval_status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                        @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                            @if ($this->record->uangJalan?->approval_status === 'pending')
                                Menunggu
                            @elseif($this->record->uangJalan?->approval_status === 'approved')
                                Disetujui
                            @elseif($this->record->uangJalan?->approval_status === 'rejected')
                                Ditolak
                            @else
                                N/A
                            @endif
                        </span>
                    </div>

                    <!-- Pengiriman Status -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <x-heroicon-o-truck class="w-5 h-5 text-gray-400" />
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Pengiriman</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Status persetujuan</p>
                            </div>
                        </div>
                        <span
                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                        @if ($this->record->pengirimanDriver?->approval_status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                        @elseif($this->record->pengirimanDriver?->approval_status === 'approved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                        @elseif($this->record->pengirimanDriver?->approval_status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                        @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                            @if ($this->record->pengirimanDriver?->approval_status === 'pending')
                                Menunggu
                            @elseif($this->record->pengirimanDriver?->approval_status === 'approved')
                                Disetujui
                            @elseif($this->record->pengirimanDriver?->approval_status === 'rejected')
                                Ditolak
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">
                    Timeline Pengiriman
                </h3>
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        @foreach ($this->getDeliveryTimeline() as $index => $event)
                            <li>
                                <div class="relative pb-8">
                                    @if (!$loop->last)
                                        <span
                                            class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-600"
                                            aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span
                                                class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-gray-800
                                            @if ($event['status'] === 'completed') bg-green-500
                                            @else bg-gray-400 @endif">
                                                @php
                                                    $iconClass = 'h-4 w-4 text-white';
                                                @endphp
                                                @if ($event['icon'] === 'heroicon-o-document-plus')
                                                    <x-heroicon-o-document-plus class="{{ $iconClass }}" />
                                                @elseif($event['icon'] === 'heroicon-o-banknotes')
                                                    <x-heroicon-o-banknotes class="{{ $iconClass }}" />
                                                @elseif($event['icon'] === 'heroicon-o-play')
                                                    <x-heroicon-o-play class="{{ $iconClass }}" />
                                                @elseif($event['icon'] === 'heroicon-o-map-pin')
                                                    <x-heroicon-o-map-pin class="{{ $iconClass }}" />
                                                @elseif($event['icon'] === 'heroicon-o-home')
                                                    <x-heroicon-o-home class="{{ $iconClass }}" />
                                                @else
                                                    <x-heroicon-o-clock class="{{ $iconClass }}" />
                                                @endif
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $event['title'] }}</p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $event['description'] }}</p>
                                            </div>
                                            <div
                                                class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                @if ($event['time'])
                                                    <time datetime="{{ $event['time'] }}">
                                                        {{ \Carbon\Carbon::parse($event['time'])->format('d/m/Y H:i') }}
                                                    </time>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Items Detail -->
            @if ($this->record->transaksi->penjualanDetails->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        Detail Item
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Item</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Volume</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Harga</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($this->record->transaksi->penjualanDetails as $detail)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $detail->item->nama ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ number_format($detail->volume_item, 0, ',', '.') }} L
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            Rp {{ number_format($detail->harga_jual, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            Rp
                                            {{ number_format($detail->volume_item * $detail->harga_jual, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- JavaScript for Section Toggle -->
        <script>
            function toggleSection(sectionId) {
                const section = document.getElementById(sectionId);
                if (section.classList.contains('hidden')) {
                    // Hide all other sections first
                    document.querySelectorAll('[id$="-section"]').forEach(el => {
                        if (el.id !== sectionId) {
                            el.classList.add('hidden');
                        }
                    });
                    section.classList.remove('hidden');
                } else {
                    section.classList.add('hidden');
                }
            }
        </script>
    @else
        <div class="text-center py-12">
            <x-heroicon-o-exclamation-triangle class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Record tidak ditemukan</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Delivery order yang Anda cari tidak ditemukan atau
                Anda tidak memiliki akses.</p>
            <div class="mt-6">
                <a href="{{ route('filament.admin.pages.driver-dashboard') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <x-heroicon-o-arrow-left class="mr-2 h-4 w-4" />
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    @endif
</x-filament-panels::page>
