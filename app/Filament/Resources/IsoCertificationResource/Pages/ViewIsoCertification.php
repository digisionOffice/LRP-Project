<?php

namespace App\Filament\Resources\IsoCertificationResource\Pages;

use App\Filament\Resources\IsoCertificationResource;
use App\Models\IsoCertification;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\IconEntry;
use Illuminate\Support\HtmlString;
use Filament\Infolists\Components\Actions\Action; // <-- ADDED

class ViewIsoCertification extends ViewRecord
{
    protected static string $resource = IsoCertificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    /**
     * Defines the layout for displaying the record's information.
     */
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Detail Sertifikasi ISO')
                    ->schema([
                        // --- UPDATED: The ImageEntry is now clickable and opens a modal ---
                        ImageEntry::make('logo_path')
                            ->label('Logo Sertifikasi')
                            ->disk('public')
                            ->height(150) // A smaller height in the infolist
                            ->columnSpanFull()
                            ->action(
                                Action::make('view_full_logo')
                                    ->label('Lihat Gambar Penuh') // Accessibility label
                                    ->modalContent(fn (IsoCertification $record): HtmlString => new HtmlString("<img src='{$record->logo_url}' alt='Logo' style='max-width: 100%; height: auto;' />"))
                                    ->modalSubmitAction(false) // Hide the submit button
                                    ->modalCancelAction(false) // Hide the cancel button
                                    ->modalWidth('3xl') // Set a comfortable modal width
                            ),
                        TextEntry::make('name')
                            ->label('Nama Sertifikat'),
                        TextEntry::make('certificate_number')
                            ->label('Nomor Sertifikat'),
                        TextEntry::make('active_year')
                            ->label('Tahun Aktif'),
                        TextEntry::make('end_year')
                            ->label('Tahun Berakhir'),
                        IconEntry::make('is_active')
                            ->label('Status Aktif')
                            ->boolean(),
                    ])->columns(2),
            ]);
    }
}
