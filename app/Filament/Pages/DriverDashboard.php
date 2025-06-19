<?php

namespace App\Filament\Pages;

use App\Models\DeliveryOrder;
use App\Models\PengirimanDriver;
use App\Models\UangJalan;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\Action;
use Filament\Support\Colors\Color;
use Filament\Actions;
use Filament\Support\Enums\ActionSize;

class DriverDashboard extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Dashboard Driver';
    protected static ?string $title = 'Dashboard Driver';
    protected static string $view = 'filament.pages.driver-dashboard';
    protected static ?int $navigationSort = 1;

    public string $viewMode = 'auto';

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('driver') ?? false;
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('toggle_view')
                ->label($this->getToggleButtonLabel())
                ->icon($this->getToggleButtonIcon())
                ->color('gray')
                ->action(function () {
                    $this->toggleViewMode();
                })
                ->extraAttributes([
                    'id' => 'view-toggle-button',
                    'class' => 'hidden md:inline-flex', // Hide on mobile, show on desktop
                ]),
        ];
    }

    public function toggleViewMode(): void
    {
        $this->viewMode = $this->viewMode === 'table' ? 'compact' : 'table';
    }

    public function setViewMode(string $mode): void
    {
        if (in_array($mode, ['table', 'compact'])) {
            $this->viewMode = $mode;
        }
    }

    private function getToggleButtonLabel(): string
    {
        return match ($this->viewMode) {
            'table' => 'Tampilan Kompak',
            'compact' => 'Tampilan Tabel',
            default => 'Tampilan Kompak'
        };
    }

    private function getToggleButtonIcon(): string
    {
        return match ($this->viewMode) {
            'table' => 'heroicon-o-squares-2x2',
            'compact' => 'heroicon-o-table-cells',
            default => 'heroicon-o-squares-2x2'
        };
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DeliveryOrder::query()
                    ->with([
                        'transaksi.pelanggan',
                        'transaksi.penjualanDetails.item',
                        'kendaraan',
                        'pengirimanDriver',
                        'uangJalan'
                    ])
                    ->where('id_user', Auth::id())
                    ->latest('tanggal_delivery')
            )
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode DO')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('transaksi.pelanggan.nama')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('kendaraan.nomor_polisi')
                    ->label('Kendaraan')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('tanggal_delivery')
                    ->label('Tanggal Pengiriman')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('volume_do')
                    ->label('Volume (L)')
                    ->numeric()
                    ->suffix(' L')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('status_muat')
                    ->label('Status Muat')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'danger',
                        'muat' => 'warning',
                        'selesai' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'muat' => 'Sedang Muat',
                        'selesai' => 'Selesai',
                        default => $state,
                    }),

                Tables\Columns\IconColumn::make('uangJalan.status_terima')
                    ->label('Uang Jalan')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\IconColumn::make('pengirimanDriver')
                    ->label('Status Pengiriman')
                    ->boolean()
                    ->getStateUsing(fn($record) => $record->pengirimanDriver !== null)
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('uangJalan.approval_status')
                    ->label('Approval Uang Jalan')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => 'N/A',
                    }),

                Tables\Columns\TextColumn::make('pengirimanDriver.approval_status')
                    ->label('Approval Pengiriman')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => 'N/A',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_muat')
                    ->label('Status Muat')
                    ->options([
                        'pending' => 'Menunggu',
                        'muat' => 'Sedang Muat',
                        'selesai' => 'Selesai',
                    ]),

                Tables\Filters\Filter::make('tanggal_delivery')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_delivery', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_delivery', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Action::make('view_details')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn(DeliveryOrder $record): string => route('filament.admin.pages.driver-delivery-detail', ['record' => $record->id])),

                Action::make('update_status')
                    ->label('Update Status')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->form([
                        \Filament\Forms\Components\Select::make('status_muat')
                            ->label('Status Muat')
                            ->options([
                                'pending' => 'Menunggu',
                                'muat' => 'Sedang Muat',
                                'selesai' => 'Selesai',
                            ])
                            ->required(),
                    ])
                    ->fillForm(fn(DeliveryOrder $record): array => [
                        'status_muat' => $record->status_muat,
                    ])
                    ->action(function (array $data, DeliveryOrder $record): void {
                        $record->update($data);
                        \Filament\Notifications\Notification::make()
                            ->title('Status berhasil diperbarui')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(DeliveryOrder $record) => $record->status_muat !== 'selesai'),

                Action::make('approve_allowance')
                    ->label('ACC Uang Jalan')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('approval_notes')
                            ->label('Catatan Persetujuan')
                            ->rows(3)
                            ->placeholder('Masukkan catatan persetujuan (opsional)'),
                    ])
                    ->action(function (array $data, DeliveryOrder $record): void {
                        if ($record->uangJalan && $record->uangJalan->canBeApproved()) {
                            $record->uangJalan->approve(Auth::user(), $data['approval_notes'] ?? null);
                            \Filament\Notifications\Notification::make()
                                ->title('Uang jalan berhasil disetujui')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Uang jalan tidak dapat disetujui')
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn(DeliveryOrder $record) => $record->uangJalan && $record->uangJalan->canBeApproved())
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Uang Jalan')
                    ->modalDescription('Apakah Anda yakin ingin menyetujui uang jalan ini?'),

                Action::make('reject_allowance')
                    ->label('Tolak Uang Jalan')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('approval_notes')
                            ->label('Alasan Penolakan')
                            ->rows(3)
                            ->required()
                            ->placeholder('Masukkan alasan penolakan'),
                    ])
                    ->action(function (array $data, DeliveryOrder $record): void {
                        if ($record->uangJalan && $record->uangJalan->canBeRejected()) {
                            $record->uangJalan->reject(Auth::user(), $data['approval_notes']);
                            \Filament\Notifications\Notification::make()
                                ->title('Uang jalan ditolak')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Uang jalan tidak dapat ditolak')
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn(DeliveryOrder $record) => $record->uangJalan && $record->uangJalan->canBeRejected())
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Uang Jalan')
                    ->modalDescription('Apakah Anda yakin ingin menolak uang jalan ini?'),

                Action::make('approve_delivery')
                    ->label('ACC Pengiriman')
                    ->icon('heroicon-o-truck')
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('approval_notes')
                            ->label('Catatan Persetujuan')
                            ->rows(3)
                            ->placeholder('Masukkan catatan persetujuan (opsional)'),
                    ])
                    ->action(function (array $data, DeliveryOrder $record): void {
                        if ($record->pengirimanDriver && $record->pengirimanDriver->canBeApproved()) {
                            $record->pengirimanDriver->approve(Auth::user(), $data['approval_notes'] ?? null);
                            \Filament\Notifications\Notification::make()
                                ->title('Pengiriman berhasil disetujui')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Pengiriman tidak dapat disetujui')
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn(DeliveryOrder $record) => $record->pengirimanDriver && $record->pengirimanDriver->canBeApproved())
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Pengiriman')
                    ->modalDescription('Apakah Anda yakin ingin menyetujui pengiriman ini?'),

                Action::make('reject_delivery')
                    ->label('Tolak Pengiriman')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('approval_notes')
                            ->label('Alasan Penolakan')
                            ->rows(3)
                            ->required()
                            ->placeholder('Masukkan alasan penolakan'),
                    ])
                    ->action(function (array $data, DeliveryOrder $record): void {
                        if ($record->pengirimanDriver && $record->pengirimanDriver->canBeRejected()) {
                            $record->pengirimanDriver->reject(Auth::user(), $data['approval_notes']);
                            \Filament\Notifications\Notification::make()
                                ->title('Pengiriman ditolak')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Pengiriman tidak dapat ditolak')
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn(DeliveryOrder $record) => $record->pengirimanDriver && $record->pengirimanDriver->canBeRejected())
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Pengiriman')
                    ->modalDescription('Apakah Anda yakin ingin menolak pengiriman ini?'),
            ])
            ->defaultSort('tanggal_delivery', 'desc')
            ->striped()
            ->paginated([10, 25, 50]);
    }

    public function getDeliveryStats(): array
    {
        $userId = Auth::id();

        return [
            'total_deliveries' => DeliveryOrder::where('id_user', $userId)->count(),
            'pending_deliveries' => DeliveryOrder::where('id_user', $userId)->where('status_muat', 'pending')->count(),
            'in_progress_deliveries' => DeliveryOrder::where('id_user', $userId)->where('status_muat', 'muat')->count(),
            'completed_deliveries' => DeliveryOrder::where('id_user', $userId)->where('status_muat', 'selesai')->count(),
            'total_volume' => DeliveryOrder::where('id_user', $userId)->sum('volume_do'),
            'pending_allowances' => UangJalan::whereHas('deliveryOrder', function ($query) use ($userId) {
                $query->where('id_user', $userId);
            })->where('status_terima', 'pending')->count(),
            'pending_allowance_approvals' => UangJalan::whereHas('deliveryOrder', function ($query) use ($userId) {
                $query->where('id_user', $userId);
            })->where('approval_status', 'pending')->count(),
            'approved_allowances' => UangJalan::whereHas('deliveryOrder', function ($query) use ($userId) {
                $query->where('id_user', $userId);
            })->where('approval_status', 'approved')->count(),
            'pending_delivery_approvals' => PengirimanDriver::whereHas('deliveryOrder', function ($query) use ($userId) {
                $query->where('id_user', $userId);
            })->where('approval_status', 'pending')->count(),
            'approved_deliveries' => PengirimanDriver::whereHas('deliveryOrder', function ($query) use ($userId) {
                $query->where('id_user', $userId);
            })->where('approval_status', 'approved')->count(),
        ];
    }

    public function getDeliveryOrders()
    {
        return DeliveryOrder::query()
            ->with([
                'transaksi.pelanggan.alamatPelanggan',
                'transaksi.penjualanDetails.item',
                'kendaraan',
                'pengirimanDriver',
                'uangJalan'
            ])
            ->where('id_user', Auth::id())
            ->latest('tanggal_delivery')
            ->get();
    }
}
