<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi {{ $record->nomor_invoice }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }

        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 30px;
            background: white;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
        }

        .company-info {
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }

        .company-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 12px;
            text-align: center;
            line-height: 1.2;
        }

        .company-details {
            flex: 1;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 5px;
        }

        .company-tagline {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 2px;
        }

        .receipt-title {
            text-align: center;
            flex: 1;
        }

        .receipt-title h1 {
            font-size: 36px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 10px;
            letter-spacing: 3px;
        }

        .receipt-number {
            text-align: right;
            font-size: 14px;
            color: #374151;
        }

        .receipt-body {
            margin: 40px 0;
        }

        .receipt-row {
            display: flex;
            margin-bottom: 25px;
            align-items: flex-start;
        }

        .receipt-label {
            width: 180px;
            font-weight: normal;
            color: #374151;
            flex-shrink: 0;
        }

        .receipt-colon {
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }

        .receipt-value {
            flex: 1;
            border-bottom: 1px solid #333;
            min-height: 25px;
            padding-bottom: 5px;
            color: #1f2937;
            font-weight: 500;
        }

        .amount-row .receipt-value {
            font-weight: bold;
            font-size: 16px;
        }

        .terbilang-row .receipt-value {
            font-style: italic;
            color: #374151;
        }

        .payment-description {
            min-height: 60px;
            padding: 10px 0;
        }

        .signature-section {
            margin-top: 60px;
            display: flex;
            justify-content: flex-end;
        }

        .signature-box {
            text-align: center;
            width: 250px;
        }

        .signature-location {
            margin-bottom: 5px;
            font-size: 14px;
        }

        .signature-space {
            height: 80px;
            margin: 20px 0;
            position: relative;
        }

        .signature-name {
            font-weight: bold;
            border-bottom: 1px solid #333;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }

        .signature-title {
            font-size: 12px;
            color: #374151;
        }

        @media print {
            body {
                margin: 0;
                padding: 10px;
            }

            .receipt-container {
                border: 2px solid #333;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
    <div class="receipt-container">
        <!-- Header Section -->
        <div class="header">
            <div class="company-info">
                <div class="company-logo">
                    LINTAS<br>RIAU<br>PRIMA
                </div>
            </div>

            <div class="receipt-title">
                <h1>KWITANSI</h1>
            </div>

            <div class="receipt-number">
                No. Invoice : {{ $record->nomor_invoice }}
            </div>
        </div>

        <!-- Receipt Body -->
        <div class="receipt-body">
            <div class="receipt-row">
                <div class="receipt-label">Sudah Terima Dari</div>
                <div class="receipt-colon">:</div>
                <div class="receipt-value">
                    {{ $record->transaksiPenjualan->pelanggan->nama ?? ($record->nama_pelanggan ?? '-') }}</div>
            </div>

            <div class="receipt-row amount-row">
                <div class="receipt-label">Uang Sebanyak</div>
                <div class="receipt-colon">:</div>
                <div class="receipt-value">Rp
                    {{ number_format($record->nominal_bayar ?: $record->total_invoice, 0, ',', '.') }}</div>
            </div>

            <div class="receipt-row terbilang-row">
                <div class="receipt-label">Terbilang</div>
                <div class="receipt-colon">:</div>
                <div class="receipt-value">
                    "{{ ucwords(\App\Helpers\NumberToWords::convert($record->nominal_bayar ?: $record->total_invoice)) }}
                    rupiah"
                </div>
            </div>

            <div class="receipt-row">
                <div class="receipt-label">Untuk Pembayaran</div>
                <div class="receipt-colon">:</div>
                <div class="receipt-value payment-description">
                    @if ($record->deliveryOrder)
                        Biaya Ongkos Angkut BBM Biosolar ke
                        {{ $record->transaksiPenjualan->pelanggan->nama ?? $record->nama_pelanggan }}. Indah tanggal
                        {{ $record->deliveryOrder->tanggal_delivery ? $record->deliveryOrder->tanggal_delivery->format('d M Y') : $record->tanggal_invoice->format('d M Y') }}.
                    @else
                        Pembayaran Invoice {{ $record->nomor_invoice }} untuk layanan pengiriman BBM.
                    @endif
                </div>
            </div>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-location">Pekanbaru,
                    {{ $record->tanggal_bayar ? $record->tanggal_bayar->format('d-M-y') : now()->format('d-M-y') }}
                </div>
                <div class="signature-space">
                    <div class="company-logo" style="width: 60px; height: 60px; margin: 10px auto; font-size: 10px;">
                        LINTAS<br>RIAU<br>PRIMA
                    </div>
                </div>
                <div class="signature-name">Agustiawan Syahputra</div>
                <div class="signature-title">Direktur</div>
            </div>
        </div>
    </div>
</body>

</html>
