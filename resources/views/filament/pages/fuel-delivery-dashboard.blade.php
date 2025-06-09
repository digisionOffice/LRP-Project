<x-filament-panels::page>
    <!-- Summary Cards (Optional) -->

    <div class="mt-8 grid grid-cols-4 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-shopping-cart class="h-6 w-6 text-gray-400" />
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Total Sales Orders
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
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
                                Active Deliveries
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
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
                                Completed Deliveries
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
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
                                Pending Payments
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ \App\Models\DeliveryOrder::whereIn('payment_status', ['pending', 'partial', 'overdue'])->count() }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button wire:click="$set('activeTab', 'sales')"
                    class="@if ($activeTab === 'sales') border-primary-500 text-primary-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    <div class="flex items-center space-x-2">
                        <x-heroicon-o-shopping-cart class="w-5 h-5" />
                        <span>Sales</span>
                    </div>
                </button>

                <button wire:click="$set('activeTab', 'operations')"
                    class="@if ($activeTab === 'operations') border-primary-500 text-primary-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    <div class="flex items-center space-x-2">
                        <x-heroicon-o-truck class="w-5 h-5" />
                        <span>Operations</span>
                    </div>
                </button>

                <button wire:click="$set('activeTab', 'administration')"
                    class="@if ($activeTab === 'administration') border-primary-500 text-primary-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    <div class="flex items-center space-x-2">
                        <x-heroicon-o-clipboard-document-list class="w-5 h-5" />
                        <span>Administration</span>
                    </div>
                </button>

                <button wire:click="$set('activeTab', 'driver')"
                    class="@if ($activeTab === 'driver') border-primary-500 text-primary-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    <div class="flex items-center space-x-2">
                        <x-heroicon-o-user class="w-5 h-5" />
                        <span>Driver</span>
                    </div>
                </button>

                <button wire:click="$set('activeTab', 'finance')"
                    class="@if ($activeTab === 'finance') border-primary-500 text-primary-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    <div class="flex items-center space-x-2">
                        <x-heroicon-o-banknotes class="w-5 h-5" />
                        <span>Finance</span>
                    </div>
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="mt-6">
            @if ($activeTab === 'sales')
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Sales Orders</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Manage sales order data with customer information, fuel types, and delivery locations.
                        </p>
                    </div>
                    <div class="p-6">
                        {{ $this->table }}
                    </div>
                </div>
            @elseif($activeTab === 'operations')
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Operations</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Track operational delivery data including truck assignments and loading status.
                        </p>
                    </div>
                    <div class="p-6">
                        {{ $this->table }}
                    </div>
                </div>
            @elseif($activeTab === 'administration')
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Administration</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Manage delivery order administrative data including seals, signatories, and allowances.
                        </p>
                    </div>
                    <div class="p-6">
                        {{ $this->table }}
                    </div>
                </div>
            @elseif($activeTab === 'driver')
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Driver Activities</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Track driver delivery activities including totalizer readings and delivery photos.
                        </p>
                    </div>
                    <div class="p-6">
                        {{ $this->table }}
                    </div>
                </div>
            @elseif($activeTab === 'finance')
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Finance & Invoicing</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Manage financial and invoicing data including payment status and invoice tracking.
                        </p>
                    </div>
                    <div class="p-6">
                        {{ $this->table }}
                    </div>
                </div>
            @endif
        </div>
    </div>


</x-filament-panels::page>
