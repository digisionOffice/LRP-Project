<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationSettingResource\Pages;
use App\Models\NotificationSetting;
use App\Models\User; // <-- 1. Import model User
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NotificationSettingResource extends Resource
{
    protected static ?string $model = NotificationSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert'; // <-- Icon diubah agar lebih sesuai
    
    protected static ?string $navigationGroup = 'Settings'; // <-- Mengelompokkan menu navigasi

    public static function form(Form $form): Form
    {
        // Definisikan event yang valid di sini agar konsisten
        $validEvents = [
            'penjualan_baru' => 'Penjualan Baru (Butuh Approval)',
            'penjualan_disetujui' => 'Penjualan Disetujui',
            'penjualan_ditolak' => 'Penjualan Ditolak',
            'penjualan_membutuhkan_revisi' => 'Penjualan Butuh Revisi',
            'expense_approved_for_finance' => 'Notif Persetujuan Expense Ke Finance',
            
            // Unified manager notification for expense updates
            'expense_manager_update_direksi' => 'Notif Manajer: Update Permintaan Biaya (Direksi)',
            'expense_manager_update_sales' => 'Notif Manajer: Update Permintaan Biaya (Sales)',
            'expense_manager_update_operasional' => 'Notif Manajer: Update Permintaan Biaya (Operasional)',
            'expense_manager_update_administrasi' => 'Notif Manajer: Update Permintaan Biaya (Administrasi)',
            'expense_manager_update_keuangan' => 'Notif Manajer: Update Permintaan Biaya (Keuangan)',
            'expense_manager_update_hrd' => 'Notif Manajer: Update Permintaan Biaya (HRD)',
            'expense_manager_update_it' => 'Notif Manajer: Update Permintaan Biaya (IT)',

            // Unified notif for sph
            'sph_manager_update_sales' => 'Notif Manajer: SPH Baru (Sales)',
        ];

        return $form
            ->schema([
                // Menggunakan Section untuk tampilan yang lebih rapi
                Forms\Components\Section::make('Aturan Notifikasi')
                    ->description('Tentukan event apa yang akan mengirim notifikasi ke user mana.')
                    ->schema([
                        // Dropdown untuk memilih event. Mencegah typo.
                        Forms\Components\Select::make('event_name')
                            ->label('Nama Event')
                            ->options($validEvents)
                            ->required()
                            ->searchable()
                            ->helperText('Pilih event yang akan memicu notifikasi.'),

                        // Dropdown untuk memilih user. Searchable agar mudah dicari.
                        Forms\Components\Select::make('user_id')
                            ->label('User Penerima Notifikasi')
                            ->relationship('user', 'name') // Mengambil 'name' dari relasi 'user'
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Pilih user yang akan menerima pesan.'),
                        
                        // Opsi channel, jika nanti ingin menambah email, dll.
                        Forms\Components\Select::make('channel')
                            ->label('Kirim Melalui')
                            ->options([
                                'whatsapp' => 'WhatsApp',
                                'email' => 'Email',
                                'database' => 'Database (Notifikasi di aplikasi)',
                            ])
                            ->default('whatsapp')
                            ->required(),
                        
                        // Toggle untuk mengaktifkan/menonaktifkan aturan
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aturan Aktif')
                            ->default(true)
                            ->required(),
                    ])->columns(2), // Membuat form menjadi 2 kolom
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Menampilkan nama event dengan badge agar lebih menarik
                Tables\Columns\TextColumn::make('event_name')
                    ->label('Event')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'penjualan_baru' => 'info',
                        'penjualan_disetujui' => 'success',
                        'penjualan_ditolak' => 'danger',
                        'penjualan_membutuhkan_revisi' => 'warning',
                        default => 'gray',
                    })
                    ->searchable(),

                // Menampilkan nama user dari relasi
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User Penerima')
                    ->searchable()
                    ->sortable(),

                // Menampilkan channel
                Tables\Columns\TextColumn::make('channel')
                    ->label('Channel')
                    ->badge(),

                // Menampilkan status aktif dengan toggle yang bisa di-klik langsung
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif'),

                // Menampilkan tanggal dibuat
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Sembunyikan default
            ])
            ->filters([
                // Filter berdasarkan nama event
                Tables\Filters\SelectFilter::make('event_name')
                    ->options([
                        'penjualan_baru' => 'Penjualan Baru',
                        'penjualan_disetujui' => 'Penjualan Disetujui',
                        'penjualan_ditolak' => 'Penjualan Ditolak',
                        'penjualan_membutuhkan_revisi' => 'Penjualan Butuh Revisi',
                    ]),
                // Filter berdasarkan user
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
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
            'index' => Pages\ListNotificationSettings::route('/'),
            'create' => Pages\CreateNotificationSetting::route('/create'),
            'edit' => Pages\EditNotificationSetting::route('/{record}/edit'),
            'view' => Pages\ViewNotificationSetting::route('/{record}'), // <-- Tambahkan halaman view
        ];
    }
}
