<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filter Form -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            {{ $this->form }}

            <div class="mt-6 flex justify-end">
                <x-filament::button
                    color="primary"
                    icon="heroicon-o-document-arrow-down"
                    onclick="alert('Fitur export PDF akan segera tersedia')">
                    Export PDF
                </x-filament::button>
            </div>
        </div>

        <!-- Income Statement Report -->
        @if($this->start_date && $this->end_date)
            @php
                $data = $this->getIncomeStatementData();
            @endphp

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        LAPORAN LABA RUGI
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                        Periode {{ \Carbon\Carbon::parse($this->start_date)->format('d F Y') }} - {{ \Carbon\Carbon::parse($this->end_date)->format('d F Y') }}
                    </p>
                </div>

                <div class="space-y-8">
                    <!-- PENDAPATAN -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
                            PENDAPATAN
                        </h3>

                        @if(count($data['revenues']) > 0)
                            <div class="space-y-2">
                                @foreach($data['revenues'] as $revenue)
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-gray-700 dark:text-gray-300">
                                            {{ $revenue['account']->kode_akun }} - {{ $revenue['account']->nama_akun }}
                                        </span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">
                                            Rp {{ number_format($revenue['balance'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endforeach

                                <div class="flex justify-between items-center py-3 border-t-2 border-gray-300 dark:border-gray-600 font-bold text-lg">
                                    <span class="text-gray-900 dark:text-gray-100">TOTAL PENDAPATAN</span>
                                    <span class="text-green-600 dark:text-green-400">
                                        Rp {{ number_format($data['total_revenue'], 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @else
                            <p class="text-gray-500 dark:text-gray-400 italic">Tidak ada data pendapatan</p>
                        @endif
                    </div>

                    <!-- BEBAN -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
                            BEBAN
                        </h3>

                        @if(count($data['expenses']) > 0)
                            <div class="space-y-2">
                                @foreach($data['expenses'] as $expense)
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-gray-700 dark:text-gray-300">
                                            {{ $expense['account']->kode_akun }} - {{ $expense['account']->nama_akun }}
                                        </span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">
                                            Rp {{ number_format($expense['balance'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endforeach

                                <div class="flex justify-between items-center py-3 border-t-2 border-gray-300 dark:border-gray-600 font-bold text-lg">
                                    <span class="text-gray-900 dark:text-gray-100">TOTAL BEBAN</span>
                                    <span class="text-red-600 dark:text-red-400">
                                        Rp {{ number_format($data['total_expense'], 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @else
                            <p class="text-gray-500 dark:text-gray-400 italic">Tidak ada data beban</p>
                        @endif
                    </div>

                    <!-- LABA BERSIH -->
                    <div class="pt-6 border-t-4 border-gray-400 dark:border-gray-600">
                        <div class="flex justify-between items-center py-4 bg-gray-50 dark:bg-gray-700 px-6 rounded-lg">
                            <span class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                LABA BERSIH
                            </span>
                            <span class="text-2xl font-bold {{ $data['net_income'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                Rp {{ number_format($data['net_income'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="text-center py-8">
                    <div class="text-gray-400 dark:text-gray-500 mb-2">
                        <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">
                        Pilih Periode Laporan
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400">
                        Silakan pilih tanggal mulai dan akhir untuk melihat laporan laba rugi
                    </p>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
