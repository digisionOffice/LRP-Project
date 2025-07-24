{{--
    This Blade file renders a preview of the SPH document.
    Location: resources/views/sph/sph-preview.blade.php
--}}

@php
    // The $record variable is passed in from the View::make() call.

    // --- UPDATED: The query now specifically fetches only the two required ISOs ---
    $isoNamesToDisplay = ['ISO 9001:2015', 'ISO 45001:2018'];
    $isoCertifications = \App\Models\IsoCertification::where('is_active', true)
        ->whereIn('name', $isoNamesToDisplay)
        ->get();
@endphp

<div class="p-4 sm:p-6 bg-white font-sans text-gray-800">

    <div class="flex justify-end mb-4 print:hidden">
        <button onclick="window.parent.postMessage({action: 'downloadPdf', recordId: {{ $record->id }}}, '*')"
            class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-500 focus:outline-none focus:border-primary-700 focus:ring focus:ring-primary-200 active:bg-primary-700 disabled:opacity-25 transition">
            <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M10 3a.75.75 0 01.75.75v6.19l1.97-1.97a.75.75 0 111.06 1.06l-3.25 3.25a.75.75 0 01-1.06 0l-3.25-3.25a.75.75 0 111.06-1.06l1.97 1.97V3.75A.75.75 0 0110 3zM3.75 14.25a.75.75 0 01.75-.75h10.5a.75.75 0 010 1.5H4.5a.75.75 0 01-.75-.75z"
                    clip-rule="evenodd" />
            </svg>
            Download PDF
        </button>
    </div>

    {{-- Header Section --}}
    <header class="mb-8">
        <table class="w-full">
            <tbody>
                <tr>
                    {{-- Column 1: Logo (Left) --}}
                    <td class="w-1/3">
                        <img src="{{ asset('images/lrp.png') }}" alt="PT. Lintas Riau Prima" style="height: 150px;"
                            class="mb-2">
                    </td>
                    {{-- Column 2: Empty Spacer (Center) --}}
                    <td class="w-1/3"></td>
                    {{-- Column 3: Partner Text (Right) --}}
                    <td class="w-1/3 text-right">
                        <h2 class="font-bold text-lg">TRUSTED & RELIABLE PARTNER</h2>
                        <p class="text-xs">Fuel Agent – Fuel Transportation – Bunker Service</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </header>

    {{-- Document Info Section --}}
    <section class="mb-8">
        <div class="float-right text-sm">
            Pekanbaru, {{ $record->sph_date->format('d F Y') }}
        </div>
        <div class="clear-both"></div>

        <table class="text-sm mt-4">
            <tbody>
                <tr>
                    <td class="pr-2">No</td>
                    <td class="pr-2">:</td>
                    <td class="font-semibold">{{ $record->sph_number }}</td>
                </tr>
                <tr>
                    <td class="pr-2">Lampiran</td>
                    <td class="pr-2">:</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td class="pr-2 align-top">Perihal</td>
                    <td class="pr-2 align-top">:</td>
                    <td class="font-semibold">Penawaran Harga Pertalite Industri<br>Pertamina Patra Niaga</td>
                </tr>
            </tbody>
        </table>
    </section>

    {{-- Recipient Section --}}
    <section class="mb-8 text-sm">
        <p>Kepada Yth.</p>
        <p class="font-bold">{{ $record->customer?->nama }}</p>
        @if ($record->opsional_pic)
            <p>Up: {{ $record->opsional_pic }}</p>
        @elseif($record->customer?->pic_nama)
            <p>Up: {{ $record->customer->pic_nama }}</p>
        @endif
        <p>Di –</p>
        <p class="ml-4">Tempat</p>
    </section>

    {{-- Body / Salutation --}}
    <section class="mb-6 text-sm leading-relaxed">
        <p class="mb-4">Salam hormat,</p>
        <p>
            Sehubungan dengan adanya informasi kebutuhan BBM Pertalite industri untuk PT. Amico Putera Perkasa, maka
            bersama ini kami kirimkan surat penawaran harga untuk periode
            <span class="font-bold">{{ $record->sph_date->format('d M Y') }}</span> s/d <span
                class="font-bold">{{ $record->valid_until_date->format('d M Y') }}</span>.
        </p>
    </section>

    {{-- Product Offering Section --}}
    <section class="mb-8 text-sm">
        <p class="mb-2 font-bold">Produk BBM yang kami tawarkan yaitu :</p>
        <div class="pl-4">
            <table class="w-full">
                <tbody>
                    <tr>
                        <td class="w-4 pr-2 align-top">1.</td>
                        <td class="font-semibold pr-2 w-28">Nama Produk</td>
                        <td>: Pertalite Industri.</td>
                    </tr>
                    <tr>
                        <td class="pr-2 align-top">2.</td>
                        <td class="font-semibold pr-2">Spesifikasi</td>
                        <td>: Standar Dirjen Migas & International ASTM</td>
                    </tr>
                    <tr>
                        <td class="pr-2 align-top">3.</td>
                        <td class="font-semibold pr-2">Legalitas</td>
                        <td>: Full Document/ Resmi dari PT. Pertamina Patra Niaga</td>
                    </tr>
                    <tr>
                        <td class="pr-2 align-top">4.</td>
                        <td class="font-semibold pr-2">Sumber</td>
                        <td>: Pertamina Patra Niaga</td>
                    </tr>
                    <tr>
                        <td class="pr-2 align-top">5.</td>
                        <td class="font-semibold pr-2">TKDN</td>
                        <td>: 99,93% berdasarkan laporan Kementrian Perindustrian RI</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    {{-- Price Details Table --}}
    <section class="mb-8">
        <h2 class="text-lg font-semibold mb-2">Harga Penawaran yang kami berikan yaitu:</h2>
        <table class="w-full text-left text-sm border-collapse border border-gray-400">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 border border-gray-300 text-center">No</th>
                    <th class="p-2 border border-gray-300">Rincian</th>
                    <th class="p-2 border border-gray-300 text-right">Harga/Liter</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($record->details as $idx => $detail)
                    <tr class="border-t border-gray-300">
                        <td class="p-2 border border-gray-300 text-center">{{ $idx + 1 }}</td>
                        <td class="p-2 border border-gray-300">Dasar BBM</td>
                        <td class="p-2 border border-gray-300 text-right">
                            {{ number_format($detail->harga_dasar, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="p-2 border border-gray-300 text-center"></td>
                        <td class="p-2 border border-gray-300">PPN BBM 11%</td>
                        <td class="p-2 border border-gray-300 text-right">
                            {{ number_format($detail->ppn, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="p-2 border border-gray-300 text-center"></td>
                        <td class="p-2 border border-gray-300">Oat</td>
                        <td class="p-2 border border-gray-300 text-right">
                            {{ number_format($detail->oat, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="font-bold bg-gray-100">
                        <td colspan="2" class="p-2 border border-gray-300">Total Penawaran</td>
                        <td class="p-2 border border-gray-300 text-right">
                            {{ number_format($detail->price, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    {{-- Terms and Conditions --}}
    <section class="mb-8 text-sm">
        <h2 class="text-lg font-semibold mb-2">Syarat dan Ketentuan</h2>
        <ol class="list-none space-y-2">
            <li class="flex"><span class="mr-2">1.</span><span>Penawaran harga ini berlaku pada periode
                    {{ $record->sph_date->format('d M Y') }} -
                    {{ $record->valid_until_date->format('d M Y') }}.</span></li>
            <li class="flex"><span class="mr-2">2.</span>
                <div><span>Pembayaran tagihan CASH hari setelah Dokumen diterima melalui transfer ke Bank BNI</span>
                    <div class="font-semibold ml-4">No Rekening: 217736160 An. PT. Lintas Riau Prima</div>
                </div>
            </li>
            <li class="flex"><span class="mr-2">3.</span><span>PO kami terima minimal 3 (Tiga) hari (via email atau
                    WA) sebelum pengiriman.</span></li>
            <li class="flex"><span class="mr-2">4.</span><span>Untuk kondisi mendesak/urgent dapat berkoordinasi
                    langsung sebelum pukul 12.00 Wib. pada hari yang sama.</span></li>
        </ol>
    </section>

    {{-- Signature Section --}}
    <section class="pt-8 flex justify-end">
        <div class="text-center">
            <p class="text-sm">Hormat kami</p>
            <p class="text-sm">PT Lintas Riau Prima</p>
            {{-- Placeholder for signature image --}}
            <img src="https://placehold.co/150x60/ffffff/000000?text=Signature" alt="Signature" class="mx-auto my-2">
            <p class="text-sm font-bold underline">{{ $record->createdBy?->name ?? 'N/A' }}</p>
            <p class="text-xs text-gray-600">{{ $record->createdBy?->position ?? 'Manager Pemasaran' }}</p>
        </div>
    </section>

    {{-- Footer Section --}}
    <footer class="mt-16 pt-4 border-t-4 border-blue-800 flex justify-between items-center text-xs">
        <div class="flex items-center space-x-2">
            @if (isset($isoCertifications))
                @foreach ($isoCertifications as $cert)
                    <img src="{{ $cert->logo_url }}" alt="{{ $cert->name }}" class="h-10">
                @endforeach
            @endif
        </div>
        <div class="text-center">
            <p class="font-bold">PT. LINTAS RIAU PRIMA</p>
            <p>Jl. Mesjid Al Furqon No. 26</p>
            <p>Pekanbaru, Riau. 28144</p>
        </div>
        <div class="text-left">
            <p>☎️ 0761-22369</p>
            <p>✉️ office@lintasriauprima.com</p>
            <p>🌐 www.lintasriauprima.com</p>
        </div>
    </footer>
</div>
