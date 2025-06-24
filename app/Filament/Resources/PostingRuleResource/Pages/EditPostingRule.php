<?php

namespace App\Filament\Resources\PostingRuleResource\Pages;

use App\Filament\Resources\PostingRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPostingRule extends EditRecord
{
    protected static string $resource = PostingRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
