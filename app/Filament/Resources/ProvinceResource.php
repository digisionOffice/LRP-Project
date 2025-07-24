<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProvinceResource\Pages;
use App\Filament\Resources\ProvinceResource\RelationManagers;
use App\Models\Province;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProvinceResource extends Resource
{
    protected static ?string $model = Province::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?string $navigationLabel = 'Provinsi';

    protected static ?int $navigationSort = 8;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Provinsi')
                    ->description('Kelola informasi provinsi dan hierarki administratif')
                    ->icon('heroicon-o-map')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('id')
                                    ->label('ID Provinsi')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(2)
                                    ->placeholder('contoh: 14')
                                    ->helperText('Kode provinsi 2 digit')
                                    ->disabled(fn($context) => $context === 'edit'),

                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Provinsi')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('contoh: Riau')
                                    ->helperText('Nama lengkap provinsi'),
                            ]),

                        Forms\Components\Hidden::make('created_by')
                            ->default(fn() => \Illuminate\Support\Facades\Auth::id()),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID Provinsi')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->size('sm'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Provinsi')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap(),

                Tables\Columns\TextColumn::make('regencies_count')
                    ->label('Kabupaten/Kota')
                    ->counts('regencies')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('districts_count')
                    ->label('Kecamatan')
                    ->counts('districts')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subdistricts_count')
                    ->label('Kelurahan')
                    ->getStateUsing(function ($record) {
                        return $record->subdistricts()->count();
                    })
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->placeholder('Sistem')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('name')
            ->striped()
            ->paginated([10, 25, 50]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RegenciesRelationManager::class,
            RelationManagers\DistrictsRelationManager::class,
            RelationManagers\SubdistrictsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProvinces::route('/'),
            'create' => Pages\CreateProvince::route('/create'),
            'view' => Pages\ViewProvince::route('/{record}'),
            'edit' => Pages\EditProvince::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }
}
