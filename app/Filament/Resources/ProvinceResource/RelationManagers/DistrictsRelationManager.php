<?php

namespace App\Filament\Resources\ProvinceResource\RelationManagers;

use App\Models\District;
use App\Models\Regency;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DistrictsRelationManager extends RelationManager
{
    protected static string $relationship = 'districts';

    protected static ?string $title = 'Kecamatan';

    protected static ?string $modelLabel = 'Kecamatan';

    protected static ?string $pluralModelLabel = 'Kecamatan';

    protected static ?string $icon = 'heroicon-o-building-office';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kecamatan')
                    ->description('Tambah atau edit informasi kecamatan')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('id')
                                    ->label('ID Kecamatan')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(6)
                                    ->placeholder('contoh: 140101')
                                    ->helperText('Kode kecamatan 6 digit'),

                                Forms\Components\Select::make('regency_id')
                                    ->label('Kabupaten/Kota')
                                    ->required()
                                    ->options(function ($livewire) {
                                        return Regency::where('province_id', $livewire->ownerRecord->id)
                                            ->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Pilih kabupaten/kota'),

                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Kecamatan')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('contoh: Kampar Kiri')
                                    ->helperText('Nama kecamatan tanpa prefix tipe')
                                    ->columnSpanFull(),

                                Forms\Components\Hidden::make('created_by')
                                    ->default(fn() => \Illuminate\Support\Facades\Auth::id()),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->size('sm'),

                Tables\Columns\TextColumn::make('regency.name')
                    ->label('Kabupaten/Kota')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Kecamatan')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('subdistricts_count')
                    ->label('Kelurahan/Desa')
                    ->counts('subdistricts')
                    ->badge()
                    ->color('success')
                    ->sortable(),

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
                Tables\Filters\SelectFilter::make('regency_id')
                    ->label('Kabupaten/Kota')
                    ->options(function ($livewire) {
                        return Regency::where('province_id', $livewire->ownerRecord->id)
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Kecamatan')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Tambah Kecamatan Baru')
                    ->successNotificationTitle('Kecamatan berhasil ditambahkan'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Edit Kecamatan')
                    ->successNotificationTitle('Kecamatan berhasil diperbarui'),
                Tables\Actions\DeleteAction::make()
                    ->successNotificationTitle('Kecamatan berhasil dihapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]))
            ->defaultSort('regency.name')
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
