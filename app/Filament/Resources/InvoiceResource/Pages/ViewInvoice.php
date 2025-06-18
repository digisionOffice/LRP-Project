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
use App\Models\TransaksiPenjualan;



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
                            ->label('Nomor DO'),
                        TextEntry::make('transaksiPenjualan.kode')
                            ->label('Nomor SO'),
                        TextEntry::make('transaksiPenjualan.pelanggan.nama')
                            ->label('Pelanggan'),
                        TextEntry::make('tanggal_invoice')
                            ->label('Tanggal Invoice')
                            ->date(),
                        TextEntry::make('tanggal_jatuh_tempo')
                            ->label('Tanggal Jatuh Tempo')
                            ->date(),
                        TextEntry::make('total_invoice')
                            ->label('Total Invoice')
                            ->money('IDR'),
                        TextEntry::make('sisa_tagihan')
                            ->label('Sisa Tagihan')
                            ->money('IDR'),
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
                    ])->columns(2)
                    ->collapsible(),
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
                            'transaksiPenjualan.pelanggan.alamatUtama',
                            'transaksiPenjualan.pelanggan.subdistrict.district.regency',
                            'transaksiPenjualan.penjualanDetails.item.satuanDasar',
                            'deliveryOrder',
                            'createdBy'
                        ])->find($this->record->id);

                        // Generate dynamic filename
                        $filename = 'Receipt_' . $invoice->nomor_invoice . '_' . now()->format('Ymd_His') . '.pdf';

                        // Get logo as base64
                        $logoPath = public_path('images/lrp.png');
                        $logoBase64 = '';

                        if (File::exists($logoPath)) {
                            $logoBase64 = base64_encode(File::get($logoPath));
                        }

                        // Load the PDF view with the record data
                        $pdf = Pdf::loadView('pdf.receipt', ['record' => $invoice, 'logoBase64' => $logoBase64])
                            ->setPaper('a4', 'portrait')
                            ->setOptions([
                                'isHtml5ParserEnabled' => true,
                                'isPhpEnabled' => true,
                                'defaultFont' => 'Arial'
                            ]);

                        // Stream the PDF as a download
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, $filename);
                    } catch (\Exception $e) {
                        // Log the error for debugging
                        Log::error('Failed to generate Receipt PDF: ' . $e->getMessage());
                        Log::error('Receipt PDF Error Stack Trace: ' . $e->getTraceAsString());

                        // Show notification to user
                        \Filament\Notifications\Notification::make()
                            ->title('Error generating PDF')
                            ->body('Failed to generate PDF. Please try again or contact administrator.')
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
                            'transaksiPenjualan.pelanggan.alamatUtama',
                            'transaksiPenjualan.pelanggan.subdistrict.district.regency',
                            'transaksiPenjualan.penjualanDetails.item.satuanDasar',
                            'deliveryOrder',
                            'createdBy'
                        ])->find($this->record->id);

                        // Generate dynamic filename
                        $filename = 'Invoice_' . $invoice->nomor_invoice . '_' . now()->format('Ymd_His') . '.pdf';

                        // Load the PDF view with the record data
                        $pdf = Pdf::loadView('pdf.invoice', ['record' => $invoice])
                            ->setPaper('a4', 'portrait')
                            ->setOptions([
                                'isHtml5ParserEnabled' => true,
                                'isPhpEnabled' => true,
                                'defaultFont' => 'Arial'
                            ]);

                        // Stream the PDF as a download
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, $filename);
                    } catch (\Exception $e) {
                        // Log the error for debugging
                        Log::error('Failed to generate Invoice PDF: ' . $e->getMessage());
                        Log::error('Invoice PDF Error Stack Trace: ' . $e->getTraceAsString());

                        // Show notification to user
                        \Filament\Notifications\Notification::make()
                            ->title('Error generating PDF')
                            ->body('Failed to generate PDF. Please try again or contact administrator.')
                            ->danger()
                            ->send();

                        return;
                    }
                }),
        ];
    }
}
