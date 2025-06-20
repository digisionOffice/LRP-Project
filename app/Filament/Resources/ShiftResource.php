<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShiftResource\Pages;
use App\Models\Shift;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ShiftResource extends Resource
{
    protected static ?string $model = Shift::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Shift Kerja';

    protected static ?string $modelLabel = 'Shift';

    protected static ?string $pluralModelLabel = 'Shift Kerja';

    protected static ?string $navigationGroup = 'Manajemen Karyawan';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Shift')
                    ->schema([
                        Forms\Components\TextInput::make('nama_shift')
                            ->label('Nama Shift')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Waktu Kerja Periode 1')
                    ->schema([
                        Forms\Components\TimePicker::make('waktu_mulai')
                            ->label('Waktu Mulai')
                            ->required()
                            ->seconds(false),

                        Forms\Components\TimePicker::make('waktu_selesai')
                            ->label('Waktu Selesai')
                            ->required()
                            ->seconds(false),

                        Forms\Components\TextInput::make('toleransi_keterlambatan')
                            ->label('Toleransi Keterlambatan (menit)')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(120),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Split Shift (Periode 2)')
                    ->schema([
                        Forms\Components\Toggle::make('is_split_shift')
                            ->label('Aktifkan Split Shift')
                            ->default(false)
                            ->live(),

                        Forms\Components\TimePicker::make('waktu_mulai_periode2')
                            ->label('Waktu Mulai Periode 2')
                            ->seconds(false)
                            ->visible(fn (Forms\Get $get): bool => $get('is_split_shift')),

                        Forms\Components\TimePicker::make('waktu_selesai_periode2')
                            ->label('Waktu Selesai Periode 2')
                            ->seconds(false)
                            ->visible(fn (Forms\Get $get): bool => $get('is_split_shift')),

                        Forms\Components\TextInput::make('toleransi_keterlambatan_periode2')
                            ->label('Toleransi Keterlambatan Periode 2 (menit)')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(120)
                            ->visible(fn (Forms\Get $get): bool => $get('is_split_shift')),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_shift')
                    ->label('Nama Shift')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('waktu_mulai')
                    ->label('Waktu Mulai')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('waktu_selesai')
                    ->label('Waktu Selesai')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('toleransi_keterlambatan')
                    ->label('Toleransi (menit)')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_split_shift')
                    ->label('Split Shift')
                    ->boolean(),

                Tables\Columns\TextColumn::make('waktu_mulai_periode2')
                    ->label('Periode 2 Mulai')
                    ->time('H:i')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('waktu_selesai_periode2')
                    ->label('Periode 2 Selesai')
                    ->time('H:i')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),

                Tables\Filters\TernaryFilter::make('is_split_shift')
                    ->label('Split Shift')
                    ->placeholder('Semua')
                    ->trueLabel('Split Shift')
                    ->falseLabel('Regular Shift'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nama_shift');
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
            'index' => Pages\ListShifts::route('/'),
            'create' => Pages\CreateShift::route('/create'),
            'view' => Pages\ViewShift::route('/{record}'),
            'edit' => Pages\EditShift::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }
}
