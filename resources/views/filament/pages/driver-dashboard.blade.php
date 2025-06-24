<x-filament-panels::page>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- CSS for responsive view mode -->
    <style>
        /* Hide table view on mobile by default */
        @media (max-width: 767px) {
            .table-view-container {
                display: none !important;
            }

            .compact-view-container {
                display: block !important;
            }
        }

        /* Hide compact view on desktop by default */
        @media (min-width: 768px) {
            .table-view-container {
                display: block !important;
            }

            .compact-view-container {
                display: none !important;
            }
        }

        /* Override when JavaScript sets specific view mode */
        .force-table-view .table-view-container {
            display: block !important;
        }

        .force-table-view .compact-view-container {
            display: none !important;
        }

        .force-compact-view .table-view-container {
            display: none !important;
        }

        .force-compact-view .compact-view-container {
            display: block !important;
        }
    </style>

    <!-- Mobile View Mode Detection -->
    <script>
        // Global variables for Livewire integration
        let livewireReady = false;
        let pendingViewModeChanges = [];

        // Check if Livewire is available
        function isLivewireReady() {
            return typeof window.Livewire !== 'undefined' && window.Livewire.find && livewireReady;
        }

        // Execute pending view mode changes when Livewire is ready
        function executePendingChanges() {
            if (isLivewireReady() && pendingViewModeChanges.length > 0) {
                pendingViewModeChanges.forEach(change => {
                    try {
                        if (window.Livewire.find) {
                            const component = window.Livewire.find(document.querySelector('[wire\\:id]')
                                ?.getAttribute('wire:id'));
                            if (component && component.call) {
                                component.call('setViewMode', change.mode);
                            }
                        }
                    } catch (error) {
                        console.warn('Failed to execute pending view mode change:', error);
                    }
                });
                pendingViewModeChanges = [];
            }
        }

        // Safe wrapper for Livewire method calls
        function safeSetViewMode(mode) {
            if (isLivewireReady()) {
                try {
                    const component = window.Livewire.find(document.querySelector('[wire\\:id]')?.getAttribute('wire:id'));
                    if (component && component.call) {
                        component.call('setViewMode', mode);
                        return true;
                    }
                } catch (error) {
                    console.warn('Failed to call setViewMode:', error);
                }
            }

            // Queue the change for later execution
            pendingViewModeChanges.push({
                mode: mode
            });
            return false;
        }

        // Screen size detection and auto view mode setting
        function detectScreenSize() {
            const isMobile = window.innerWidth < 768;
            const currentMode = @js($this->viewMode);

            // Auto-set view mode based on screen size on initial load
            if (currentMode === 'auto' || currentMode === null) {
                const newMode = isMobile ? 'compact' : 'table';
                safeSetViewMode(newMode);
            }

            // Update toggle button visibility and apply CSS classes
            updateToggleButtonVisibility(isMobile);
            applyViewModeClasses(currentMode);
        }

        function applyViewModeClasses(mode) {
            const container = document.querySelector('.space-y-6');
            if (container) {
                // Remove existing classes
                container.classList.remove('force-table-view', 'force-compact-view');

                // Apply appropriate class based on mode
                if (mode === 'table') {
                    container.classList.add('force-table-view');
                } else if (mode === 'compact') {
                    container.classList.add('force-compact-view');
                }
            }
        }

        function updateToggleButtonVisibility(isMobile) {
            const toggleButton = document.getElementById('view-toggle-button');
            if (toggleButton) {
                if (isMobile) {
                    toggleButton.classList.add('hidden');
                    toggleButton.classList.remove('inline-flex');
                } else {
                    toggleButton.classList.remove('hidden');
                    toggleButton.classList.add('inline-flex');
                }
            }
        }

        // Livewire initialization and event listeners
        document.addEventListener('livewire:init', () => {
            livewireReady = true;
            console.log('Livewire initialized');
            executePendingChanges();
        });

        document.addEventListener('livewire:navigated', () => {
            livewireReady = true;
            console.log('Livewire navigated');
            executePendingChanges();
        });

        // Run on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Wait a bit for Livewire to initialize
            setTimeout(() => {
                if (typeof window.Livewire !== 'undefined') {
                    livewireReady = true;
                    executePendingChanges();
                }
                detectScreenSize();
            }, 100);
        });

        // Run on window resize with debouncing
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                const isMobile = window.innerWidth < 768;
                const currentMode = @js($this->viewMode);

                // Auto-switch view mode on resize
                if (isMobile && currentMode === 'table') {
                    safeSetViewMode('compact');
                } else if (!isMobile && currentMode === 'compact') {
                    safeSetViewMode('table');
                }

                updateToggleButtonVisibility(isMobile);
            }, 250); // 250ms debounce
        });

        // Livewire hook to update button after view mode changes
        document.addEventListener('livewire:updated', function() {
            const isMobile = window.innerWidth < 768;
            const currentMode = @js($this->viewMode);
            updateToggleButtonVisibility(isMobile);
            updateMobileToggleButton();
            applyViewModeClasses(currentMode);

            // Execute any pending changes after update
            executePendingChanges();
        });

        // Mobile toggle function
        function toggleMobileView() {
            if (isLivewireReady()) {
                try {
                    const component = window.Livewire.find(document.querySelector('[wire\\:id]')?.getAttribute('wire:id'));
                    if (component && component.call) {
                        component.call('toggleViewMode').then(() => {
                            const newMode = @js($this->viewMode);
                            applyViewModeClasses(newMode);
                        }).catch(error => {
                            console.warn('Failed to toggle view mode:', error);
                        });
                    }
                } catch (error) {
                    console.warn('Failed to call toggleViewMode:', error);
                }
            } else {
                console.warn('Livewire not ready for toggleViewMode');
            }
        }

        // Update mobile toggle button text and icon
        function updateMobileToggleButton() {
            const currentMode = @js($this->viewMode);
            const toggleText = document.getElementById('mobile-toggle-text');
            const toggleIcon = document.getElementById('mobile-toggle-icon');

            if (toggleText && toggleIcon) {
                if (currentMode === 'compact') {
                    toggleText.textContent = 'Tabel';
                    toggleIcon.innerHTML = `
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M3 18h18M3 6h18"></path>
                        </svg>
                    `;
                } else {
                    toggleText.textContent = 'Kompak';
                    toggleIcon.innerHTML = `
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                    `;
                }
            }
        }

        // Initialize mobile toggle button on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateMobileToggleButton();
        });
    </script>

    <div class="space-y-6">
        <!-- Welcome Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <x-heroicon-o-truck class="h-8 w-8 text-amber-500" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Selamat Datang, {{ Auth::user()->name }}
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Dashboard pengiriman untuk driver
                    </p>
                </div>
            </div>
        </div>

        <!-- KPI Cards -->
        @php
            $stats = $this->getDeliveryStats();
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <!-- Total Deliveries -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-clipboard-document-list class="h-6 w-6 text-blue-500" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Total Pengiriman
                                </dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ $stats['total_deliveries'] }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Deliveries -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-clock class="h-6 w-6 text-yellow-500" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Menunggu Muat
                                </dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ $stats['pending_deliveries'] }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- In Progress Deliveries -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-arrow-path class="h-6 w-6 text-orange-500" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Sedang Muat
                                </dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ $stats['in_progress_deliveries'] }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completed Deliveries -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-check-circle class="h-6 w-6 text-green-500" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Selesai
                                </dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ $stats['completed_deliveries'] }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Volume -->
            {{-- <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-beaker class="h-6 w-6 text-purple-500" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Total Volume
                                </dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ number_format($stats['total_volume'], 0, ',', '.') }} L
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div> --}}

            <!-- Pending Allowances -->
            {{-- <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-banknotes class="h-6 w-6 text-red-500" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Uang Jalan Pending
                                </dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ $stats['pending_allowances'] }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div> --}}

            <!-- Pending Allowance Approvals -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-clock class="h-6 w-6 text-amber-500" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Approval Uang Jalan Pending
                                </dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ $stats['pending_allowance_approvals'] }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approved Allowances -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-check-badge class="h-6 w-6 text-emerald-500" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Uang Jalan Disetujui
                                </dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ $stats['approved_allowances'] }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Delivery Approvals -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-yellow-500" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Approval Pengiriman Pending
                                </dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ $stats['pending_delivery_approvals'] }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approved Deliveries -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-shield-check class="h-6 w-6 text-green-500" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Pengiriman Disetujui
                                </dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ $stats['approved_deliveries'] }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delivery Orders Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            Daftar Pengiriman Saya
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Kelola dan pantau status pengiriman yang ditugaskan kepada Anda
                        </p>
                    </div>

                    <!-- Mobile Toggle Button -->
                    <div class="md:hidden">
                        <button type="button" id="mobile-view-toggle" onclick="toggleMobileView()"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <span id="mobile-toggle-icon">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                </svg>
                            </span>
                            <span id="mobile-toggle-text">Tabel</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table View Container -->
            <div class="table-view-container p-6">
                {{ $this->table }}
            </div>

            <!-- Compact View Container -->
            <div class="compact-view-container p-4 space-y-4">
                @forelse($this->getDeliveryOrders() as $delivery)
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 space-y-3">
                        <!-- Header Row -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                        <x-heroicon-o-truck class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ $delivery->kode }}
                                    </h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $delivery->transaksi->pelanggan->nama }}
                                    </p>
                                </div>
                            </div>

                            <!-- Status Badges -->
                            <div class="flex flex-wrap gap-1">
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        @if ($delivery->status_muat === 'pending') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                        @elseif($delivery->status_muat === 'muat') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @endif">
                                    @if ($delivery->status_muat === 'pending')
                                        Menunggu
                                    @elseif($delivery->status_muat === 'muat')
                                        Sedang Muat
                                    @else
                                        Selesai
                                    @endif
                                </span>
                            </div>
                        </div>

                        <!-- Details Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
                            <!-- Vehicle Info -->
                            <div class="flex items-center space-x-2">
                                <x-heroicon-o-truck class="w-4 h-4 text-gray-400" />
                                <span class="text-gray-600 dark:text-gray-300">
                                    {{ $delivery->kendaraan->no_pol_kendaraan ?? 'N/A' }}
                                </span>
                            </div>

                            <!-- Date -->
                            <div class="flex items-center space-x-2">
                                <x-heroicon-o-calendar class="w-4 h-4 text-gray-400" />
                                <span class="text-gray-600 dark:text-gray-300">
                                    {{ $delivery->tanggal_delivery ? \Carbon\Carbon::parse($delivery->tanggal_delivery)->format('d/m/Y') : 'N/A' }}
                                </span>
                            </div>

                            <!-- Volume -->
                            <div class="flex items-center space-x-2">
                                <x-heroicon-o-beaker class="w-4 h-4 text-gray-400" />
                                <span class="text-gray-600 dark:text-gray-300">
                                    {{ number_format($delivery->volume_do, 0, ',', '.') }} L
                                </span>
                            </div>

                            <!-- Address -->
                            <div class="flex items-center space-x-2">
                                <x-heroicon-o-map-pin class="w-4 h-4 text-gray-400" />
                                <span class="text-gray-600 dark:text-gray-300 truncate">
                                    {{ $delivery->transaksi->pelanggan->alamatPelanggan->first()->alamat ?? 'Alamat tidak tersedia' }}
                                </span>
                            </div>
                        </div>

                        <!-- Approval Status Row -->
                        <div
                            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pt-2 border-t border-gray-200 dark:border-gray-600">
                            <div class="flex flex-wrap gap-2">
                                <!-- Uang Jalan Status -->
                                <div class="flex items-center space-x-2">
                                    <x-heroicon-o-banknotes class="w-4 h-4 text-gray-400" />
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Uang Jalan:</span>
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                            @if ($delivery->uangJalan?->approval_status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @elseif($delivery->uangJalan?->approval_status === 'approved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @elseif($delivery->uangJalan?->approval_status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                        @if ($delivery->uangJalan?->approval_status === 'pending')
                                            Menunggu
                                        @elseif($delivery->uangJalan?->approval_status === 'approved')
                                            Disetujui
                                        @elseif($delivery->uangJalan?->approval_status === 'rejected')
                                            Ditolak
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </div>

                                <!-- Pengiriman Status -->
                                <div class="flex items-center space-x-2">
                                    <x-heroicon-o-truck class="w-4 h-4 text-gray-400" />
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Pengiriman:</span>
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                            @if ($delivery->pengirimanDriver?->approval_status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @elseif($delivery->pengirimanDriver?->approval_status === 'approved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @elseif($delivery->pengirimanDriver?->approval_status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                        @if ($delivery->pengirimanDriver?->approval_status === 'pending')
                                            Menunggu
                                        @elseif($delivery->pengirimanDriver?->approval_status === 'approved')
                                            Disetujui
                                        @elseif($delivery->pengirimanDriver?->approval_status === 'rejected')
                                            Ditolak
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-wrap gap-1">
                                <!-- View Details -->
                                <a href="{{ route('filament.admin.pages.driver-delivery-detail', ['record' => $delivery->id]) }}"
                                    class="inline-flex items-center px-2 py-1 border border-gray-300 dark:border-gray-600 rounded text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <x-heroicon-o-eye class="w-3 h-3 mr-1" />
                                    Detail
                                </a>

                                <!-- Status Update -->
                                @if ($delivery->status_muat !== 'selesai')
                                    <button type="button" onclick="updateStatus('{{ $delivery->id }}')"
                                        class="inline-flex items-center px-2 py-1 border border-yellow-300 dark:border-yellow-600 rounded text-xs font-medium text-yellow-700 dark:text-yellow-300 bg-yellow-50 dark:bg-yellow-900 hover:bg-yellow-100 dark:hover:bg-yellow-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                        <x-heroicon-o-pencil-square class="w-3 h-3 mr-1" />
                                        Update
                                    </button>
                                @endif

                                <!-- Approval Actions -->
                                @if ($delivery->uangJalan && $delivery->uangJalan->approval_status === 'pending')
                                    <button type="button" onclick="approveAllowance('{{ $delivery->id }}')"
                                        class="inline-flex items-center px-2 py-1 border border-green-300 dark:border-green-600 rounded text-xs font-medium text-green-700 dark:text-green-300 bg-green-50 dark:bg-green-900 hover:bg-green-100 dark:hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        <x-heroicon-o-banknotes class="w-3 h-3 mr-1" />
                                        ACC UJ
                                    </button>
                                @endif

                                @if ($delivery->pengirimanDriver && $delivery->pengirimanDriver->approval_status === 'pending')
                                    <button type="button" onclick="approveDelivery('{{ $delivery->id }}')"
                                        class="inline-flex items-center px-2 py-1 border border-blue-300 dark:border-blue-600 rounded text-xs font-medium text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900 hover:bg-blue-100 dark:hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <x-heroicon-o-truck class="w-3 h-3 mr-1" />
                                        ACC Kirim
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <x-heroicon-o-truck class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Tidak ada pengiriman
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Belum ada pengiriman yang
                            ditugaskan kepada Anda.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                Aksi Cepat
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @can('view_any_delivery_order')
                    <a href="{{ route('filament.admin.resources.delivery-orders.index') }}"
                        class="flex items-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                        <x-heroicon-o-clipboard-document-list class="h-6 w-6 text-blue-600 dark:text-blue-400 mr-3" />
                        <span class="text-sm font-medium text-blue-900 dark:text-blue-100">Lihat Semua DO</span>
                    </a>
                @endcan

                @can('view_any_pengiriman_driver')
                    <a href="{{ route('filament.admin.resources.pengiriman-drivers.index') }}"
                        class="flex items-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                        <x-heroicon-o-truck class="h-6 w-6 text-green-600 dark:text-green-400 mr-3" />
                        <span class="text-sm font-medium text-green-900 dark:text-green-100">Data Pengiriman</span>
                    </a>
                @endcan

                @can('view_any_uang_jalan')
                    <a href="{{ route('filament.admin.resources.uang-jalans.index') }}"
                        class="flex items-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors">
                        <x-heroicon-o-banknotes class="h-6 w-6 text-purple-600 dark:text-purple-400 mr-3" />
                        <span class="text-sm font-medium text-purple-900 dark:text-purple-100">Uang Jalan</span>
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- JavaScript for Compact View Actions -->
    <script>
        function updateStatus(deliveryId) {
            // Trigger Filament action for status update
            $wire.mountTableAction('update_status', deliveryId);
        }

        function approveAllowance(deliveryId) {
            // Trigger Filament action for allowance approval
            $wire.mountTableAction('approve_allowance', deliveryId);
        }

        function approveDelivery(deliveryId) {
            // Trigger Filament action for delivery approval
            $wire.mountTableAction('approve_delivery', deliveryId);
        }
    </script>
</x-filament-panels::page>
