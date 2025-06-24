<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Models\Invoice;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\Facades\File;



class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;


    //  infolis untuk menampilkan informasi invoice
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Invoice')
                    ->schema([
                        TextEntry::make('nomor_invoice')
                            ->icon('heroicon-o-document-text')
                            ->color('primary')
                            ->weight('bold')
                            ->label('Nomor Invoice'),
                        TextEntry::make('deliveryOrder.kode')
                            ->label('Nomor DO')
                            ->url(fn($record) => $record->deliveryOrderUrl)
                            ->color('primary'),
                        TextEntry::make('transaksiPenjualan.kode')
                            ->label('Nomor SO')
                            ->url(fn($record) => $record->transaksiPenjualan ? route('filament.admin.resources.transaksi-penjualans.view', ['record' => $record->transaksiPenjualan]) : null)
                            ->color('primary'),
                        TextEntry::make('tanggal_invoice')
                            ->label('Tanggal Invoice')
                            ->date('d/m/Y'),
                        TextEntry::make('tanggal_jatuh_tempo')
                            ->label('Tanggal Jatuh Tempo')
                            ->date('d/m/Y')
                            ->color(fn($record) => $record->tanggal_jatuh_tempo && $record->tanggal_jatuh_tempo->isPast() && $record->status !== 'paid' ? 'danger' : 'gray'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'draft' => 'secondary',
                                'sent' => 'warning',
                                'paid' => 'success',
                                'overdue' => 'danger',
                                'cancelled' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'draft' => 'Draft',
                                'sent' => 'Terkirim',
                                'paid' => 'Lunas',
                                'overdue' => 'Jatuh Tempo',
                                'cancelled' => 'Dibatalkan',
                                default => $state,
                            }),
                    ])->columns(3),

                Section::make('Informasi Pelanggan')
                    ->schema([
                        TextEntry::make('nama_pelanggan')
                            ->label('Nama Pelanggan')
                            ->weight('bold'),
                        TextEntry::make('alamat_pelanggan')
                            ->label('Alamat Pelanggan')
                            ->columnSpanFull(),
                        TextEntry::make('npwp_pelanggan')
                            ->label('NPWP Pelanggan'),
                        TextEntry::make('transaksiPenjualan.nomor_po')
                            ->label('Nomor PO'),
                        TextEntry::make('transaksiPenjualan.tanggal')
                            ->label('Tanggal PO')
                            ->date('d/m/Y'),
                    ])->columns(3),

                Section::make('Rincian Keuangan')
                    ->schema([
                        TextEntry::make('subtotal')
                            ->label('Subtotal')
                            ->money('IDR')
                            ->weight('bold'),
                        TextEntry::make('total_pajak')
                            ->label('Total Pajak (PPN 11%)')
                            ->money('IDR')
                            ->visible(fn($record) => $record->include_ppn),
                        TextEntry::make('biaya_ongkos_angkut')
                            ->label('Biaya Ongkos Angkut')
                            ->money('IDR')
                            ->visible(fn($record) => $record->biaya_ongkos_angkut > 0),
                        TextEntry::make('biaya_operasional_kerja')
                            ->label('Biaya Operasional Kerja')
                            ->money('IDR')
                            ->visible(fn($record) => $record->include_operasional_kerja && $record->biaya_operasional_kerja > 0),
                        TextEntry::make('biaya_pbbkb')
                            ->label('Biaya PBBKB')
                            ->money('IDR')
                            ->visible(fn($record) => $record->include_pbbkb && $record->biaya_pbbkb > 0),
                        TextEntry::make('total_invoice')
                            ->label('Total Invoice')
                            ->money('IDR')
                            ->weight('bold')
                            ->color('primary')
                            ->size('lg'),
                    ])->columns(3),

                Section::make('Status Pembayaran')
                    ->schema([
                        TextEntry::make('total_terbayar')
                            ->label('Total Terbayar')
                            ->money('IDR')
                            ->color('success'),
                        TextEntry::make('sisa_tagihan')
                            ->label('Sisa Tagihan')
                            ->money('IDR')
                            ->color(fn($record) => $record->sisa_tagihan > 0 ? 'warning' : 'success'),
                        TextEntry::make('tanggal_bayar')
                            ->label('Tanggal Bayar')
                            ->date('d/m/Y')
                            ->visible(fn($record) => $record->tanggal_bayar),
                        TextEntry::make('metode_bayar')
                            ->label('Metode Bayar')
                            ->visible(fn($record) => $record->metode_bayar),
                        TextEntry::make('referensi_bayar')
                            ->label('Referensi Bayar')
                            ->visible(fn($record) => $record->referensi_bayar),
                    ])->columns(3),

                Section::make('Catatan')
                    ->schema([
                        TextEntry::make('catatan')
                            ->label('Catatan')
                            ->columnSpanFull()
                            ->visible(fn($record) => $record->catatan),
                    ])
                    ->visible(fn($record) => $record->catatan),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            //
            Actions\Action::make('view_so')
                ->label('Lihat SO')
                ->icon('heroicon-o-document-text')
                ->url(fn($record) => $record->transaksiPenjualan ? route('filament.admin.resources.transaksi-penjualans.view', ['record' => $record->transaksiPenjualan]) : null)
                ->visible(fn($record) => $record->transaksiPenjualan !== null)
                ->openUrlInNewTab(false),
            // lihat do
            // lihat do kalau ada
            Actions\Action::make('view_do')
                ->label('Lihat DO')
                ->icon('heroicon-o-document-text')
                ->color('primary')
                ->url(fn($record): string => $record->deliveryOrderUrl)
                ->visible(fn($record): bool => $record->deliveryOrderUrl !== null)
                ->openUrlInNewTab(false),

            // cetak receipt
            Actions\Action::make('print_receipt')
                ->label('Cetak Receipt')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->visible(fn(): bool => Auth::user()?->can('view', $this->record) ?? false)
                ->action(function () {
                    try {
                        // Load the invoice with all necessary relationships
                        $invoice = Invoice::with([
                            'transaksiPenjualan.pelanggan',
                            'transaksiPenjualan.penjualanDetails.item.satuan',
                            'deliveryOrder.transaksi',
                            'createdBy',
                            'receipts',
                            'taxInvoice'
                        ])->find($this->record->id);

                        if (!$invoice) {
                            throw new \Exception('Invoice not found');
                        }

                        // Generate dynamic filename
                        $filename = 'Receipt_' . str_replace(['/', '\\', ' '], '_', $invoice->nomor_invoice) . '_' . now()->format('Ymd_His') . '.pdf';

                        // Get logo as base64
                        $logoPath = public_path('images/lrp.png');
                        $logoBase64 = '';

                        if (File::exists($logoPath)) {
                            $logoBase64 = base64_encode(File::get($logoPath));
                        }

                        // Load the PDF view with the record data
                        $pdf = Pdf::loadView('pdf.receipt', [
                            'record' => $invoice,
                            'logoBase64' => $logoBase64
                        ])
                            ->setPaper('a4', 'portrait')
                            ->setOptions([
                                'isHtml5ParserEnabled' => true,
                                'isPhpEnabled' => true,
                                'defaultFont' => 'Arial',
                                'dpi' => 150,
                                'defaultPaperSize' => 'a4',
                                'chroot' => public_path(),
                            ]);

                        // Stream the PDF as a download
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, $filename, [
                            'Content-Type' => 'application/pdf',
                            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
                        ]);
                    } catch (\Exception $e) {
                        // Log the error for debugging
                        Log::error('Failed to generate Receipt PDF: ' . $e->getMessage());
                        Log::error('Receipt PDF Error Stack Trace: ' . $e->getTraceAsString());
                        Log::error('Receipt PDF Error Context: ', [
                            'invoice_id' => $this->record->id,
                            'user_id' => Auth::id(),
                        ]);

                        // Show notification to user
                        \Filament\Notifications\Notification::make()
                            ->title('Error generating PDF')
                            ->body('Failed to generate PDF: ' . $e->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }
                }),

            Actions\Action::make('print_pdf')
                ->label('Cetak PDF')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->visible(fn(): bool => Auth::user()?->can('view', $this->record) ?? false)
                ->action(function () {
                    try {
                        // Load the invoice with all necessary relationships
                        $invoice = Invoice::with([
                            'transaksiPenjualan.pelanggan',
                            'transaksiPenjualan.penjualanDetails.item.satuan',
                            'deliveryOrder.transaksi',
                            'createdBy',
                            'receipts',
                            'taxInvoice'
                        ])->find($this->record->id);

                        if (!$invoice) {
                            throw new \Exception('Invoice not found');
                        }

                        // Generate dynamic filename
                        $filename = 'Invoice_' . str_replace(['/', '\\', ' '], '_', $invoice->nomor_invoice) . '_' . now()->format('Ymd_His') . '.pdf';

                        // Get logo as base64
                        $logoPath = public_path('images/lrp.png');
                        $logoBase64 = '';

                        if (File::exists($logoPath)) {
                            $logoBase64 = base64_encode(File::get($logoPath));
                        }

                        // Load the PDF view with the record data
                        $pdf = Pdf::loadView('pdf.invoice', [
                            'record' => $invoice,
                            'logoBase64' => $logoBase64
                        ])
                            ->setPaper('a4', 'portrait')
                            ->setOptions([
                                'isHtml5ParserEnabled' => true,
                                'isPhpEnabled' => true,
                                'defaultFont' => 'Arial',
                                'dpi' => 150,
                                'defaultPaperSize' => 'a4',
                                'chroot' => public_path(),
                            ]);

                        // Stream the PDF as a download
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, $filename, [
                            'Content-Type' => 'application/pdf',
                            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
                        ]);
                    } catch (\Exception $e) {
                        // Log the error for debugging
                        Log::error('Failed to generate Invoice PDF: ' . $e->getMessage());
                        Log::error('Invoice PDF Error Stack Trace: ' . $e->getTraceAsString());
                        Log::error('Invoice PDF Error Context: ', [
                            'invoice_id' => $this->record->id,
                            'user_id' => Auth::id(),
                        ]);

                        // Show notification to user
                        \Filament\Notifications\Notification::make()
                            ->title('Error generating PDF')
                            ->body('Failed to generate PDF: ' . $e->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }
                }),
        ];
    }
}
