<?php

namespace App\Filament\Resources\ExpenseRequestResource\Pages;

use App\Filament\Resources\ExpenseRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExpenseRequests extends ListRecords
{
    protected static string $resource = ExpenseRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
