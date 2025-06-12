<?php

namespace App\Filament\Pages;

use App\Models\TransaksiPenjualan;
use App\Models\DeliveryOrder;
use App\Models\PengirimanDriver;
use App\Models\UangJalan;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SalesOrderTimelineDetail extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $title = 'Sales Order Timeline Detail';
    protected static string $view = 'filament.pages.sales-order-timeline-detail';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'sales-order-timeline-detail';

    public TransaksiPenjualan $record;

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
                'subdistrict.district.regency.province',
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
        return "Timeline for SO: {$this->record->kode}";
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/admin/sales-order-timeline' => 'Sales Order Timeline',
            '' => "SO: {$this->record->kode}",
        ];
    }

    public function getDeliveryOrders()
    {
        return DeliveryOrder::where('id_transaksi', $this->record->id)
            ->with(['user', 'kendaraan', 'pengirimanDriver', 'uangJalan'])
            ->orderBy('created_at')
            ->get();
    }

    public function getTimelineEvents()
    {
        $events = collect();

        // Sales Order Created Event
        $events->push([
            'type' => 'sales_order_created',
            'title' => 'Sales Order Created',
            'description' => 'Sales order was created and confirmed',
            'timestamp' => $this->record->created_at,
            'icon' => 'heroicon-o-document-plus',
            'color' => 'blue',
            'data' => [
                'so_number' => $this->record->kode,
                'customer' => $this->record->pelanggan->nama ?? 'N/A',
                'fuel_types' => $this->record->penjualanDetails->pluck('item.name')->unique()->join(', '),
                'total_volume' => number_format($this->record->penjualanDetails->sum('volume_item'), 2) . ' L',
                'tbbm' => $this->record->tbbm->nama ?? 'N/A',
                'created_by' => $this->record->createdBy->name ?? 'System',
            ]
        ]);

        // Delivery Orders Events
        $deliveryOrders = $this->getDeliveryOrders();
        foreach ($deliveryOrders as $do) {
            // Delivery Order Created
            $events->push([
                'type' => 'delivery_order_created',
                'title' => 'Delivery Order Created',
                'description' => 'Delivery order was created for this sales order',
                'timestamp' => $do->created_at,
                'icon' => 'heroicon-o-truck',
                'color' => 'indigo',
                'data' => [
                    'do_number' => $do->kode,
                    'delivery_date' => $do->tanggal_delivery ? $do->tanggal_delivery->format('d M Y') : 'Not scheduled',
                    'vehicle' => $do->kendaraan->nomor_polisi ?? 'Not assigned',
                    'driver' => $do->user->name ?? 'Not assigned',
                    'seal_number' => $do->no_segel ?? 'Not set',
                    'status' => $do->status_muat ?? 'pending',
                ]
            ]);

            // Loading Events
            if ($do->waktu_muat) {
                $events->push([
                    'type' => 'loading_started',
                    'title' => 'Loading Started',
                    'description' => 'Fuel loading process has begun',
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
                    'title' => 'Loading Completed',
                    'description' => 'Fuel loading process has been completed',
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
                    'title' => 'Driver Allowance Created',
                    'description' => 'Driver allowance has been prepared',
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
                        'title' => 'Delivery Departed',
                        'description' => 'Vehicle has departed for delivery',
                        'timestamp' => $delivery->waktu_berangkat,
                        'icon' => 'heroicon-o-arrow-right',
                        'color' => 'orange',
                        'data' => [
                            'do_number' => $do->kode,
                            'vehicle' => $do->kendaraan->nomor_polisi ?? 'N/A',
                            'driver' => $do->user->name ?? 'N/A',
                        ]
                    ]);
                }

                if ($delivery->waktu_tiba) {
                    $events->push([
                        'type' => 'delivery_arrived',
                        'title' => 'Delivery Arrived',
                        'description' => 'Vehicle has arrived at destination',
                        'timestamp' => $delivery->waktu_tiba,
                        'icon' => 'heroicon-o-map-pin',
                        'color' => 'teal',
                        'data' => [
                            'do_number' => $do->kode,
                            'location' => $delivery->lokasi_tiba ?? 'Customer location',
                        ]
                    ]);
                }

                if ($delivery->waktu_selesai) {
                    $events->push([
                        'type' => 'delivery_completed',
                        'title' => 'Delivery Completed',
                        'description' => 'Fuel delivery has been completed',
                        'timestamp' => $delivery->waktu_selesai,
                        'icon' => 'heroicon-o-check-badge',
                        'color' => 'emerald',
                        'data' => [
                            'do_number' => $do->kode,
                            'delivered_volume' => $delivery->volume_terkirim ? number_format($delivery->volume_terkirim, 2) . ' L' : 'N/A',
                        ]
                    ]);
                }
            }
        }

        // Sort events by timestamp
        return $events->sortBy('timestamp');
    }
}
