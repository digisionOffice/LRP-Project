<?php

namespace App\Filament\Resources\ProvinceResource\RelationManagers;

use App\Models\Subdistrict;
use App\Models\District;
use App\Models\Regency;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;

class SubdistrictsRelationManager extends RelationManager
{
    protected static string $relationship = 'subdistricts';

    protected static ?string $title = 'Kelurahan/Desa';

    protected static ?string $modelLabel = 'Kelurahan/Desa';

    protected static ?string $pluralModelLabel = 'Kelurahan/Desa';

    protected static ?string $icon = 'heroicon-o-home';

    protected function getTableQuery(): Builder
    {
        return Subdistrict::query()
            ->join('districts', 'subdistricts.district_id', '=', 'districts.id')
            ->join('regencies', 'districts.regency_id', '=', 'regencies.id')
            ->where('regencies.province_id', $this->ownerRecord->id)
            ->select('subdistricts.*');
    }

    protected function canCreate(): bool
    {
        return true;
    }

    protected function canEdit(Model $record): bool
    {
        return true;
    }

    protected function canDelete(Model $record): bool
    {
        return true;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kelurahan/Desa')
                    ->description('Tambah atau edit informasi kelurahan/desa')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('id')
                                    ->label('ID Kelurahan/Desa')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(10)
                                    ->placeholder('contoh: 1401010001')
                                    ->helperText('Kode kelurahan/desa 10 digit'),

                                Forms\Components\Select::make('district_id')
                                    ->label('Kecamatan')
                                    ->required()
                                    ->options(function ($livewire) {
                                        return District::whereHas('regency', function ($query) use ($livewire) {
                                            $query->where('province_id', $livewire->ownerRecord->id);
                                        })
                                            ->with('regency')
                                            ->get()
                                            ->mapWithKeys(function ($district) {
                                                return [$district->id => $district->regency->name . ' - ' . $district->name];
                                            });
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Pilih kecamatan'),

                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Kelurahan/Desa')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('contoh: Kampar Kiri Hilir')
                                    ->helperText('Nama kelurahan/desa tanpa prefix tipe')
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

                Tables\Columns\TextColumn::make('district.regency.name')
                    ->label('Kabupaten/Kota')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('district.name')
                    ->label('Kecamatan')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Kelurahan/Desa')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->weight('medium'),

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
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn(Builder $query, $value): Builder => $query->whereHas('district.regency', function ($query) use ($value) {
                                $query->where('id', $value);
                            })
                        );
                    })
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('district_id')
                    ->label('Kecamatan')
                    ->options(function ($livewire) {
                        return District::whereHas('regency', function ($query) use ($livewire) {
                            $query->where('province_id', $livewire->ownerRecord->id);
                        })
                            ->with('regency')
                            ->get()
                            ->mapWithKeys(function ($district) {
                                return [$district->id => $district->regency->name . ' - ' . $district->name];
                            });
                    })
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Kelurahan/Desa')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Tambah Kelurahan/Desa Baru')
                    ->successNotificationTitle('Kelurahan/Desa berhasil ditambahkan'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Edit Kelurahan/Desa')
                    ->successNotificationTitle('Kelurahan/Desa berhasil diperbarui'),
                Tables\Actions\DeleteAction::make()
                    ->successNotificationTitle('Kelurahan/Desa berhasil dihapus'),
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
            ->defaultSort('district.regency.name')
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
