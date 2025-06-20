<?php

namespace App\Filament\Resources\TaxInvoiceResource\Pages;

use App\Filament\Resources\TaxInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTaxInvoices extends ListRecords
{
    protected static string $resource = TaxInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
