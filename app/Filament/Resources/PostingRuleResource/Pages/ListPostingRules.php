<?php

namespace App\Filament\Resources\PostingRuleResource\Pages;

use App\Filament\Resources\PostingRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPostingRules extends ListRecords
{
    protected static string $resource = PostingRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
