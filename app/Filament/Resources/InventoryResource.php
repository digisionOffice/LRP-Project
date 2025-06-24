<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages;
use App\Models\Inventory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Inventaris';

    protected static ?string $modelLabel = 'Inventaris';

    protected static ?string $pluralModelLabel = 'Inventaris';

    protected static ?string $navigationGroup = 'Akuntansi';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('item_id')
                    ->label('Item')
                    ->relationship('item', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('quantity')
                    ->label('Kuantitas')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('unit_cost')
                    ->label('Harga Pokok per Unit')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->step(0.01),
                Forms\Components\TextInput::make('total_value')
                    ->label('Total Nilai')
                    ->disabled()
                    ->dehydrated(false)
                    ->prefix('Rp'),
                Forms\Components\Hidden::make('created_by')
                    ->default(auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item.name')
                    ->label('Item')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('item.kode')
                    ->label('Kode Item')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Kuantitas')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_cost')
                    ->label('Harga Pokok/Unit')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_value')
                    ->label('Total Nilai')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListInventories::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'view' => Pages\ViewInventory::route('/{record}'),
            'edit' => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}
