<div class="flow-root">
    <ul role="list" class="-mb-8">
        <!-- Sales Order Created -->
        <li>
            <div class="relative pb-8">
                <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700"
                    aria-hidden="true"></span>
                <div class="relative flex items-start space-x-3">
                    <div class="relative">
                        <div
                            class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div>
                            <div class="text-sm">
                                <span class="font-medium text-gray-900 dark:text-white">Sales Order Created</span>
                            </div>
                            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                {{ $record->tanggal ? $record->tanggal->format('d M Y H:i') : 'N/A' }}
                            </p>
                        </div>
                        <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                <p class="font-medium">SO Number: {{ $record->kode }}</p>
                                <p>Customer: {{ $record->pelanggan->nama ?? 'N/A' }}</p>
                                <p>Fuel Type: {{ $record->penjualanDetails->pluck('item.name')->unique()->join(', ') }}
                                </p>
                                <p>Volume: {{ number_format($record->penjualanDetails->sum('volume_item'), 2) }} L</p>
                                <p>TBBM: {{ $record->tbbm->nama ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </li>

        <!-- Delivery Order Created -->
        @php
            $deliveryOrders = \App\Models\DeliveryOrder::where('id_transaksi', $record->id)->get();
        @endphp

        @foreach ($deliveryOrders as $do)
            <li>
                <div class="relative pb-8">
                    <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700"
                        aria-hidden="true"></span>
                    <div class="relative flex items-start space-x-3">
                        <div class="relative">
                            <div
                                class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path
                                        d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                    <path
                                        d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1v-5h2.05a2.5 2.5 0 014.9 0H18a1 1 0 001-1V5a1 1 0 00-1-1H3z" />
                                </svg>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div>
                                <div class="text-sm">
                                    <span class="font-medium text-gray-900 dark:text-white">Delivery Order
                                        Created</span>
                                </div>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $do->created_at ? $do->created_at->format('d M Y H:i') : 'N/A' }}
                                </p>
                            </div>
                            <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                    <p class="font-medium">DO Number: {{ $do->kode }}</p>
                                    <p>Delivery Date:
                                        {{ $do->tanggal_delivery ? $do->tanggal_delivery->format('d M Y') : 'Not scheduled' }}
                                    </p>
                                    <p>Vehicle: {{ $do->kendaraan->nomor_polisi ?? 'Not assigned' }}</p>
                                    <p>Driver: {{ $do->user->name ?? 'Not assigned' }}</p>
                                    <p>Seal Number: {{ $do->no_segel ?? 'Not set' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </li>

            <!-- Loading Status -->
            <li>
                <div class="relative pb-8">
                    <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700"
                        aria-hidden="true"></span>
                    <div class="relative flex items-start space-x-3">
                        <div class="relative">
                            <div
                                class="h-10 w-10 rounded-full bg-yellow-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div>
                                <div class="text-sm">
                                    <span class="font-medium text-gray-900 dark:text-white">Loading Status</span>
                                </div>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $do->waktu_muat ? $do->waktu_muat->format('d M Y H:i') : 'Not started' }}
                                </p>
                            </div>
                            <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                    <p class="font-medium">Status:
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $do->status_muat === 'pending'
                                            ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100'
                                            : ($do->status_muat === 'muat'
                                                ? 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100'
                                                : 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100') }}">
                                            {{ $do->status_muat === 'pending' ? 'Pending' : ($do->status_muat === 'muat' ? 'Loading' : 'Completed') }}
                                        </span>
                                    </p>
                                    @if ($do->waktu_muat)
                                        <p>Loading Started: {{ $do->waktu_muat->format('d M Y H:i') }}</p>
                                    @endif
                                    @if ($do->waktu_selesai_muat)
                                        <p>Loading Completed: {{ $do->waktu_selesai_muat->format('d M Y H:i') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </li>

            <!-- Driver Allowance -->
            @php
                $uangJalan = \App\Models\UangJalan::where('id_do', $do->id)->first();
            @endphp

            @if ($uangJalan)
                <li>
                    <div class="relative pb-8">
                        <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700"
                            aria-hidden="true"></span>
                        <div class="relative flex items-start space-x-3">
                            <div class="relative">
                                <div
                                    class="h-10 w-10 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                                        <path fill-rule="evenodd"
                                            d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div>
                                    <div class="text-sm">
                                        <span class="font-medium text-gray-900 dark:text-white">Driver Allowance</span>
                                    </div>
                                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $uangJalan->created_at ? $uangJalan->created_at->format('d M Y H:i') : 'N/A' }}
                                    </p>
                                </div>
                                <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                        <p class="font-medium">Amount: Rp
                                            {{ number_format($uangJalan->nominal, 0, ',', '.') }}</p>
                                        <p>Status:
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $uangJalan->status_kirim
                                            ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'
                                            : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100' }}">
                                                {{ $uangJalan->status_kirim ? 'Sent' : 'Pending' }}
                                            </span>
                                        </p>
                                        <p>Received:
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $uangJalan->status_terima
                                            ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'
                                            : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100' }}">
                                                {{ $uangJalan->status_terima ? 'Confirmed' : 'Pending' }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            @endif

            <!-- Delivery Status -->
            @php
                $pengirimanDriver = \App\Models\PengirimanDriver::where('id_do', $do->id)->first();
            @endphp

            @if ($pengirimanDriver)
                <li>
                    <div class="relative pb-8">
                        <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700"
                            aria-hidden="true"></span>
                        <div class="relative flex items-start space-x-3">
                            <div class="relative">
                                <div
                                    class="h-10 w-10 rounded-full bg-purple-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div>
                                    <div class="text-sm">
                                        <span class="font-medium text-gray-900 dark:text-white">Delivery Status</span>
                                    </div>
                                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $pengirimanDriver->waktu_berangkat ? $pengirimanDriver->waktu_berangkat->format('d M Y H:i') : 'Not started' }}
                                    </p>
                                </div>
                                <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                        <p class="font-medium">Status:
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ !$pengirimanDriver->waktu_berangkat
                                            ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100'
                                            : (!$pengirimanDriver->waktu_tiba
                                                ? 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100'
                                                : (!$pengirimanDriver->waktu_selesai
                                                    ? 'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100'
                                                    : 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100')) }}">
                                                {{ !$pengirimanDriver->waktu_berangkat
                                                    ? 'Not Started'
                                                    : (!$pengirimanDriver->waktu_tiba
                                                        ? 'In Transit'
                                                        : (!$pengirimanDriver->waktu_selesai
                                                            ? 'Arrived'
                                                            : 'Completed')) }}
                                            </span>
                                        </p>
                                        @if ($pengirimanDriver->waktu_berangkat)
                                            <p>Departure: {{ $pengirimanDriver->waktu_berangkat->format('d M Y H:i') }}
                                            </p>
                                        @endif
                                        @if ($pengirimanDriver->waktu_tiba)
                                            <p>Arrival: {{ $pengirimanDriver->waktu_tiba->format('d M Y H:i') }}</p>
                                        @endif
                                        @if ($pengirimanDriver->waktu_selesai)
                                            <p>Completion: {{ $pengirimanDriver->waktu_selesai->format('d M Y H:i') }}
                                            </p>
                                        @endif
                                        @if ($pengirimanDriver->volume_terkirim)
                                            <p>Volume Delivered:
                                                {{ number_format($pengirimanDriver->volume_terkirim, 2) }} L</p>
                                        @endif

                                        @if ($pengirimanDriver->foto_totalizer_awal || $pengirimanDriver->foto_totalizer_akhir)
                                            <div class="mt-2 grid grid-cols-2 gap-2">
                                                @if ($pengirimanDriver->foto_totalizer_awal)
                                                    <div>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">
                                                            Initial Totalizer</p>
                                                        <img src="{{ asset('storage/' . $pengirimanDriver->foto_totalizer_awal) }}"
                                                            alt="Initial Totalizer"
                                                            class="h-24 w-auto object-cover rounded-lg">
                                                    </div>
                                                @endif

                                                @if ($pengirimanDriver->foto_totalizer_akhir)
                                                    <div>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Final
                                                            Totalizer</p>
                                                        <img src="{{ asset('storage/' . $pengirimanDriver->foto_totalizer_akhir) }}"
                                                            alt="Final Totalizer"
                                                            class="h-24 w-auto object-cover rounded-lg">
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            @endif

            {{-- <!-- Invoice Status -->
        @php
            $invoice = \App\Models\Invoice::whereHas('invoiceDetails', function($query) use ($do) {
                $query->where('id_do', $do->id);
            })->first();
        @endphp

        @if ($invoice)
        <li>
            <div class="relative pb-8">
                @if (!$loop->last)
                <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                @endif
                <div class="relative flex items-start space-x-3">
                    <div class="relative">
                        <div class="h-10 w-10 rounded-full bg-pink-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div>
                            <div class="text-sm">
                                <span class="font-medium text-gray-900 dark:text-white">Invoice Generated</span>
                            </div>
                            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                {{ $invoice->tanggal ? $invoice->tanggal->format('d M Y H:i') : 'N/A' }}
                            </p>
                        </div>
                        <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                <p class="font-medium">Invoice Number: {{ $invoice->nomor_invoice }}</p>
                                <p>Tax Invoice: {{ $invoice->nomor_faktur ?? 'Not issued' }}</p>
                                <p>Amount: Rp {{ number_format($invoice->total_invoice, 0, ',', '.') }}</p>
                                <p>Status:
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $invoice->status_pembayaran ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' :
                                           'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100' }}">
                                        {{ $invoice->status_pembayaran ? 'Paid' : 'Unpaid' }}
                                    </span>
                                </p>
                                @if ($invoice->tanggal_pembayaran)
                                <p>Payment Date: {{ $invoice->tanggal_pembayaran->format('d M Y') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </li>
        @endif --}}
        @endforeach
    </ul>
</div>
