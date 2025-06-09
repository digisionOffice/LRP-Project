<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KaryawanResource\Pages;
use App\Filament\Resources\KaryawanResource\RelationManagers;
use App\Models\Karyawan;
use App\Models\Jabatan;
use App\Models\Divisi;
use App\Models\Entitas;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KaryawanResource extends Resource
{
    protected static ?string $model = Karyawan::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?string $navigationLabel = 'Karyawan';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Karyawan')
                    ->schema([
                        Forms\Components\TextInput::make('no_induk')
                            ->label('Nomor Induk')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(100)
                            ->placeholder('Contoh: EMP001'),

                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Masukkan nama lengkap karyawan'),

                        Forms\Components\TextInput::make('hp')
                            ->label('Nomor HP')
                            ->tel()
                            ->maxLength(100)
                            ->placeholder('Contoh: 081234567890'),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(100)
                            ->placeholder('Contoh: nama@lrp.com'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informasi Jabatan')
                    ->schema([
                        Forms\Components\Select::make('id_jabatan')
                            ->label('Jabatan')
                            ->options(Jabatan::all()->pluck('nama', 'id'))
                            ->searchable()
                            ->placeholder('Pilih jabatan'),

                        Forms\Components\Select::make('id_divisi')
                            ->label('Divisi')
                            ->options(Divisi::all()->pluck('nama', 'id'))
                            ->searchable()
                            ->placeholder('Pilih divisi'),

                        Forms\Components\Select::make('id_entitas')
                            ->label('Entitas')
                            ->options(Entitas::all()->pluck('nama', 'id'))
                            ->searchable()
                            ->placeholder('Pilih entitas (opsional)'),

                        Forms\Components\Select::make('id_user')
                            ->label('User Account')
                            ->options(User::all()->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Link ke user account (opsional)')
                            ->helperText('Hubungkan dengan akun user untuk login'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no_induk')
                    ->label('No. Induk')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jabatan.nama')
                    ->label('Jabatan')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('divisi.nama')
                    ->label('Divisi')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('hp')
                    ->label('No. HP')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Nomor HP disalin!')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email disalin!')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id_jabatan')
                    ->label('Jabatan')
                    ->options(Jabatan::all()->pluck('nama', 'id')),

                Tables\Filters\SelectFilter::make('id_divisi')
                    ->label('Divisi')
                    ->options(Divisi::all()->pluck('nama', 'id')),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListKaryawans::route('/'),
            'create' => Pages\CreateKaryawan::route('/create'),
            'edit' => Pages\EditKaryawan::route('/{record}/edit'),
        ];
    }
}
