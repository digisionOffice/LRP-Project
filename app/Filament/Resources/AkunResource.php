<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AkunResource\Pages;
use App\Filament\Resources\AkunResource\RelationManagers;
use App\Models\Akun;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AkunResource extends Resource
{
    protected static ?string $model = Akun::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?string $navigationLabel = 'Chart of Accounts';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Account Information')
                    ->schema([
                        Forms\Components\TextInput::make('kode_akun')
                            ->label('Account Code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->placeholder('e.g., 10101'),

                        Forms\Components\TextInput::make('nama_akun')
                            ->label('Account Name')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('e.g., Kas'),

                        Forms\Components\Select::make('tipe_akun')
                            ->label('Account Type')
                            ->options([
                                'aktiva' => 'Aktiva',
                                'kewajiban' => 'Kewajiban',
                                'modal' => 'Modal',
                                'pendapatan' => 'Pendapatan',
                                'biaya' => 'Biaya',
                            ])
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_akun')
                    ->label('Account Code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('nama_akun')
                    ->label('Account Name')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('tipe_akun')
                    ->label('Account Type')
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
            'index' => Pages\ListAkuns::route('/'),
            'create' => Pages\CreateAkun::route('/create'),
            'edit' => Pages\EditAkun::route('/{record}/edit'),
        ];
    }
}
