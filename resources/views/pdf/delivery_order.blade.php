<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Order - {{ $record->kode }}</title>
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
        }

        .header {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .header-content {
            display: table;
            width: 100%;
        }

        .logo-section {
            display: table-cell;
            width: 25%;
            vertical-align: top;
        }

        .company-info {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: center;
            padding: 0 10px;
        }

        .recipient-info {
            display: table-cell;
            width: 25%;
            vertical-align: top;
            text-align: right;
        }

        .logo-placeholder {
            width: 80px;
            height: 60px;
            border: 2px solid #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            color: #666;
            text-align: center;
        }

        .company-name {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .company-tagline {
            font-size: 10px;
            margin-bottom: 3px;
        }

        .company-contact {
            font-size: 9px;
            line-height: 1.2;
        }

        .recipient-box {
            border: 1px solid #000;
            padding: 8px;
            min-height: 60px;
        }

        .recipient-label {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 5px;
        }

        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
            text-transform: uppercase;
        }

        .do-info {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .do-number {
            display: table-cell;
            width: 50%;
        }

        .do-date {
            display: table-cell;
            width: 50%;
            text-align: right;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 8px 5px;
            text-align: center;
            vertical-align: middle;
        }

        .items-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 10px;
        }

        .items-table td {
            font-size: 10px;
        }

        .items-table .text-left {
            text-align: left;
        }

        .notes {
            margin-bottom: 20px;
        }

        .notes-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .notes-content {
            font-size: 10px;
            line-height: 1.4;
        }

        .signatures {
            display: table;
            width: 100%;
            margin-top: 30px;
        }

        .signature-section {
            display: table-cell;
            width: 25%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }

        .signature-title {
            font-size: 10px;
            margin-bottom: 5px;
        }

        .signature-space {
            height: 60px;
            border-bottom: 1px solid #000;
            margin-bottom: 5px;
        }

        .signature-name {
            font-size: 9px;
            font-weight: bold;
        }

        .signature-role {
            font-size: 8px;
            color: #666;
        }

        .page-break {
            page-break-after: always;
        }

        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
    </style>
</head>

<body>
    <!-- Header Section -->
    <div class="header">
        <div class="header-content">
            <div class="logo-section">
                <div class="logo-placeholder">
                    COMPANY<br>LOGO
                </div>
            </div>
            <div class="company-info">
                <div class="company-name">LINTAS RIAU PRIMA</div>
                <div class="company-tagline">TRUSTED & RELIABLE PARTNER</div>
                <div class="company-tagline">Fuel Agent - Fuel Transportation - Bunker Service</div>
                <div class="company-contact">
                    📞 0761-22369 ✉️ office@lintasriauprima.com<br>
                    🌐 www.lintasriauprima.com
                </div>
            </div>
            <div class="recipient-info">
                <div class="recipient-box">
                    <div class="recipient-label">Kepada Yth.</div>
                    <div style="font-weight: bold; font-size: 11px;">
                        {{ strtoupper($record->transaksi->pelanggan->nama ?? 'PT. ANUGERAH PRAMUDITA UTAMA') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Title -->
    <div class="title">
        BUKTI PENGIRIMAN BARANG / DELIVERY ORDER (DO)
    </div>

    <!-- DO Information -->
    <div class="do-info">
        <div class="do-number">
            <strong>Nomor DO : {{ $record->kode ?? 'N/A' }}</strong>
        </div>
        <div class="do-date">
            <strong>LRP-Form-Ops-04/Rev 03/
                {{ $record->transaksi && $record->transaksi->created_at ? $record->transaksi->created_at->format('d M Y') : now()->format('d M Y') }}</strong>
        </div>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 40%;">JENIS BARANG</th>
                <th style="width: 15%;">JUMLAH</th>
                <th style="width: 25%;">NO SEGEL</th>
                <th style="width: 15%;">PENERIMA/PIC</th>
                <th style="width: 15%;">TANDA TANGAN</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalVolume = 0;
                $itemNumber = 1;
            @endphp

            @if ($record->transaksi && $record->transaksi->penjualanDetails && $record->transaksi->penjualanDetails->count() > 0)
                @foreach ($record->transaksi->penjualanDetails as $detail)
                    @php
                        $totalVolume += $detail->volume_item;
                    @endphp
                    <tr>
                        <td>{{ $itemNumber++ }}.</td>
                        <td class="text-left">
                            {{ $detail->item->name ?? 'BBM INDUSTRI' }} (BioSolar - B35) - Duri - Riau
                        </td>
                        <td>{{ number_format($detail->volume_item, 0) }} {{ $detail->item->satuan->nama ?? 'ltr' }}</td>
                        <td>
                            @if ($record->no_segel)
                                {{ $record->no_segel }}
                            @else
                                002360,002361,002362,002363
                            @endif
                        </td>
                        <td>{{ $record->transaksi->pelanggan->pic_nama ?? '-' }}</td>
                        <td></td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td>1.</td>
                    <td class="text-left">No items found</td>
                    <td>0 ltr</td>
                    <td>
                        @if ($record->no_segel)
                            {{ $record->no_segel }}
                        @else
                            002360,002361,002362,002363
                        @endif
                    </td>
                    <td>{{ $record->transaksi->pelanggan->pic_nama ?? '-' }}</td>
                    <td></td>
                </tr>
            @endif

            <!-- Empty rows to fill the table -->
            @for ($i = $itemNumber; $i <= 5; $i++)
                <tr>
                    <td>{{ $i }}.</td>
                    <td class="text-left">-</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor

            <!-- Total Row -->
            <tr class="total-row">
                <td colspan="2" style="text-align: center;"><strong>TOTAL</strong></td>
                <td><strong>{{ number_format($totalVolume, 0) }} ltr</strong></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <!-- Notes Section -->
    <div class="notes">
        <div class="notes-title">Catatan :</div>
        <div class="notes-content">
            - Periksa kualitas dan kuantitas barang sebelum bongkar, setelah bingkar keluhan tidak dilayani<br>
            - Periksa seluruh segel atas dan bawah harus dalam kondisi baik sebelum dibongkar
        </div>
    </div>

    <!-- Signatures Section -->
    <div class="signatures">
        <div class="signature-section">
            <div class="signature-title">.............../............................. {{ now()->format('d') }}...
            </div>
            <div class="signature-title">Penerima,</div>
            <div class="signature-space"></div>
            <div class="signature-name">(...........................)</div>
            <div class="signature-role">Nama & Tanda Tangan<br>atau Cap Perusahaan</div>
        </div>

        <div class="signature-section">
            <div class="signature-title">Security (jika ada),</div>
            <div class="signature-space"></div>
            <div class="signature-name">(...........................)</div>
            <div class="signature-role">Nama & Tanda Tangan<br>atau Cap Perusahaan</div>
        </div>

        <div class="signature-section">
            <div class="signature-title">Pekanbaru, /........................... {{ now()->format('d') }}...</div>
            <div class="signature-title">Pengantar,</div>
            <div class="signature-space"></div>
            <div class="signature-name">( Zaiful Amri )</div>
            <div class="signature-role">BM 8524 JO<br>Nama & Tanda Tangan</div>
        </div>

        <div class="signature-section">
            <div class="signature-title">Pengirim,</div>
            <div class="signature-space"></div>
            <div class="signature-name">(...........................)</div>
            <div class="signature-role">Nama & Tanda Tangan<br>atau Cap Perusahaan</div>
        </div>
    </div>

    <!-- Additional Information -->
    <div style="margin-top: 30px; font-size: 10px;">
        <div style="display: table; width: 100%;">
            <div style="display: table-cell; width: 50%;">
                <strong>Informasi Kendaraan:</strong><br>
                Plat Nomor: {{ $record->kendaraan->no_pol_kendaraan ?? 'N/A' }}<br>
                Driver: {{ $record->user->name ?? 'N/A' }}
            </div>
            <div style="display: table-cell; width: 50%; text-align: right;">
                <strong>Pekanbaru, {{ now()->format('d/m/Y') }}</strong><br>
                Tanggal Pengiriman:
                {{ $record->tanggal_delivery ? $record->tanggal_delivery->format('d/m/Y H:i') : 'Belum ditentukan' }}
            </div>
        </div>
    </div>
</body>

</html>
