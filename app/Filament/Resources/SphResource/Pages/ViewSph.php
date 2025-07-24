<?php

namespace App\Filament\Resources\SphResource\Pages;

use App\Filament\Resources\SphResource;
use App\Models\Sph;
use App\Services\SphService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\View;

// --- ADDED: Import all necessary components ---
use Filament\Forms\Get;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Placeholder;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;

// PDF generation imports
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ViewSph extends ViewRecord
{
    protected static string $resource = SphResource::class;

    /**
     * Defines the layout for displaying the record's information.
     */
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Main column with primary details
                Group::make()
                    ->schema([
                        Section::make('Informasi SPH & Pelanggan')
                            ->schema([
                                TextEntry::make('sph_number')->label('Nomor SPH'),
                                TextEntry::make('customer.nama')->label('Pelanggan'),
                                TextEntry::make('opsional_pic')->label('Contact Person (U.p.)'),
                                TextEntry::make('sph_date')->label('Tanggal SPH')->date('d F Y'),
                                TextEntry::make('valid_until_date')->label('Berlaku Hingga')->date('d F Y'),
                                TextEntry::make('total_amount')
                                    ->label('Total Penawaran')
                                    ->money('IDR')
                                    ->weight('bold')
                                    ->color('primary'),
                            ])
                            ->columns(2)
                            ->collapsible(),

                        Section::make('Detail Item Penawaran')
                            ->schema([
                                RepeatableEntry::make('details')
                                    ->label('') // Hide the main repeater label
                                    ->schema([
                                        // Each item is now in its own card-like grid
                                        Grid::make(4)
                                            ->schema([
                                                TextEntry::make('item.name')
                                                    ->label('Item/Produk')
                                                    ->columnSpan(3),
                                                TextEntry::make('quantity')
                                                    ->label('Kuantitas')
                                                    ->numeric(2),
                                                TextEntry::make('description')
                                                    ->label('Deskripsi')
                                                    ->columnSpanFull(),
                                                // Grouping price components
                                                TextEntry::make('harga_dasar')->label('Harga Dasar')->money('IDR'),
                                                TextEntry::make('ppn')->label('PPN')->money('IDR'),
                                                TextEntry::make('oat')->label('OAT')->money('IDR'),
                                                TextEntry::make('price')->label('Harga Jual')->money('IDR')->weight('bold'),
                                            ])
                                    ]),
                            ])
                            ->collapsible(),

                    ])->columnSpan(2),

                // Side column with metadata
                Group::make()
                    ->schema([
                        Section::make('Status')
                            ->schema([
                                TextEntry::make('status')
                                    ->label('Status Saat Ini')
                                    ->badge()
                                    ->color(fn(Sph $record) => $record->status_color)
                                    ->formatStateUsing(fn(Sph $record) => $record->status_label),
                                TextEntry::make('createdBy.name')
                                    ->label('Dibuat Oleh'),
                                TextEntry::make('created_at')
                                    ->label('Tanggal Dibuat')
                                    ->dateTime('d M Y H:i'),
                            ])
                            ->collapsible(),

                        Section::make('Riwayat Approval')
                            ->schema([
                                RepeatableEntry::make('approvals')
                                    ->label('')
                                    ->schema([
                                        TextEntry::make('user.name')
                                            ->label('Oleh')
                                            ->weight('bold'),
                                        TextEntry::make('status')
                                            ->label('Tindakan')
                                            ->badge()
                                            ->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state)))
                                            ->color(fn($state) => match ($state) {
                                                'approved' => 'success',
                                                'rejected' => 'danger',
                                                'needs_revision' => 'warning',
                                                default => 'gray'
                                            }),
                                        TextEntry::make('note')
                                            ->label('Catatan')
                                            ->placeholder('Tidak ada catatan.'),
                                        TextEntry::make('created_at')
                                            ->label('Waktu')
                                            ->since(),
                                    ])->columns(2)
                            ])
                            ->collapsible()
                            ->visible(fn(Sph $record) => $record->approvals->isNotEmpty()),

                    ])->columnSpan(1),
            ])->columns(3);
    }


    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Preview SPH')
                ->color('gray')
                ->icon('heroicon-o-eye')
                ->action(null)
                ->modalContent(
                    fn(Sph $record): \Illuminate\View\View =>
                    View::make('sph.sph-preview', ['record' => $record])
                )
                ->modalHeading("Preview: {$this->record->sph_number}")
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup')
                ->slideOver()
                ->modalWidth('4xl')
                ->extraModalFooterActions([
                    Actions\Action::make('download_from_modal')
                        ->label('Download PDF')
                        ->color('success')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (Sph $record) {
                            return $this->downloadPdf($record);
                        })
                ]),

            Actions\Action::make('download_pdf')
                ->label('Download PDF')
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function (Sph $record) {
                    return $this->downloadPdf($record);
                }),

            Actions\EditAction::make()
                ->visible(fn(Sph $record): bool => $record->isEditable())
        ];
    }

    protected function downloadPdf(Sph $record)
    {
        try {
            // Load the record with all necessary relationships
            $sph = Sph::with([
                'customer',
                'details.item',
                'createdBy'
            ])->find($record->id);

            // Generate dynamic filename
            $filename = 'SPH_' . $sph->sph_number . '_' . now()->format('Ymd_His') . '.pdf';

            // Load the PDF view with the record data
            $pdf = Pdf::loadView('sph.sph-pdf', ['record' => $sph])
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
            Log::error('Failed to generate SPH PDF: ' . $e->getMessage());
            Log::error('SPH PDF Error Stack Trace: ' . $e->getTraceAsString());

            // Show notification to user
            \Filament\Notifications\Notification::make()
                ->title('Error generating PDF')
                ->body('Failed to generate PDF. Please try again or contact administrator.')
                ->danger()
                ->send();

            return;
        }
    }
}
