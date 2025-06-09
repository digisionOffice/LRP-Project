<?php

namespace App\Filament\Resources\DistrictResource\RelationManagers;

use App\Models\Subdistrict;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubdistrictsRelationManager extends RelationManager
{
    protected static string $relationship = 'subdistricts';

    protected static ?string $title = 'Subdistricts (Kelurahan/Desa)';

    protected static ?string $modelLabel = 'Subdistrict';

    protected static ?string $pluralModelLabel = 'Subdistricts';

    protected static ?string $icon = 'heroicon-o-home';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Subdistrict Information')
                    ->description('Add or edit subdistrict (kelurahan/desa) information')
                    ->schema([
                        Forms\Components\TextInput::make('id')
                            ->label('Subdistrict ID')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(10)
                            ->placeholder('e.g., 1401010001')
                            ->helperText('10-digit subdistrict code'),

                        Forms\Components\TextInput::make('name')
                            ->label('Subdistrict Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Kampar Kiri Hilir')
                            ->helperText('Subdistrict name without type prefix'),

                        Forms\Components\Hidden::make('district_id')
                            ->default(fn($livewire) => $livewire->ownerRecord->id),

                        Forms\Components\Hidden::make('created_by')
                            ->default(auth()->id()),
                    ])
                    ->columns(2),
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
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Subdistrict Name')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Subdistrict')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Add New Subdistrict')
                    ->successNotificationTitle('Subdistrict created successfully'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Edit Subdistrict')
                    ->successNotificationTitle('Subdistrict updated successfully'),
                Tables\Actions\DeleteAction::make()
                    ->successNotificationTitle('Subdistrict deleted successfully'),
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
            ->defaultSort('name')
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
