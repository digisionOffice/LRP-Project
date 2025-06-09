<?php

namespace App\Filament\Resources\RegencyResource\RelationManagers;

use App\Models\District;
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

    protected static ?string $title = 'Districts (Kecamatan)';

    protected static ?string $modelLabel = 'District';

    protected static ?string $pluralModelLabel = 'Districts';

    protected static ?string $icon = 'heroicon-o-building-office';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('District Information')
                    ->description('Add or edit district (kecamatan) information')
                    ->schema([
                        Forms\Components\TextInput::make('id')
                            ->label('District ID')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(6)
                            ->placeholder('e.g., 140101')
                            ->helperText('6-digit district code'),

                        Forms\Components\TextInput::make('name')
                            ->label('District Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Kampar Kiri')
                            ->helperText('District name without type prefix'),

                        Forms\Components\Hidden::make('regency_id')
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
                    ->label('District Name')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('subdistricts_count')
                    ->label('Subdistricts')
                    ->counts('subdistricts')
                    ->badge()
                    ->color('success')
                    ->sortable(),

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
                    ->label('Add District')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Add New District')
                    ->successNotificationTitle('District created successfully'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn($record) => route('filament.admin.resources.districts.view', ['record' => $record]))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make()
                    ->modalHeading('Edit District')
                    ->successNotificationTitle('District updated successfully'),
                Tables\Actions\DeleteAction::make()
                    ->successNotificationTitle('District deleted successfully'),
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
