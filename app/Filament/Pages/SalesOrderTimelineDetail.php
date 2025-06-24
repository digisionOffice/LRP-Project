<?php

namespace App\Filament\Pages;

use App\Models\TransaksiPenjualan;
use App\Models\DeliveryOrder;
use App\Models\PengirimanDriver;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class SalesOrderTimelineDetail extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $title = 'Detail Timeline Pesanan Penjualan';
    protected static string $view = 'filament.pages.sales-order-timeline-detail';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'sales-order-timeline-detail';

    public ?TransaksiPenjualan $record = null;

    public static function canAccess(): bool
    {
        return Auth::user()?->can('page_SalesOrderTimelineDetail') ?? false;
    }

    public function mount(): void
    {
        // Get record ID from query parameter
        $recordId = request()->get('record');

        if (!$recordId) {
            abort(404, 'Sales Order ID not provided');
        }

        try {
            $this->record = TransaksiPenjualan::with([
                'pelanggan',
                'penjualanDetails.item.kategori',
                'penjualanDetails.item.satuan',
                'tbbm',
                'createdBy'
            ])->findOrFail($recordId);
        } catch (ModelNotFoundException) {
            abort(404, 'Sales Order not found');
        }
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function getTitle(): string
    {
        return $this->record ? "Timeline for SO: {$this->record->kode}" : 'Detail Timeline Pesanan Penjualan';
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/admin/sales-order-timeline' => 'Sales Order Timeline',
            '' => $this->record ? "SO: {$this->record->kode}" : 'Detail Timeline',
        ];
    }

    public function getDeliveryOrders()
    {
        if (!$this->record) {
            return collect();
        }

        return DeliveryOrder::where('id_transaksi', $this->record->id)
            ->with(['user', 'kendaraan', 'pengirimanDriver', 'uangJalan'])
            ->orderBy('created_at')
            ->get();
    }

    public function getTimelineEvents()
    {
        if (!$this->record) {
            return collect();
        }

        $events = collect();

        // Sales Order Created Event
        $events->push([
            'type' => 'sales_order_created',
            'title' => 'Sales Order Dibuat',
            'link' => route('filament.admin.resources.transaksi-penjualans.view', ['record' => $this->record->id]),
            'description' => 'Sales order dibuat dan dikonfirmasi',
            'timestamp' => $this->record->created_at,
            'icon' => 'heroicon-o-document-plus',
            'color' => 'blue',
            'data' => [
                'Nomor SO' => $this->record->kode,
                'Pelanggan' => $this->record->pelanggan->nama ?? 'N/A',
                'Jenis BBM' => $this->record->penjualanDetails->pluck('item.name')->unique()->join(', '),
                'Volume' => number_format($this->record->penjualanDetails->sum('volume_item'), 2) . ' Liter',
                'TBBM' => $this->record->tbbm->nama ?? 'N/A',
                'created_by' => $this->record->createdBy->name ?? 'System',
            ]
        ]);

        // Delivery Orders Events
        $deliveryOrders = $this->getDeliveryOrders();
        foreach ($deliveryOrders as $do) {
            // Delivery Order Created
            $events->push([
                'type' => 'delivery_order_created',
                'title' => 'Delivery Order Dibuat',
                'link' => route('filament.admin.resources.delivery-orders.view', ['record' => $do->id]),
                'description' => 'Delivery order dibuat untuk pesanan penjualan ini',
                'timestamp' => $do->created_at,
                'icon' => 'heroicon-o-truck',
                'color' => 'indigo',
                'data' => [
                    'Nomor DO' => $do->kode,
                    'Tanggal Pengiriman' => $do->tanggal_delivery ? $do->tanggal_delivery->format('d M Y') : 'Not scheduled',
                    'Kendaraan' => $do->kendaraan->nomor_polisi ?? 'Not assigned',
                    'Sopir' => $do->user->name ?? 'Not assigned',
                    'Nomor Segel' => $do->no_segel ?? 'Not set',
                    'Status' => $do->status_muat ?? 'pending',
                ]
            ]);

            // Loading Events
            if ($do->waktu_muat) {
                $events->push([
                    'type' => 'loading_started',
                    'title' => 'Muat Dimulai',
                    'link' => route('filament.admin.resources.delivery-orders.view', ['record' => $do->id]),
                    'description' => 'Proses muat dimulai',
                    'timestamp' => $do->waktu_muat,
                    'icon' => 'heroicon-o-arrow-down-on-square',
                    'color' => 'yellow',
                    'data' => [
                        'do_number' => $do->kode,
                        'vehicle' => $do->kendaraan->nomor_polisi ?? 'N/A',
                    ]
                ]);
            }

            if ($do->waktu_selesai_muat) {
                $events->push([
                    'type' => 'loading_completed',
                    'title' => 'Muat Selesai',
                    'link' => route('filament.admin.resources.delivery-orders.view', ['record' => $do->id]),
                    'description' => 'Proses muat selesai',
                    'timestamp' => $do->waktu_selesai_muat,
                    'icon' => 'heroicon-o-check-circle',
                    'color' => 'green',
                    'data' => [
                        'do_number' => $do->kode,
                        'vehicle' => $do->kendaraan->nomor_polisi ?? 'N/A',
                    ]
                ]);
            }

            // Driver Allowance Events
            if ($do->uangJalan) {
                $allowance = $do->uangJalan;

                $events->push([
                    'type' => 'allowance_created',
                    'title' => 'Uang Jalan Dibuat',
                    'link' => route('filament.admin.resources.uang-jalans.view', ['record' => $allowance->id]),
                    'description' => 'Uang jalan dibuat',
                    'timestamp' => $allowance->created_at,
                    'icon' => 'heroicon-o-banknotes',
                    'color' => 'purple',
                    'data' => [
                        'amount' => 'IDR ' . number_format($allowance->nominal),
                        'driver' => $allowance->user->name ?? 'N/A',
                        'send_status' => $allowance->status_kirim ?? 'pending',
                        'receive_status' => $allowance->status_terima ?? 'pending',
                    ]
                ]);
            }

            // Delivery Events
            if ($do->pengirimanDriver) {
                $delivery = $do->pengirimanDriver;

                if ($delivery->waktu_berangkat) {
                    $events->push([
                        'type' => 'delivery_departed',
                        'title' => 'Pengiriman Berangkat',
                        'link' => route('filament.admin.resources.pengiriman-drivers.view', ['record' => $delivery->id]),
                        'description' => 'Kendaraan berangkat untuk pengiriman',
                        'timestamp' => $delivery->waktu_berangkat,
                        'icon' => 'heroicon-o-arrow-right',
                        'color' => 'orange',
                        'data' => [
                            'Nomor DO' => $do->kode,
                            'Kendaraan' => $do->kendaraan->nomor_polisi ?? 'N/A',
                            'Sopir' => $do->user->name ?? 'N/A',
                        ]
                    ]);
                }

                if ($delivery->waktu_tiba) {
                    $events->push([
                        'type' => 'delivery_arrived',
                        'title' => 'Pengiriman Tiba',
                        'link' => route('filament.admin.resources.pengiriman-drivers.view', ['record' => $delivery->id]),
                        'description' => 'Kendaraan tiba di lokasi tujuan',
                        'timestamp' => $delivery->waktu_tiba,
                        'icon' => 'heroicon-o-map-pin',
                        'color' => 'teal',
                        'data' => [
                            'Nomor DO' => $do->kode,
                            'Lokasi Tujuan' => $delivery->lokasi_tiba ?? 'Lokasi Pelanggan',
                        ]
                    ]);
                }

                if ($delivery->waktu_selesai) {
                    $events->push([
                        'type' => 'delivery_completed',
                        'title' => 'Pengiriman Selesai',
                        'link' => route('filament.admin.resources.pengiriman-drivers.view', ['record' => $delivery->id]),
                        'description' => 'Pengiriman selesai',
                        'timestamp' => $delivery->waktu_selesai,
                        'icon' => 'heroicon-o-check-badge',
                        'color' => 'emerald',
                        'data' => [
                            'Nomor DO' => $do->kode,
                            'delivered_volume' => $delivery->volume_terkirim ? number_format($delivery->volume_terkirim, 2) . ' L' : 'N/A',
                        ]
                    ]);
                }
            }
        }

        // Sort events by timestamp
        return $events->sortBy('timestamp', null, true);
    }
}
