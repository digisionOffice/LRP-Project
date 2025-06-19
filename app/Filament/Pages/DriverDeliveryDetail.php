<?php

namespace App\Filament\Pages;

use App\Models\DeliveryOrder;
use App\Models\PengirimanDriver;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class DriverDeliveryDetail extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $title = 'Detail Pengiriman';
    protected static string $view = 'filament.pages.driver-delivery-detail';
    protected static bool $shouldRegisterNavigation = false;

    public ?DeliveryOrder $record = null;
    public array $totalisatorData = [];
    public array $statusData = [];

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('driver') ?? false;
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function mount(): void
    {
        $recordId = request()->query('record');

        if (!$recordId) {
            abort(404, 'Record ID is required');
        }

        $this->record = DeliveryOrder::with([
            'transaksi.pelanggan.alamatPelanggan',
            'transaksi.penjualanDetails.item',
            'kendaraan',
            'pengirimanDriver',
            'uangJalan.approvedBy',
            'user'
        ])->findOrFail($recordId);

        // Check if the current driver is assigned to this delivery
        if ($this->record->id_user !== Auth::id()) {
            abort(403, 'You are not authorized to view this delivery order.');
        }

        // Initialize form data
        $this->totalisatorData = [
            'totalisator_awal' => $this->record->pengirimanDriver?->totalisator_awal,
            'totalisator_tiba' => $this->record->pengirimanDriver?->totalisator_tiba,
            'totalisator_pool_return' => $this->record->pengirimanDriver?->totalisator_pool_return,
            'waktu_mulai' => $this->record->pengirimanDriver?->waktu_mulai,
            'waktu_tiba' => $this->record->pengirimanDriver?->waktu_tiba,
            'waktu_pool_arrival' => $this->record->pengirimanDriver?->waktu_pool_arrival,
        ];

        $this->statusData = [
            'status_muat' => $this->record->status_muat,
        ];

        // Initialize forms
        $this->totalisatorForm->fill($this->totalisatorData);
        $this->statusForm->fill($this->statusData);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_dashboard')
                ->label('Kembali ke Dashboard')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(route('filament.admin.pages.driver-dashboard')),
        ];
    }

    protected function getForms(): array
    {
        return [
            'totalisatorForm',
            'statusForm',
        ];
    }

    public function totalisatorForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Totalisator')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('totalisator_awal')
                                    ->label('Totalisator Awal')
                                    ->numeric()
                                    ->suffix('km'),

                                Forms\Components\TextInput::make('totalisator_tiba')
                                    ->label('Totalisator Tiba')
                                    ->numeric()
                                    ->suffix('km'),

                                Forms\Components\TextInput::make('totalisator_pool_return')
                                    ->label('Totalisator Kembali Pool')
                                    ->numeric()
                                    ->suffix('km'),
                            ]),

                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\DateTimePicker::make('waktu_mulai')
                                    ->label('Waktu Mulai'),

                                Forms\Components\DateTimePicker::make('waktu_tiba')
                                    ->label('Waktu Tiba'),

                                Forms\Components\DateTimePicker::make('waktu_pool_arrival')
                                    ->label('Waktu Kembali Pool'),
                            ]),
                    ])
            ])
            ->statePath('totalisatorData')
            ->model($this->record);
    }

    public function statusForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status_muat')
                    ->label('Status Muat')
                    ->options([
                        'pending' => 'Menunggu',
                        'muat' => 'Sedang Muat',
                        'selesai' => 'Selesai',
                    ])
                    ->required()
            ])
            ->statePath('statusData')
            ->model($this->record);
    }

    public function updateTotalisator(): void
    {
        if (!$this->record) {
            Notification::make()
                ->title('Record tidak ditemukan')
                ->danger()
                ->send();
            return;
        }

        try {
            $data = $this->totalisatorForm->getState();

            if ($this->record->pengirimanDriver) {
                $this->record->pengirimanDriver->update($data);
            } else {
                PengirimanDriver::create([
                    'id_do' => $this->record->id,
                    'created_by' => Auth::id(),
                    ...$data
                ]);
                $this->record->refresh();
            }

            Notification::make()
                ->title('Data totalisator berhasil diperbarui')
                ->success()
                ->send();

            // Hide the form section
            $this->dispatch('toggle-section', 'totalisator-section');
        } catch (\Exception $e) {
            Notification::make()
                ->title('Terjadi kesalahan saat menyimpan data')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function updateStatus(): void
    {
        if (!$this->record) {
            Notification::make()
                ->title('Record tidak ditemukan')
                ->danger()
                ->send();
            return;
        }

        try {
            $data = $this->statusForm->getState();
            $this->record->update($data);

            Notification::make()
                ->title('Status pengiriman berhasil diperbarui')
                ->success()
                ->send();

            // Hide the form section
            $this->dispatch('toggle-section', 'status-section');
        } catch (\Exception $e) {
            Notification::make()
                ->title('Terjadi kesalahan saat menyimpan data')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function approveAllowance(): void
    {
        if (!$this->record) {
            Notification::make()
                ->title('Record tidak ditemukan')
                ->danger()
                ->send();
            return;
        }

        if ($this->record->uangJalan && $this->record->uangJalan->canBeApproved()) {
            $this->record->uangJalan->approve(Auth::user());
            $this->record->refresh();

            Notification::make()
                ->title('Uang jalan berhasil disetujui')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Uang jalan tidak dapat disetujui')
                ->danger()
                ->send();
        }
    }

    public function approveDelivery(): void
    {
        if (!$this->record) {
            Notification::make()
                ->title('Record tidak ditemukan')
                ->danger()
                ->send();
            return;
        }

        if ($this->record->pengirimanDriver && $this->record->pengirimanDriver->canBeApproved()) {
            $this->record->pengirimanDriver->approve(Auth::user());
            $this->record->refresh();

            Notification::make()
                ->title('Pengiriman berhasil disetujui')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Pengiriman tidak dapat disetujui')
                ->danger()
                ->send();
        }
    }

    public function getDeliveryTimeline(): array
    {
        if (!$this->record) {
            return [];
        }

        $timeline = [];

        // Created
        $timeline[] = [
            'title' => 'DO Dibuat',
            'description' => 'Delivery Order dibuat',
            'time' => $this->record->created_at,
            'status' => 'completed',
            'icon' => 'heroicon-o-document-plus'
        ];

        // Uang Jalan
        if ($this->record->uangJalan) {
            $timeline[] = [
                'title' => 'Uang Jalan',
                'description' => 'Nominal: Rp ' . number_format($this->record->uangJalan->nominal, 0, ',', '.'),
                'time' => $this->record->uangJalan->created_at,
                'status' => $this->record->uangJalan->approval_status === 'approved' ? 'completed' : 'pending',
                'icon' => 'heroicon-o-banknotes'
            ];
        }

        // Pengiriman Started
        if ($this->record->pengirimanDriver?->waktu_mulai) {
            $timeline[] = [
                'title' => 'Pengiriman Dimulai',
                'description' => 'Driver memulai perjalanan',
                'time' => $this->record->pengirimanDriver->waktu_mulai,
                'status' => 'completed',
                'icon' => 'heroicon-o-play'
            ];
        }

        // Arrived at Destination
        if ($this->record->pengirimanDriver?->waktu_tiba) {
            $timeline[] = [
                'title' => 'Tiba di Tujuan',
                'description' => 'Driver tiba di lokasi pengiriman',
                'time' => $this->record->pengirimanDriver->waktu_tiba,
                'status' => 'completed',
                'icon' => 'heroicon-o-map-pin'
            ];
        }

        // Returned to Pool
        if ($this->record->pengirimanDriver?->waktu_pool_arrival) {
            $timeline[] = [
                'title' => 'Kembali ke Pool',
                'description' => 'Driver kembali ke pool',
                'time' => $this->record->pengirimanDriver->waktu_pool_arrival,
                'status' => 'completed',
                'icon' => 'heroicon-o-home'
            ];
        }

        return $timeline;
    }
}
