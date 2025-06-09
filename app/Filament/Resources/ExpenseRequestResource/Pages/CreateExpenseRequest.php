<?php

namespace App\Filament\Resources\ExpenseRequestResource\Pages;

use App\Filament\Resources\ExpenseRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateExpenseRequest extends CreateRecord
{
    protected static string $resource = ExpenseRequestResource::class;
}
