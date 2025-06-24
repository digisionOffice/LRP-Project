<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filter Form -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            {{ $this->form }}
        </div>

        <!-- Account Summary -->
        @if($this->getSelectedAccount())
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Ringkasan Akun: {{ $this->getSelectedAccount()->kode_akun }} - {{ $this->getSelectedAccount()->nama_akun }}
                </h3>

                @php
                    $summary = $this->getAccountSummary();
                @endphp

                @if(!empty($summary))
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                            <div class="text-sm font-medium text-blue-600 dark:text-blue-400">Saldo Awal</div>
                            <div class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                                Rp {{ number_format($summary['opening_balance'], 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                            <div class="text-sm font-medium text-green-600 dark:text-green-400">Total Debit</div>
                            <div class="text-2xl font-bold text-green-900 dark:text-green-100">
                                Rp {{ number_format($summary['total_debit'], 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
                            <div class="text-sm font-medium text-red-600 dark:text-red-400">Total Credit</div>
                            <div class="text-2xl font-bold text-red-900 dark:text-red-100">
                                Rp {{ number_format($summary['total_credit'], 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg">
                            <div class="text-sm font-medium text-purple-600 dark:text-purple-400">Saldo Akhir</div>
                            <div class="text-2xl font-bold text-purple-900 dark:text-purple-100">
                                Rp {{ number_format($summary['ending_balance'], 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Transactions Table -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            {{ $this->table }}
        </div>

        @if(!$this->getSelectedAccount())
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="text-center py-8">
                    <div class="text-gray-400 dark:text-gray-500 mb-2">
                        <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">
                        Pilih Akun untuk Melihat Buku Besar
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400">
                        Silakan pilih akun dari dropdown di atas untuk melihat detail transaksi
                    </p>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
