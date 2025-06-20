<?php

namespace App\Filament\Resources\UangJalanResource\Pages;

use App\Filament\Resources\UangJalanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUangJalan extends ViewRecord
{
    protected static string $resource = UangJalanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            // lihat do
            Actions\Action::make('view_do')
                ->label('Lihat DO')
                ->icon('heroicon-o-document-text')
                ->color('primary')
                ->url(fn($record): string => $record->deliveryOrderUrl)
                ->visible(fn($record): bool => $record->deliveryOrderUrl !== null)
                ->openUrlInNewTab(false),


        ];
    }


}
