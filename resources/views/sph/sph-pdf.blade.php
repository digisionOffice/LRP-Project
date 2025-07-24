<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPH - {{ $record->sph_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            margin-bottom: 30px;
        }

        .header table {
            width: 100%;
        }

        .header td {
            vertical-align: top;
        }

        .logo {
            height: 120px;
            max-width: 200px;
            object-fit: contain;
        }

        .partner-text {
            text-align: right;
        }

        .partner-text h2 {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
        }

        .partner-text p {
            font-size: 10px;
            margin: 5px 0 0 0;
        }

        .doc-info {
            margin-bottom: 30px;
        }

        .doc-info .date {
            text-align: right;
            margin-bottom: 15px;
        }

        .doc-info table {
            font-size: 12px;
        }

        .doc-info td {
            padding: 2px 0;
        }

        .recipient {
            margin-bottom: 30px;
            font-size: 12px;
        }

        .body-text {
            margin-bottom: 25px;
            font-size: 12px;
            line-height: 1.5;
        }

        .product-section {
            margin-bottom: 30px;
            font-size: 12px;
        }

        .product-section table {
            width: 100%;
        }

        .product-section td {
            padding: 2px 0;
            vertical-align: top;
        }

        .price-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .price-table th,
        .price-table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }

        .price-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .price-table .number {
            text-align: center;
        }

        .price-table .amount {
            text-align: right;
        }

        .price-table .total-row {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .terms {
            margin-bottom: 30px;
            font-size: 12px;
        }

        .terms h2 {
            font-size: 14px;
            margin-bottom: 10px;
        }

        .terms ol {
            padding-left: 0;
            list-style: none;
            margin: 0;
        }

        .terms li {
            margin-bottom: 8px;
            padding: 0;
            display: block;
        }

        .terms li span:first-child {
            margin-right: 8px;
            min-width: 15px;
            display: inline-block;
            vertical-align: top;
        }

        .signature {
            margin-top: 40px;
            text-align: right;
        }

        .signature-box {
            display: inline-block;
            text-align: center;
        }

        .signature-space {
            height: 60px;
            margin: 10px 0;
        }

        .footer {
            margin-top: 60px;
            padding-top: 15px;
            border-top: 4px solid #1e40af;
            font-size: 10px;
        }

        .footer table {
            width: 100%;
        }

        .footer td {
            vertical-align: top;
        }

        .footer .center {
            text-align: center;
        }

        .footer .right {
            text-align: left;
        }

        .iso-logos {
            text-align: left;
        }

        .iso-logos img {
            height: 40px;
            margin-right: 10px;
        }
    </style>
</head>

<body>
    {{-- Header Section --}}
    <div class="header">
        <table>
            <tr>
                <td style="width: 33%;">
                    @php
                        $logoPath = public_path('images/lrp.png');
                        $logoExists = file_exists($logoPath);
                    @endphp

                    @if ($logoExists)
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}"
                            alt="PT. Lintas Riau Prima" class="logo">
                    @else
                        <div
                            style="height: 120px; width: 200px; border: 1px solid #ccc; text-align: center; padding-top: 50px; font-size: 12px; color: #666;">
                            PT. LINTAS RIAU PRIMA
                        </div>
                    @endif
                </td>
                <td style="width: 34%;"></td>
                <td style="width: 33%;" class="partner-text">
                    <h2>TRUSTED & RELIABLE PARTNER</h2>
                    <p>Fuel Agent – Fuel Transportation – Bunker Service</p>
                </td>
            </tr>
        </table>
    </div>

    {{-- Document Info Section --}}
    <div class="doc-info">
        <div class="date">
            Pekanbaru, {{ $record->sph_date->format('d F Y') }}
        </div>

        <table>
            <tr>
                <td style="width: 80px;">No</td>
                <td style="width: 10px;">:</td>
                <td><strong>{{ $record->sph_number }}</strong></td>
            </tr>
            <tr>
                <td>Lampiran</td>
                <td>:</td>
                <td>-</td>
            </tr>
            <tr>
                <td style="vertical-align: top;">Perihal</td>
                <td style="vertical-align: top;">:</td>
                <td><strong>Penawaran Harga Pertalite Industri<br>Pertamina Patra Niaga</strong></td>
            </tr>
        </table>
    </div>

    {{-- Recipient Section --}}
    <div class="recipient">
        <p>Kepada Yth.</p>
        <p><strong>{{ $record->customer?->nama }}</strong></p>
        @if ($record->opsional_pic)
            <p>Up: {{ $record->opsional_pic }}</p>
        @elseif($record->customer?->pic_nama)
            <p>Up: {{ $record->customer->pic_nama }}</p>
        @endif
        <p>Di –</p>
        <p style="margin-left: 20px;">Tempat</p>
    </div>

    {{-- Body / Salutation --}}
    <div class="body-text">
        <p style="margin-bottom: 15px;">Salam hormat,</p>
        <p>
            Sehubungan dengan adanya informasi kebutuhan BBM Pertalite industri untuk {{ $record->customer?->nama }},
            maka bersama ini kami kirimkan surat penawaran harga untuk periode
            <strong>{{ $record->sph_date->format('d M Y') }}</strong> s/d
            <strong>{{ $record->valid_until_date->format('d M Y') }}</strong>.
        </p>
    </div>

    {{-- Product Offering Section --}}
    <div class="product-section">
        <p style="margin-bottom: 10px;"><strong>Produk BBM yang kami tawarkan yaitu :</strong></p>
        <div style="padding-left: 20px;">
            <table>
                <tr>
                    <td style="width: 20px;">1.</td>
                    <td style="width: 120px;"><strong>Nama Produk</strong></td>
                    <td>: Pertalite Industri.</td>
                </tr>
                <tr>
                    <td>2.</td>
                    <td><strong>Spesifikasi</strong></td>
                    <td>: Standar Dirjen Migas & International ASTM</td>
                </tr>
                <tr>
                    <td>3.</td>
                    <td><strong>Legalitas</strong></td>
                    <td>: Full Document/ Resmi dari PT. Pertamina Patra Niaga</td>
                </tr>
                <tr>
                    <td>4.</td>
                    <td><strong>Sumber</strong></td>
                    <td>: Pertamina Patra Niaga</td>
                </tr>
                <tr>
                    <td>5.</td>
                    <td><strong>TKDN</strong></td>
                    <td>: 99,93% berdasarkan laporan Kementrian Perindustrian RI</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Price Details Table --}}
    <div>
        <h2 style="font-size: 14px; margin-bottom: 10px;">Harga Penawaran yang kami berikan yaitu:</h2>
        <table class="price-table">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Rincian</th>
                    <th style="width: 120px;">Harga/Liter</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($record->details as $idx => $detail)
                    <tr>
                        <td class="number">{{ $idx + 1 }}</td>
                        <td>Dasar BBM</td>
                        <td class="amount">{{ number_format($detail->harga_dasar, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="number"></td>
                        <td>PPN BBM 11%</td>
                        <td class="amount">{{ number_format($detail->ppn, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="number"></td>
                        <td>Oat</td>
                        <td class="amount">{{ number_format($detail->oat, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="2"><strong>Total Penawaran</strong></td>
                        <td class="amount"><strong>{{ number_format($detail->price, 0, ',', '.') }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Terms and Conditions --}}
    <div class="terms">
        <h2>Syarat dan Ketentuan</h2>
        <div style="margin-bottom: 8px;">
            <span style="display: inline-block; margin-right: 8px; min-width: 15px; vertical-align: top;">1.</span>
            <span>Penawaran harga ini berlaku pada periode {{ $record->sph_date->format('d M Y') }} -
                {{ $record->valid_until_date->format('d M Y') }}.</span>
        </div>
        <div style="margin-bottom: 8px;">
            <span style="display: inline-block; margin-right: 8px; min-width: 15px; vertical-align: top;">2.</span>
            <span>Pembayaran tagihan CASH hari setelah Dokumen diterima melalui transfer ke Bank BNI<br>
                <strong style="margin-left: 20px;">No Rekening: 217736160 An. PT. Lintas Riau Prima</strong>
            </span>
        </div>
        <div style="margin-bottom: 8px;">
            <span style="display: inline-block; margin-right: 8px; min-width: 15px; vertical-align: top;">3.</span>
            <span>PO kami terima minimal 3 (Tiga) hari (via email atau WA) sebelum pengiriman.</span>
        </div>
        <div style="margin-bottom: 8px;">
            <span style="display: inline-block; margin-right: 8px; min-width: 15px; vertical-align: top;">4.</span>
            <span>Untuk kondisi mendesak/urgent dapat berkoordinasi langsung sebelum pukul 12.00 Wib. pada hari yang
                sama.</span>
        </div>
    </div>

    {{-- Signature Section --}}
    <div class="signature">
        <div class="signature-box">
            <p>Hormat kami</p>
            <p>PT Lintas Riau Prima</p>
            <div class="signature-space"></div>
            <p><strong><u>{{ $record->createdBy?->name ?? 'N/A' }}</u></strong></p>
            <p style="font-size: 10px;">{{ $record->createdBy?->position ?? 'Manager Pemasaran' }}</p>
        </div>
    </div>

    {{-- Footer Section --}}
    <div class="footer">
        <table>
            <tr>
                <td style="width: 33%;" class="iso-logos">
                    @php
                        $isoNamesToDisplay = ['ISO 9001:2015', 'ISO 45001:2018'];
                        $isoCertifications = \App\Models\IsoCertification::where('is_active', true)
                            ->whereIn('name', $isoNamesToDisplay)
                            ->get();
                    @endphp

                    @foreach ($isoCertifications as $cert)
                        @php
                            $logoPath = public_path('storage/' . $cert->logo_path);
                        @endphp
                        @if (file_exists($logoPath))
                            <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents($logoPath)) }}"
                                alt="{{ $cert->name }}" style="height: 40px; margin-right: 10px;">
                        @endif
                    @endforeach
                </td>
                <td style="width: 34%;" class="center">
                    <p><strong>PT. LINTAS RIAU PRIMA</strong></p>
                    <p>Jl. Mesjid Al Furqon No. 26</p>
                    <p>Pekanbaru, Riau. 28144</p>
                </td>
                <td style="width: 33%;" class="right">
                    <p>Tel: 0761-22369</p>
                    <p>Email: office@lintasriauprima.com</p>
                    <p>Web: www.lintasriauprima.com</p>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
