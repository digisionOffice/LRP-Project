<?php

namespace App\Filament\Resources\TaxInvoiceResource\Pages;

use App\Filament\Resources\TaxInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaxInvoice extends EditRecord
{
    protected static string $resource = TaxInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
