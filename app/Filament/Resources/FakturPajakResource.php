<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FakturPajakResource\Pages;
use App\Filament\Resources\FakturPajakResource\RelationManagers;
use App\Models\FakturPajak;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FakturPajakResource extends Resource
{
    protected static ?string $model = FakturPajak::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Manajemen Keuangan';

    protected static ?string $navigationLabel = 'Faktur Pajak';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFakturPajaks::route('/'),
            'create' => Pages\CreateFakturPajak::route('/create'),
            'edit' => Pages\EditFakturPajak::route('/{record}/edit'),
        ];
    }
}
