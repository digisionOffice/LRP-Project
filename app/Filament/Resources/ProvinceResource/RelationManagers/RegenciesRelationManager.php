<?php

namespace App\Filament\Resources\ProvinceResource\RelationManagers;

use App\Models\Regency;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RegenciesRelationManager extends RelationManager
{
    protected static string $relationship = 'regencies';

    protected static ?string $title = 'Regencies (Kabupaten/Kota)';

    protected static ?string $modelLabel = 'Regency';

    protected static ?string $pluralModelLabel = 'Regencies';

    protected static ?string $icon = 'heroicon-o-building-office-2';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Regency Information')
                    ->description('Add or edit regency (kabupaten/kota) information')
                    ->schema([
                        Forms\Components\TextInput::make('id')
                            ->label('Regency ID')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(4)
                            ->placeholder('e.g., 1401')
                            ->helperText('4-digit regency code'),

                        Forms\Components\TextInput::make('name')
                            ->label('Regency Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Kabupaten Kampar')
                            ->helperText('Full regency name including type (Kabupaten/Kota)'),

                        Forms\Components\Hidden::make('province_id')
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
                    ->label('Regency Name')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('districts_count')
                    ->label('Districts')
                    ->counts('districts')
                    ->badge()
                    ->color('info')
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
                    ->label('Add Regency')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Add New Regency')
                    ->successNotificationTitle('Regency created successfully'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Edit Regency')
                    ->successNotificationTitle('Regency updated successfully'),
                Tables\Actions\DeleteAction::make()
                    ->successNotificationTitle('Regency deleted successfully'),
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
