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

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Akuntansi';

    protected static ?string $navigationLabel = 'Chart of Accounts';

    protected static ?string $modelLabel = 'Akun';

    protected static ?string $pluralModelLabel = 'Chart of Accounts';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode_akun')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nama_akun')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('kategori_akun')
                    ->required()
                    ->options([
                        'Aset' => 'Aset',
                        'Kewajiban' => 'Kewajiban',
                        'Ekuitas' => 'Ekuitas',
                        'Pendapatan' => 'Pendapatan',
                        'Beban' => 'Beban',
                    ])
                    ->native(false),
                Forms\Components\Select::make('tipe_akun')
                    ->required()
                    ->options([
                        'Debit' => 'Debit',
                        'Kredit' => 'Kredit',
                    ])
                    ->native(false),
                Forms\Components\TextInput::make('saldo_awal')
                    ->numeric()
                    ->prefix('Rp')
                    ->step(0.01)
                    ->default(0),
                Forms\Components\Hidden::make('created_by')
                    ->default(auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_akun')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_akun')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kategori_akun')
                    ->badge()
                    ->colors([
                        'primary' => 'Aset',
                        'danger' => 'Kewajiban',
                        'success' => 'Ekuitas',
                        'warning' => 'Pendapatan',
                        'secondary' => 'Beban',
                    ]),
                Tables\Columns\TextColumn::make('tipe_akun')
                    ->badge()
                    ->colors([
                        'success' => 'Debit',
                        'danger' => 'Kredit',
                    ]),
                Tables\Columns\TextColumn::make('saldo_awal')
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
                Tables\Filters\SelectFilter::make('kategori_akun')
                    ->options([
                        'Aset' => 'Aset',
                        'Kewajiban' => 'Kewajiban',
                        'Ekuitas' => 'Ekuitas',
                        'Pendapatan' => 'Pendapatan',
                        'Beban' => 'Beban',
                    ]),
                Tables\Filters\SelectFilter::make('tipe_akun')
                    ->options([
                        'Debit' => 'Debit',
                        'Kredit' => 'Kredit',
                    ]),
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
