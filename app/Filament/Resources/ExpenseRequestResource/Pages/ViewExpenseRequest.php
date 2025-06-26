<?php

namespace App\Filament\Resources\ExpenseRequestResource\Pages;

// --- Models & Resources ---
use App\Filament\Resources\ExpenseRequestResource;
use App\Models\ExpenseRequest;

// --- Services ---
use App\Services\ExpenseRequestService;

// --- Core Filament ---
use Filament\Actions;
use Filament\Forms\Get;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

// --- Infolist Components (Direct Use) ---
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\RepeatableEntry;

// --- Form Components (Direct Use) ---
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Placeholder;

class ViewExpenseRequest extends ViewRecord
{
    protected static string $resource = ExpenseRequestResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Main column with primary details
                Group::make()
                    ->schema([
                        Section::make('Detail Permintaan')->schema([
                            TextEntry::make('title')->label('Judul Permintaan'),
                            Grid::make(2)->schema([
                                TextEntry::make('requested_amount')->label('Jumlah Diajukan')->money('IDR')->weight('bold'),
                                TextEntry::make('approved_amount')->label('Jumlah Disetujui')->money('IDR')->weight('bold')->color('success'),
                            ]),
                            TextEntry::make('description')->label('Deskripsi')->columnSpanFull(),
                        ])->columns(2),

                        Section::make('Riwayat Approval')
                            ->schema([
                                RepeatableEntry::make('approvals')
                                    ->label('')
                                    ->schema([
                                        TextEntry::make('user.name')->label('Ditinjau Oleh')->icon('heroicon-s-user'),
                                        TextEntry::make('status')->label('Tindakan')->badge()->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state)))->color(fn ($state) => match($state) { 'approved' => 'success', 'rejected' => 'danger', 'needs_revision' => 'warning', default => 'gray' }),
                                        TextEntry::make('note')->label('Catatan/Alasan'),
                                        TextEntry::make('created_at')->label('Waktu')->dateTime('d M Y, H:i'),
                                    ])->columns(4)
                            ])
                            ->collapsible()
                            ->visible(fn (ExpenseRequest $record) => $record->approvals->isNotEmpty()),
                        
                        // --- ADDED: Section to display supporting documents ---
                        Section::make('Dokumen Pendukung')
                            ->schema([
                                SpatieMediaLibraryImageEntry::make('supporting_documents')
                                    ->collection('supporting_documents')
                                    ->label('') // Hide the main label
                                    ->columns(3), // Display up to 3 documents per row
                            ])
                            ->collapsible()
                            ->visible(fn (ExpenseRequest $record) => $record->getMedia('supporting_documents')->isNotEmpty()),

                        Section::make('Informasi Pembayaran')
                            ->schema([
                                TextEntry::make('paid_at')
                                    ->label('Tanggal Dibayar')
                                    ->dateTime('d M Y H:i')
                                    ->visible(fn (ExpenseRequest $record): bool => $record->status === 'paid'),
                                TextEntry::make('payment_status_placeholder')
                                    ->label('Status Pembayaran')
                                    ->default('Belum dibayarkan')
                                    ->visible(fn (ExpenseRequest $record): bool => $record->status !== 'paid'),
                            ]),

                    ])->columnSpan(2),

                // Side column with metadata
                Group::make()
                    ->schema([
                        Section::make('Status')->schema([
                            TextEntry::make('request_number')->label('No. Permintaan')->icon('heroicon-s-document-text'),
                            TextEntry::make('status')->label('Status Saat Ini')->badge()->color(fn ($record) => $record->status_color)->formatStateUsing(fn ($record) => $record->status_label),
                            TextEntry::make('priority')->label('Prioritas')->badge()->color(fn ($record) => $record->priority_color)->formatStateUsing(fn ($record) => $record->priority_label),
                        ]),
                        Section::make('Informasi Penting')->schema([
                            TextEntry::make('requestedBy.name')->label('Diminta Oleh')->icon('heroicon-s-user-circle'),
                            TextEntry::make('requestedBy.divisi.nama')->label('Divisi')->icon('heroicon-s-building-office-2'),
                            TextEntry::make('requested_date')->label('Tanggal Diminta')->date('d M Y')->icon('heroicon-s-calendar-days'),
                            TextEntry::make('needed_by_date')->label('Dibutuhkan Pada')->date('d M Y')->icon('heroicon-s-calendar'),
                        ]),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    /**
     * Defines the header actions.
     */
    protected function getHeaderActions(): array
    {
        // The actions remain unchanged for this update.
        return [
            Actions\Action::make('mark_as_paid')
                ->label('Konfirmasi Pembayaran')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Pembayaran')
                ->modalDescription('Apakah Anda yakin permintaan biaya ini sudah dibayar? Jurnal akan otomatis di-posting.')
                ->modalSubmitActionLabel('Ya, Sudah Dibayar')
                ->action(function (ExpenseRequest $record, ExpenseRequestService $expenseRequestService) {
                    // $record->update(['status' => 'paid','paid_at' => now()]);
                    // $record->postJournalEntry();
                    $expenseRequestService->markAsPaid($record);
                    Notification::make()->title('Pembayaran telah dikonfirmasi.')->success()->send();
                })
                ->visible(fn(ExpenseRequest $record) => $record->status === 'approved'),

            Actions\Action::make('process_approval')
                ->label('Proses Approval')
                ->color('primary')
                ->icon('heroicon-o-check-badge')
                ->form([
                    Fieldset::make('Informasi Pemohon')->schema([
                        Placeholder::make('requester_name')->label('Nama Pemohon')->content(fn (ExpenseRequest $record): string => $record->requestedBy?->name ?? 'N/A'),
                        Placeholder::make('requester_division')->label('Divisi')->content(fn (ExpenseRequest $record): string => $record->requestedBy?->divisi?->nama ?? 'N/A'),
                    ])->columns(2),
                    
                    Radio::make('status')
                        ->label('Tindakan Approval')
                        ->options([
                            'approved' => 'Approved (Disetujui)',
                            'needs_revision' => 'Needs Revision (Butuh Revisi)',
                            'rejected' => 'Rejected (Ditolak Final)',
                        ])
                        ->required()
                        ->live(),
                    TextInput::make('approved_amount')->label('Jumlah Disetujui (Rp)')->numeric()->prefix('Rp')->required()->default(fn (ExpenseRequest $record) => $record->requested_amount)->visible(fn (Get $get): bool => $get('status') === 'approved'),
                    Textarea::make('note')->label('Catatan / Alasan')->rows(3)->required(fn (Get $get): bool => in_array($get('status'), ['rejected', 'needs_revision']))->visible(fn (Get $get): bool => in_array($get('status'), ['rejected', 'needs_revision'])),
                ])
                ->action(function (ExpenseRequest $record, array $data, ExpenseRequestService $expenseRequestService) {
                    try {
                        $approvedAmount = 0;
                        if ($data['status'] === 'approved') {
                            $approvedAmount = (float) ($data['approved_amount'] ?? 0);
                        }
                        
                        $expenseRequestService->processApproval(
                            $record,
                            auth()->user(),
                            $data['status'],
                            $data['note'] ?? null,
                            $approvedAmount
                        );

                        Notification::make()->title('Proses approval berhasil disimpan.')->success()->send();
                        
                    } catch (\Throwable $e) {
                        Notification::make()->title('Terjadi Kesalahan')->body($e->getMessage())->danger()->send();
                    }
                })
                ->visible(fn(ExpenseRequest $record) => $record->canBeApproved()),

            Actions\EditAction::make()
                ->visible(fn (ExpenseRequest $record): bool => 
                    !in_array($record->status, ['approved', 'rejected', 'paid'])
                ),
        ];
    }
}
