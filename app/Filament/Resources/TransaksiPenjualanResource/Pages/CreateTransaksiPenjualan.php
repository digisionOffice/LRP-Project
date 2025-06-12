<?php

namespace App\Filament\Resources\TransaksiPenjualanResource\Pages;

use App\Filament\Resources\TransaksiPenjualanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class CreateTransaksiPenjualan extends CreateRecord
{
    protected static string $resource = TransaksiPenjualanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set created_by for the main transaction
        $data['created_by'] = Auth::id();

        return $data;
    }

    protected function afterCreate(): void
    {
        // Validate that we have at least one detail item after creation
        $record = $this->getRecord();

        if ($record->penjualanDetails()->count() === 0) {
            // Delete the record if no details were created
            $record->delete();

            Notification::make()
                ->title('Error')
                ->body('Minimal harus ada satu item dalam transaksi penjualan.')
                ->danger()
                ->send();

            // Redirect back to create page
            $this->redirect($this->getResource()::getUrl('create'));
            return;
        }

        Notification::make()
            ->title('Berhasil')
            ->body('Transaksi penjualan berhasil dibuat dengan ' . $record->penjualanDetails()->count() . ' item.')
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
