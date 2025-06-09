<?php

namespace App\Filament\Pages;

use App\Models\TransaksiPenjualan;
use App\Models\DeliveryOrder;
use App\Models\Pelanggan;
use App\Models\Item;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MonthlySalesRealizationDashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $navigationLabel = 'Monthly Sales Realization';
    protected static ?string $title = 'Monthly Sales Order Realization Report';
    protected static string $view = 'filament.pages.monthly-sales-realization-dashboard';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Reports & Analytics';

    public ?string $selectedMonth = null;
    public ?string $selectedYear = null;
    public ?string $selectedCustomer = null;
    public ?string $selectedProduct = null;

    public function mount(): void
    {
        $this->selectedMonth = now()->format('m');
        $this->selectedYear = now()->format('Y');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('selectedMonth')
                    ->label('Month')
                    ->options([
                        '01' => 'January',
                        '02' => 'February',
                        '03' => 'March',
                        '04' => 'April',
                        '05' => 'May',
                        '06' => 'June',
                        '07' => 'July',
                        '08' => 'August',
                        '09' => 'September',
                        '10' => 'October',
                        '11' => 'November',
                        '12' => 'December',
                    ])
                    ->default(now()->format('m'))
                    ->live(),

                Select::make('selectedYear')
                    ->label('Year')
                    ->options(collect(range(now()->year - 2, now()->year + 1))->mapWithKeys(fn($year) => [$year => $year]))
                    ->default(now()->format('Y'))
                    ->live(),

                Select::make('selectedCustomer')
                    ->label('Customer (Optional)')
                    ->options(Pelanggan::pluck('nama', 'id'))
                    ->searchable()
                    ->placeholder('All Customers')
                    ->live(),

                Select::make('selectedProduct')
                    ->label('Product Type (Optional)')
                    ->options(Item::whereHas('kategori', function ($query) {
                        $query->where('nama', 'like', '%BBM%');
                    })->pluck('name', 'id'))
                    ->searchable()
                    ->placeholder('All Products')
                    ->live(),
            ])
            ->columns(4);
    }

    public function getSalesOrdersData(): array
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "{$this->selectedYear}-{$this->selectedMonth}-01")->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $query = TransaksiPenjualan::query()
            ->with(['pelanggan', 'penjualanDetails.item'])
            ->whereBetween('tanggal', [$startDate, $endDate]);

        if ($this->selectedCustomer) {
            $query->where('id_pelanggan', $this->selectedCustomer);
        }

        if ($this->selectedProduct) {
            $query->whereHas('penjualanDetails', function ($q) {
                $q->where('id_item', $this->selectedProduct);
            });
        }

        return $query->get()->toArray();
    }

    public function getDeliveryRealizationData(): array
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "{$this->selectedYear}-{$this->selectedMonth}-01")->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $query = DeliveryOrder::query()
            ->with(['transaksi.pelanggan', 'transaksi.penjualanDetails.item'])
            ->whereBetween('tanggal_delivery', [$startDate, $endDate]);

        if ($this->selectedCustomer) {
            $query->whereHas('transaksi', function ($q) {
                $q->where('id_pelanggan', $this->selectedCustomer);
            });
        }

        return $query->get()->toArray();
    }

    public function getKpiData(): array
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "{$this->selectedYear}-{$this->selectedMonth}-01")->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Total Sales Orders
        $totalSO = TransaksiPenjualan::whereBetween('tanggal', [$startDate, $endDate])->count();

        // Completed Deliveries
        $completedDeliveries = DB::table('delivery_order')
            ->whereBetween('tanggal_delivery', [$startDate, $endDate])
            ->where('status_muat', 'selesai')
            ->count();

        // Pending Deliveries
        $pendingDeliveries = DB::table('delivery_order')
            ->whereBetween('tanggal_delivery', [$startDate, $endDate])
            ->whereIn('status_muat', ['pending', 'muat'])
            ->count();

        // Total Volume
        $totalVolume = DB::table('transaksi_penjualan')
            ->join('penjualan_detail', 'transaksi_penjualan.id', '=', 'penjualan_detail.id_transaksi_penjualan')
            ->whereBetween('transaksi_penjualan.tanggal', [$startDate, $endDate])
            ->sum('penjualan_detail.volume_item');

        // Delivered Volume
        $deliveredVolume = DB::table('delivery_order')
            ->join('transaksi_penjualan', 'delivery_order.id_transaksi', '=', 'transaksi_penjualan.id')
            ->join('penjualan_detail', 'transaksi_penjualan.id', '=', 'penjualan_detail.id_transaksi_penjualan')
            ->whereBetween('delivery_order.tanggal_delivery', [$startDate, $endDate])
            ->where('delivery_order.status_muat', 'selesai')
            ->sum('penjualan_detail.volume_item');

        // Completion Rate
        $completionRate = $totalSO > 0 ? round(($completedDeliveries / $totalSO) * 100, 1) : 0;

        // Volume Realization Rate
        $volumeRealizationRate = $totalVolume > 0 ? round(($deliveredVolume / $totalVolume) * 100, 1) : 0;

        return [
            'total_so' => $totalSO,
            'completed_deliveries' => $completedDeliveries,
            'pending_deliveries' => $pendingDeliveries,
            'total_volume' => $totalVolume,
            'delivered_volume' => $deliveredVolume,
            'completion_rate' => $completionRate,
            'volume_realization_rate' => $volumeRealizationRate,
        ];
    }

    public function getCustomerPerformanceData(): array
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "{$this->selectedYear}-{$this->selectedMonth}-01")->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return DB::table('transaksi_penjualan')
            ->join('pelanggan', 'transaksi_penjualan.id_pelanggan', '=', 'pelanggan.id')
            ->leftJoin('delivery_order', function ($join) use ($startDate, $endDate) {
                $join->on('transaksi_penjualan.id', '=', 'delivery_order.id_transaksi')
                    ->where('delivery_order.status_muat', 'selesai')
                    ->whereBetween('delivery_order.tanggal_delivery', [$startDate, $endDate]);
            })
            ->whereBetween('transaksi_penjualan.tanggal', [$startDate, $endDate])
            ->select([
                'pelanggan.nama as customer_name',
                DB::raw('COUNT(DISTINCT transaksi_penjualan.id) as total_orders'),
                DB::raw('COUNT(DISTINCT delivery_order.id) as completed_orders'),
                DB::raw('ROUND((COUNT(DISTINCT delivery_order.id) / COUNT(DISTINCT transaksi_penjualan.id)) * 100, 1) as completion_rate')
            ])
            ->groupBy('pelanggan.id', 'pelanggan.nama')
            ->orderBy('total_orders', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function getDailyTrendData(): array
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "{$this->selectedYear}-{$this->selectedMonth}-01")->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $dailyData = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $dayOrders = TransaksiPenjualan::whereDate('tanggal', $current)->count();
            $dayDeliveries = DB::table('delivery_order')
                ->whereDate('tanggal_delivery', $current)
                ->where('status_muat', 'selesai')
                ->count();

            $dailyData[] = [
                'date' => $current->format('Y-m-d'),
                'day' => $current->format('d'),
                'orders' => $dayOrders,
                'deliveries' => $dayDeliveries,
            ];

            $current->addDay();
        }

        return $dailyData;
    }
}
