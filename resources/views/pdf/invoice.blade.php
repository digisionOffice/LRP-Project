<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $record->nomor_invoice }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #000;
            padding: 15px;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            position: relative;
        }

        .company-section {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            flex: 1;
        }

        .company-logo {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 10px;
            text-align: center;
            line-height: 1.1;
            flex-shrink: 0;
            overflow: hidden;
        }

        .company-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .company-logo-fallback {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: white;
        }

        .company-info {
            flex: 1;
            margin-left: 10px;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 3px;
        }

        .company-tagline {
            font-size: 10px;
            color: #000;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .company-services {
            font-size: 9px;
            color: #666;
            margin-bottom: 8px;
        }

        .header-right {
            position: absolute;
            top: 0;
            right: 0;
            width: 120px;
            height: 80px;
            background-color: #1e40af;
        }

        .invoice-title-section {
            text-align: center;
            margin: 15px 0;
        }

        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #000;
            margin-bottom: 5px;
        }

        .invoice-number {
            font-size: 11px;
            color: #000;
        }

        .customer-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .customer-left,
        .customer-right {
            width: 48%;
        }

        .detail-row {
            display: flex;
            margin-bottom: 3px;
            font-size: 10px;
        }

        .detail-label {
            width: 140px;
            color: #000;
            flex-shrink: 0;
        }

        .detail-colon {
            width: 15px;
            text-align: center;
            flex-shrink: 0;
        }

        .detail-value {
            flex: 1;
            color: #000;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10px;
        }

        .items-table th {
            background-color: #fff;
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
            font-weight: bold;
            color: #000;
            font-size: 10px;
        }

        .items-table td {
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
            font-size: 10px;
            color: #000;
        }

        .items-table .text-left {
            text-align: left;
        }

        .items-table .text-right {
            text-align: right;
        }

        .totals-row {
            background-color: #f8f9fa;
        }

        .totals-row td {
            font-weight: bold;
        }

        .terbilang-section {
            margin: 10px 0;
            font-size: 10px;
        }

        .payment-notes {
            margin: 15px 0;
            font-size: 9px;
        }

        .signature-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .signature-box {
            text-align: center;
            width: 150px;
        }

        .signature-location {
            margin-bottom: 5px;
            font-size: 10px;
        }

        .signature-space {
            height: 60px;
            margin: 15px 0;
        }

        .signature-name {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 2px;
        }

        .signature-title {
            font-size: 9px;
        }

        .footer {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9px;
            border-top: 1px solid #000;
            padding-top: 10px;
        }

        .footer-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer-logo {
            width: 40px;
            height: 40px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
        }

        .footer-company {
            font-weight: bold;
        }

        .footer-contact {
            text-align: right;
        }

        @media print {
            body {
                margin: 0;
                padding: 10px;
            }

            .header {
                page-break-inside: avoid;
            }

            .items-table {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <!-- Header Section -->
        <div class="header">
            <div class="company-section">
                <div class="company-logo {{ empty($logoBase64) ? 'company-logo-fallback' : '' }}">
                    @if (!empty($logoBase64))
                        <img src="data:image/png;base64,{{ $logoBase64 }}" alt="Company Logo">
                    @else
                        LINTAS<br>RIAU<br>PRIMA
                    @endif
                </div>
                <div class="company-info">
                    <div class="company-name">PT. LINTAS RIAU PRIMA</div>
                    <div class="company-tagline">TRUSTED & RELIABLE PARTNER</div>
                    <div class="company-services">Fuel Agent - Fuel Transportation - Bunker Service</div>
                </div>
            </div>
            <div class="header-right"></div>
        </div>

        <!-- Invoice Title -->
        <div class="invoice-title-section">
            <div class="invoice-title">INVOICE</div>
            <div class="invoice-number">{{ $record->nomor_invoice }}</div>
        </div>

        @php
            // Calculate totals at the top level to ensure variable scope
            // Handle null/zero values gracefully and use proper field names
            $finalTotalPenjualan = $record->subtotal ?? ($record->total_amount ?? 0);
            $includePpn = $record->include_ppn ?? true;
            $includeOperasional = $record->include_operasional_kerja ?? false;
            $includePbbkb = $record->include_pbbkb ?? false;

            $finalTotalPajak = $includePpn ? $record->total_pajak ?? $finalTotalPenjualan * 0.11 : 0;
            $finalBiayaOperasional = $includeOperasional ? $record->biaya_operasional_kerja ?? 0 : 0;
            $finalBiayaPbbkb = $includePbbkb ? $record->biaya_pbbkb ?? 0 : 0;

            // Use database field if available, otherwise calculate
            $finalTotalInvoice =
                $record->total_invoice ??
                $finalTotalPenjualan + $finalTotalPajak + $finalBiayaOperasional + $finalBiayaPbbkb;

            // Fallback for demo purposes only if no data exists
            if ($finalTotalInvoice <= 0) {
                $finalTotalPenjualan = 100000000;
                $finalTotalPajak = $includePpn ? 11000000 : 0;
                $finalTotalInvoice =
                    $finalTotalPenjualan + $finalTotalPajak + $finalBiayaOperasional + $finalBiayaPbbkb;
            }
        @endphp

        <!-- Customer Details -->
        <div class="customer-details">
            <div class="customer-left">
                <div class="detail-row">
                    <span class="detail-label">Nama Pelanggan</span>
                    <span class="detail-colon">:</span>
                    <span
                        class="detail-value">{{ $record->nama_pelanggan ?? ($record->transaksiPenjualan?->pelanggan?->nama ?? '-') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Alamat Pelanggan</span>
                    <span class="detail-colon">:</span>
                    <span
                        class="detail-value">{{ $record->alamat_pelanggan ?? ($record->transaksiPenjualan?->pelanggan?->alamat ?? '-') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">NPWP Pelanggan</span>
                    <span class="detail-colon">:</span>
                    <span
                        class="detail-value">{{ $record->npwp_pelanggan ?? ($record->transaksiPenjualan?->pelanggan?->npwp ?? '-') }}</span>
                </div>
            </div>

            <div class="customer-right">
                <div class="detail-row">
                    <span class="detail-label">No Surat Pengantar</span>
                    <span class="detail-colon">:</span>
                    <span class="detail-value">{{ $record->deliveryOrder?->kode ?? '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Tanggal Invoice</span>
                    <span class="detail-colon">:</span>
                    <span
                        class="detail-value">{{ $record->tanggal_invoice ? $record->tanggal_invoice->format('d/m/Y') : '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Tanggal Jatuh Tempo</span>
                    <span class="detail-colon">:</span>
                    <span
                        class="detail-value">{{ $record->tanggal_jatuh_tempo ? $record->tanggal_jatuh_tempo->format('d/m/Y') : '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">No PO</span>
                    <span class="detail-colon">:</span>
                    <span class="detail-value">{{ $record->transaksiPenjualan?->nomor_po ?? '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Tanggal PO</span>
                    <span class="detail-colon">:</span>
                    <span
                        class="detail-value">{{ $record->transaksiPenjualan?->tanggal ? $record->transaksiPenjualan->tanggal->format('d/m/Y') : '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">No Tanda Bukti</span>
                    <span class="detail-colon">:</span>
                    <span class="detail-value">{{ $record->deliveryOrder?->no_segel ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 35%;">Perincian</th>
                    <th style="width: 15%;">Harga Satuan</th>
                    <th style="width: 15%;">Volume</th>
                    <th style="width: 15%;">PPN</th>
                    <th style="width: 15%;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $itemNumber = 1;
                    $details = $record->transaksiPenjualan?->penjualanDetails ?? collect();
                    $hasDetails = $details->isNotEmpty();
                @endphp

                @if ($hasDetails)
                    @foreach ($details as $detail)
                        <tr>
                            <td>{{ $itemNumber++ }}</td>
                            <td class="text-left">
                                {{ $detail->item?->nama ?? ($detail->item?->name ?? 'Item tidak ditemukan') }}<br>
                                <small
                                    style="color: #6b7280;">{{ $detail->item?->deskripsi ?? ($detail->item?->description ?? '') }}</small>
                            </td>
                            <td class="text-right">Rp {{ number_format($detail->harga_jual ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($detail->volume_item ?? 0, 2, ',', '.') }}
                                {{ $detail->item?->satuan?->nama ?? ($detail->item?->satuanDasar?->nama ?? 'Unit') }}
                            </td>
                            <td class="text-right">
                                @if ($includePpn)
                                    Rp
                                    {{ number_format(($detail->harga_jual ?? 0) * ($detail->volume_item ?? 0) * 0.11, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-right">Rp
                                {{ number_format(($detail->harga_jual ?? 0) * ($detail->volume_item ?? 0) * ($includePpn ? 1.11 : 1), 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                @else
                    @php
                        $totalPenjualan = $finalTotalPenjualan;
                        $totalPajak = $finalTotalPajak;
                        $itemNumber = 1;
                    @endphp

                    <!-- Main Service Item -->
                    <tr>
                        <td>{{ $itemNumber++ }}.</td>
                        <td class="text-left">
                            @if ($record->biaya_ongkos_angkut && $record->biaya_ongkos_angkut > 0)
                                Biaya Ongkos Angkut BBM<br>
                                {{ $record->nama_pelanggan ?? ($record->transaksiPenjualan?->pelanggan?->nama ?? 'Pelanggan') }}
                            @else
                                Layanan Pengiriman BBM<br>
                                {{ $record->nama_pelanggan ?? ($record->transaksiPenjualan?->pelanggan?->nama ?? 'Pelanggan') }}<br>
                                @if ($record->deliveryOrder?->volume_do)
                                    Volume: {{ number_format($record->deliveryOrder->volume_do, 0, ',', '.') }} Liter
                                @endif
                            @endif
                        </td>
                        <td class="text-right">
                            @if ($record->biaya_ongkos_angkut && $record->biaya_ongkos_angkut > 0)
                                Rp {{ number_format($record->biaya_ongkos_angkut, 0, ',', '.') }}
                            @else
                                Rp {{ number_format($totalPenjualan > 0 ? $totalPenjualan : 10000, 0, ',', '.') }}
                            @endif
                        </td>
                        <td class="text-right">
                            @if ($record->biaya_ongkos_angkut && $record->biaya_ongkos_angkut > 0)
                                1 Layanan
                            @else
                                {{ $record->deliveryOrder?->volume_do ? number_format($record->deliveryOrder->volume_do, 0, ',', '.') . ' Liter' : '1 Layanan' }}
                            @endif
                        </td>
                        <td class="text-right">
                            @if ($includePpn && $totalPajak > 0)
                                Rp {{ number_format($totalPajak, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</td>
                    </tr>

                    <!-- Operational Costs (if enabled and has value) -->
                    @if ($includeOperasional && $record->biaya_operasional_kerja)
                        <tr>
                            <td>{{ $itemNumber++ }}.</td>
                            <td class="text-left">Operasional Kerja<br>+ Polongan<br>+ Polongan</td>
                            <td class="text-right">968<br>968</td>
                            <td class="text-right">5000 liter<br>5000 liter</td>
                            <td class="text-right">-</td>
                            <td class="text-right">Rp.
                                {{ number_format($record->biaya_operasional_kerja, 0, ',', '.') }}</td>
                        </tr>
                    @endif

                    <!-- PPN (if enabled) -->
                    @if ($includePpn && $totalPajak > 0)
                        <tr>
                            <td>{{ $itemNumber++ }}.</td>
                            <td class="text-left">PPN (11%)</td>
                            <td class="text-right"></td>
                            <td class="text-right"></td>
                            <td class="text-right"></td>
                            <td class="text-right">Rp. {{ number_format($totalPajak, 0, ',', '.') }}</td>
                        </tr>
                    @endif

                    <!-- PBBKB (if enabled and has value) -->
                    @if ($includePbbkb && $record->biaya_pbbkb)
                        <tr>
                            <td>{{ $itemNumber++ }}.</td>
                            <td class="text-left">PPBKB BBM Solar<br>Industri
                                Pertamina<br>{{ $record->biaya_pbbkb ? number_format($record->biaya_pbbkb / 10000, 0, ',', '.') : '0' }}
                                x 10000
                                Liter</td>
                            <td class="text-right"></td>
                            <td class="text-right"></td>
                            <td class="text-right"></td>
                            <td class="text-right">Rp. {{ number_format($record->biaya_pbbkb, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                @endif

                <!-- Totals within table -->

                <tr class="totals-row">
                    <td colspan="5" style="text-align: right; font-weight: bold;">Total Penjualan</td>
                    <td class="text-right" style="font-weight: bold;">Rp.
                        {{ number_format($finalTotalPenjualan, 0, ',', '.') }}</td>
                </tr>

                @if ($includePpn && $finalTotalPajak > 0)
                    <tr class="totals-row">
                        <td colspan="5" style="text-align: right; font-weight: bold;">Total Pajak (PPN 11%)</td>
                        <td class="text-right" style="font-weight: bold;">Rp.
                            {{ number_format($finalTotalPajak, 0, ',', '.') }}</td>
                    </tr>
                @endif

                @if ($includeOperasional && $finalBiayaOperasional > 0)
                    <tr class="totals-row">
                        <td colspan="5" style="text-align: right; font-weight: bold;">Total Biaya Operasional</td>
                        <td class="text-right" style="font-weight: bold;">Rp.
                            {{ number_format($finalBiayaOperasional, 0, ',', '.') }}</td>
                    </tr>
                @endif

                @if ($includePbbkb && $finalBiayaPbbkb > 0)
                    <tr class="totals-row">
                        <td colspan="5" style="text-align: right; font-weight: bold;">Total PBBKB</td>
                        <td class="text-right" style="font-weight: bold;">Rp.
                            {{ number_format($finalBiayaPbbkb, 0, ',', '.') }}</td>
                    </tr>
                @endif

                <tr class="totals-row">
                    <td colspan="5" style="text-align: right; font-weight: bold;">Total Invoice</td>
                    <td class="text-right" style="font-weight: bold;">Rp.
                        {{ number_format($finalTotalInvoice, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Terbilang Section -->
        <div class="terbilang-section">
            <strong>Terbilang :</strong>
            "{{ ucwords(\App\Helpers\NumberToWords::convert($finalTotalInvoice > 0 ? $finalTotalInvoice : 100000000)) }}
            rupiah"
        </div>

        <!-- Payment Notes -->
        <div class="payment-notes">
            1. Payment transfer to account<br>
            2. After Payment please Call or Email transfer from to 0761-22369 or office@lintasriauprima.com
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-location">Pekanbaru,
                    {{ $record->tanggal_invoice ? $record->tanggal_invoice->format('d F Y') : now()->format('d F Y') }}
                </div>
                <div class="signature-space"></div>
                <div class="signature-name">Agustiawan Syahputra</div>
                <div class="signature-title">Direktur Utama</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-left">
                <div class="footer-logo">JSI</div>
                <div class="footer-logo">JSI</div>
                <div class="footer-company">
                    <strong>PT. LINTAS RIAU PRIMA</strong><br>
                    Jl. Mesjid Al Furqon No. 26<br>
                    Pekanbaru, Riau. 28144
                </div>
            </div>
            <div class="footer-contact">
                📞 0761-22369<br>
                ✉ office@lintasriauprima.com<br>
                🌐 www.lintasriauprima.com
            </div>
        </div>
    </div>
</body>

</html>
