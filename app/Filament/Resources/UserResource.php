<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Jabatan;
use App\Models\Divisi;
use App\Models\Entitas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?string $navigationLabel = 'Pengguna & Karyawan';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Akun')
                    ->description('Pengaturan dasar akun pengguna dan detail autentikasi')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('avatar')
                            ->label('Foto Profil')
                            ->collection('avatar')
                            ->image()
                            ->imageEditor()
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('300')
                            ->imageResizeTargetHeight('300')
                            ->helperText('Unggah foto profil (disarankan: gambar persegi, maksimal 2MB)')
                            ->maxSize(2048)
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Masukkan nama lengkap pengguna')
                                    ->helperText('Nama ini akan ditampilkan di seluruh sistem')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('email')
                                    ->label('Alamat Email')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->placeholder('pengguna@perusahaan.com')
                                    ->helperText('Digunakan untuk login dan notifikasi')
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('password')
                                    ->label('Kata Sandi')
                                    ->password()
                                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                                    ->dehydrated(fn($state) => filled($state))
                                    ->required(fn(string $context): bool => $context === 'create')
                                    ->placeholder('Masukkan kata sandi yang aman')
                                    ->helperText(
                                        fn(string $context): string =>
                                        $context === 'create'
                                            ? 'Disarankan minimal 8 karakter'
                                            : 'Biarkan kosong untuk mempertahankan kata sandi saat ini'
                                    )
                                    ->minLength(8)
                                    ->columnSpan(1),

                                Forms\Components\Select::make('roles')
                                    ->label('Peran Sistem')
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->placeholder('Pilih peran pengguna')
                                    ->helperText('Menentukan izin sistem dan tingkat akses')
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Akun')
                            ->helperText('Aktifkan atau nonaktifkan akses pengguna ke sistem')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->collapsible()
                    ->persistCollapsed(),

                Forms\Components\Section::make('Informasi Karyawan')
                    ->description('Detail karyawan dan struktur organisasi')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('no_induk')
                                    ->label('ID Karyawan')
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(100)
                                    ->placeholder('contoh: EMP001, SYS001')
                                    ->helperText('Pengenal unik untuk karyawan')
                                    ->columnSpan(1),

                                // Forms\Components\TextInput::make('hp')
                                //     ->label('Nomor Telepon')
                                //     ->tel()
                                //     ->maxLength(100)
                                //     ->placeholder('contoh: +62 81234567890')
                                //     ->helperText('Nomor kontak karyawan')
                                //     ->columnSpan(1),

                                Forms\Components\TextInput::make('hp')
                                    ->label('Nomor Telepon')
                                    ->tel()
                                    ->maxLength(15) // Adjusted for typical phone number length
                                    ->placeholder('contoh: +6281234567890')
                                    ->helperText('Masukkan nomor telepon aktif, contoh: +6281234567890')
                                    ->default('+62')
                                    ->prefix('+62') // Visually lock the prefix
                                    ->mask('+6299999999999') // Optional: Apply input mask
                                    ->rules([
                                        'required',
                                        'regex:/^\+62[0-9]{9,12}$/', // Enforce +62 followed by 9-12 digits
                                    ])
                                    ->validationMessages([
                                        'required' => 'Nomor telepon wajib diisi.',
                                        'regex' => 'Nomor telepon harus diawali +62 diikuti 9-12 angka.',
                                    ])
                                    ->columnSpan(1)
                                    ->reactive() // Enable real-time validation
                                    ->dehydrateStateUsing(fn($state) => preg_replace('/\s+/', '', $state)) // Normalize input
                                    ->extraAttributes(['autocomplete' => 'tel']), // Improve accessibility

                                // Forms\Components\Select::make('id_entitas')
                                //     ->label('Entitas')
                                //     ->relationship('entitas', 'nama')
                                //     ->searchable()
                                //     ->preload()
                                //     ->placeholder('Pilih entitas (opsional)')
                                //     ->helperText('Entitas bisnis atau cabang perusahaan')
                                //     ->columnSpan(1),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('id_jabatan')
                                    ->label('Jabatan')
                                    ->relationship('jabatan', 'nama')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Pilih jabatan karyawan')
                                    ->helperText('Gelar pekerjaan atau posisi dalam organisasi')
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('nama')
                                            ->label('Nama Jabatan')
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->columnSpan(1),

                                Forms\Components\Select::make('id_divisi')
                                    ->label('Divisi')
                                    ->relationship('divisi', 'nama')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Pilih divisi')
                                    ->helperText('Departemen atau divisi dalam organisasi')
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('nama')
                                            ->label('Nama Divisi')
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->collapsible()
                    ->persistCollapsed(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('avatar')
                    ->label('Avatar')
                    ->collection('avatar')
                    ->conversion('thumb')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl(url('/images/default-avatar.png'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('no_induk')
                    ->label('ID Karyawan')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('ID Karyawan disalin!')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Email disalin!')
                    ->icon('heroicon-m-envelope'),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Peran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'admin' => 'warning',
                        'sales' => 'success',
                        'operational' => 'info',
                        'driver' => 'primary',
                        'finance' => 'secondary',
                        'administration' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'super_admin' => 'Super Admin',
                        'admin' => 'Administrator',
                        'sales' => 'Manajer Penjualan',
                        'operational' => 'Manajer Operasional',
                        'driver' => 'Sopir',
                        'finance' => 'Manajer Keuangan',
                        'administration' => 'Staf Administrasi',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->searchable()
                    ->sortable()
                    ->separator(', '),

                Tables\Columns\TextColumn::make('jabatan.nama')
                    ->label('Jabatan')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->placeholder('Tidak Ada Jabatan')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('divisi.nama')
                    ->label('Divisi')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->placeholder('Tidak Ada Divisi')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('hp')
                    ->label('Telepon')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Nomor telepon disalin!')
                    ->icon('heroicon-m-phone')
                    ->placeholder('Tidak Ada Telepon')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('entitas.nama')
                    ->label('Entitas')
                    ->badge()
                    ->color('warning')
                    ->placeholder('Tidak Ada Entitas')
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
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Peran')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->placeholder('Semua Peran'),

                Tables\Filters\SelectFilter::make('id_jabatan')
                    ->label('Jabatan')
                    ->relationship('jabatan', 'nama')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->placeholder('Semua Jabatan'),

                Tables\Filters\SelectFilter::make('id_divisi')
                    ->label('Divisi')
                    ->relationship('divisi', 'nama')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->placeholder('Semua Divisi'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Akun')
                    ->placeholder('Semua Pengguna')
                    ->trueLabel('Pengguna Aktif')
                    ->falseLabel('Pengguna Tidak Aktif'),

                Tables\Filters\Filter::make('has_employee_data')
                    ->label('Memiliki Data Karyawan')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('no_induk'))
                    ->toggle(),

                Tables\Filters\TrashedFilter::make()
                    ->label('Pengguna Terhapus'),
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
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
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
