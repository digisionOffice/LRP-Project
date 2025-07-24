<?php

namespace App\Filament\Resources\SphResource\Pages;

use App\Filament\Resources\SphResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

// models
use Illuminate\Database\Eloquent\Model;

// services
use App\Services\SphService;

class EditSph extends EditRecord
{
    protected static string $resource = SphResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * This hook runs before the form is filled with the record's data.
     * We use it to manually load and format the 'details' relationship
     * into an array that the Repeater component can understand.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Eager load the details to be safe
        $this->record->load('details');

        // Map the relationship data into the correct array structure
        $data['details'] = $this->record->details->map(function ($detail) {
            return [
                'item_id'     => $detail->item_id,
                'description' => $detail->description,
                'quantity'    => $detail->quantity,
                'harga_dasar' => $detail->harga_dasar,
                'ppn'         => $detail->ppn,
                'oat'         => $detail->oat,
                'price'       => $detail->price,
                'subtotal'    => $detail->subtotal,
            ];
        })->toArray();
        
        return $data;
    }

    /**
     * Override the default update logic to use our custom service.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $service = resolve(SphService::class);
        return $service->updateSph($record, $data);
    }
}
