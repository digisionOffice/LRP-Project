<?php

namespace App\Filament\Resources\TaxInvoiceResource\Pages;

use App\Filament\Resources\TaxInvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTaxInvoice extends CreateRecord
{
    protected static string $resource = TaxInvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        
        return $data;
    }
}
