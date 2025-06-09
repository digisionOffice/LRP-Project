<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengirimanDriverResource\Pages;
use App\Filament\Resources\PengirimanDriverResource\RelationManagers;
use App\Models\PengirimanDriver;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PengirimanDriverResource extends Resource
{
    protected static ?string $model = PengirimanDriver::class;

    protected static ?string $navigationGroup = 'Operasional';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id_do')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('totalisator_awal')
                    ->numeric(),
                Forms\Components\TextInput::make('totalisator_tiba')
                    ->numeric(),
                Forms\Components\DateTimePicker::make('waktu_mulai'),
                Forms\Components\DateTimePicker::make('waktu_tiba'),
                Forms\Components\Textarea::make('foto_pengiriman')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('totalisator_pool_return')
                    ->numeric(),
                Forms\Components\DateTimePicker::make('waktu_pool_arrival'),
                Forms\Components\TextInput::make('created_by')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id_do')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('totalisator_awal')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('totalisator_tiba')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('waktu_mulai')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('waktu_tiba')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('totalisator_pool_return')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('waktu_pool_arrival')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_by')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListPengirimanDrivers::route('/'),
            'create' => Pages\CreatePengirimanDriver::route('/create'),
            'view' => Pages\ViewPengirimanDriver::route('/{record}'),
            'edit' => Pages\EditPengirimanDriver::route('/{record}/edit'),
        ];
    }
}
