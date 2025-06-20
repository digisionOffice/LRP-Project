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

        <!-- Balance Sheet Report -->
        @if($this->report_date)
            @php
                $data = $this->getBalanceSheetData();
            @endphp

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        NERACA
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                        Per {{ \Carbon\Carbon::parse($this->report_date)->format('d F Y') }}
                    </p>
                </div>

                <!-- Balance Status -->
                @if($data['is_balanced'])
                    <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-green-800 dark:text-green-200 font-medium">
                                Neraca Seimbang: Aset = Kewajiban + Ekuitas
                            </span>
                        </div>
                    </div>
                @else
                    <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-red-800 dark:text-red-200 font-medium">
                                Neraca Tidak Seimbang! Periksa kembali jurnal Anda.
                            </span>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- ASET -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
                            ASET
                        </h3>

                        @if(count($data['assets']) > 0)
                            <div class="space-y-2">
                                @foreach($data['assets'] as $asset)
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-gray-700 dark:text-gray-300">
                                            {{ $asset['account']->kode_akun }} - {{ $asset['account']->nama_akun }}
                                        </span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">
                                            Rp {{ number_format($asset['balance'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endforeach

                                <div class="flex justify-between items-center py-3 border-t-2 border-gray-300 dark:border-gray-600 font-bold text-lg">
                                    <span class="text-gray-900 dark:text-gray-100">TOTAL ASET</span>
                                    <span class="text-blue-600 dark:text-blue-400">
                                        Rp {{ number_format($data['total_assets'], 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @else
                            <p class="text-gray-500 dark:text-gray-400 italic">Tidak ada data aset</p>
                        @endif
                    </div>

                    <!-- KEWAJIBAN & EKUITAS -->
                    <div>
                        <!-- KEWAJIBAN -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
                                KEWAJIBAN
                            </h3>

                            @if(count($data['liabilities']) > 0)
                                <div class="space-y-2">
                                    @foreach($data['liabilities'] as $liability)
                                        <div class="flex justify-between items-center py-1">
                                            <span class="text-gray-700 dark:text-gray-300">
                                                {{ $liability['account']->kode_akun }} - {{ $liability['account']->nama_akun }}
                                            </span>
                                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                                Rp {{ number_format($liability['balance'], 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @endforeach

                                    <div class="flex justify-between items-center py-2 border-t border-gray-200 dark:border-gray-700 font-semibold">
                                        <span class="text-gray-900 dark:text-gray-100">Total Kewajiban</span>
                                        <span class="text-red-600 dark:text-red-400">
                                            Rp {{ number_format($data['total_liabilities'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            @else
                                <p class="text-gray-500 dark:text-gray-400 italic">Tidak ada data kewajiban</p>
                            @endif
                        </div>

                        <!-- EKUITAS -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
                                EKUITAS
                            </h3>

                            @if(count($data['equity']) > 0)
                                <div class="space-y-2">
                                    @foreach($data['equity'] as $equity)
                                        <div class="flex justify-between items-center py-1">
                                            <span class="text-gray-700 dark:text-gray-300">
                                                {{ $equity['account']->kode_akun }} - {{ $equity['account']->nama_akun }}
                                            </span>
                                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                                Rp {{ number_format($equity['balance'], 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @endforeach

                                    <div class="flex justify-between items-center py-2 border-t border-gray-200 dark:border-gray-700 font-semibold">
                                        <span class="text-gray-900 dark:text-gray-100">Total Ekuitas</span>
                                        <span class="text-green-600 dark:text-green-400">
                                            Rp {{ number_format($data['total_equity'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            @else
                                <p class="text-gray-500 dark:text-gray-400 italic">Tidak ada data ekuitas</p>
                            @endif
                        </div>

                        <!-- TOTAL KEWAJIBAN + EKUITAS -->
                        <div class="mt-6 pt-4 border-t-2 border-gray-300 dark:border-gray-600">
                            <div class="flex justify-between items-center py-3 bg-gray-50 dark:bg-gray-700 px-4 rounded-lg font-bold text-lg">
                                <span class="text-gray-900 dark:text-gray-100">
                                    TOTAL KEWAJIBAN + EKUITAS
                                </span>
                                <span class="text-blue-600 dark:text-blue-400">
                                    Rp {{ number_format($data['total_liabilities_equity'], 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="text-center py-8">
                    <div class="text-gray-400 dark:text-gray-500 mb-2">
                        <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">
                        Pilih Tanggal Laporan
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400">
                        Silakan pilih tanggal untuk melihat laporan neraca
                    </p>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
