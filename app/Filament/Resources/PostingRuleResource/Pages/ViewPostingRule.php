<?php

namespace App\Filament\Resources\PostingRuleResource\Pages;

use App\Filament\Resources\PostingRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPostingRule extends ViewRecord
{
    protected static string $resource = PostingRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
