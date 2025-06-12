<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\Kendaraan;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MonthlyDeliveryReportDashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Laporan Pengiriman Bulanan';
    protected static ?string $title = 'Laporan Kinerja Pengiriman Bulanan';
    protected static string $view = 'filament.pages.monthly-delivery-report-dashboard';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup = 'Operasional';

    public ?string $selectedMonth = null;
    public ?string $selectedYear = null;
    public ?string $selectedDriver = null;
    public ?string $selectedVehicle = null;

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
                    ->label('Bulan')
                    ->options([
                        '01' => 'Januari',
                        '02' => 'Februari',
                        '03' => 'Maret',
                        '04' => 'April',
                        '05' => 'Mei',
                        '06' => 'Juni',
                        '07' => 'Juli',
                        '08' => 'Agustus',
                        '09' => 'September',
                        '10' => 'Oktober',
                        '11' => 'November',
                        '12' => 'Desember',
                    ])
                    ->default(now()->format('m'))
                    ->live(),

                Select::make('selectedYear')
                    ->label('Tahun')
                    ->options(collect(range(now()->year - 2, now()->year + 1))->mapWithKeys(fn($year) => [$year => $year]))
                    ->default(now()->format('Y'))
                    ->live(),

                Select::make('selectedDriver')
                    ->label('Sopir (Opsional)')
                    ->options(User::whereHas('jabatan', function ($query) {
                        $query->where('nama', 'like', '%driver%');
                    })->pluck('name', 'id'))
                    ->searchable()
                    ->placeholder('Semua Sopir')
                    ->live(),

                Select::make('selectedVehicle')
                    ->label('Kendaraan (Opsional)')
                    ->options(Kendaraan::pluck('no_pol_kendaraan', 'id'))
                    ->searchable()
                    ->placeholder('Semua Kendaraan')
                    ->live(),
            ])
            ->columns(4);
    }

    public function getDeliveryKpiData(): array
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "{$this->selectedYear}-{$this->selectedMonth}-01")->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $baseQuery = DB::table('delivery_order')->whereBetween('tanggal_delivery', [$startDate, $endDate]);

        if ($this->selectedDriver) {
            $baseQuery->where('id_user', $this->selectedDriver);
        }

        if ($this->selectedVehicle) {
            $baseQuery->where('id_kendaraan', $this->selectedVehicle);
        }

        $totalDeliveries = $baseQuery->count();
        $completedDeliveries = (clone $baseQuery)->where('status_muat', 'selesai')->count();
        $pendingDeliveries = (clone $baseQuery)->whereIn('status_muat', ['pending', 'muat'])->count();

        // On-time delivery calculation (assuming delivery should be completed within the scheduled date)
        $onTimeDeliveries = (clone $baseQuery)
            ->where('status_muat', 'selesai')
            ->whereRaw('DATE(waktu_selesai_muat) <= DATE(tanggal_delivery)')
            ->count();

        $completionRate = $totalDeliveries > 0 ? round(($completedDeliveries / $totalDeliveries) * 100, 1) : 0;
        $onTimeRate = $completedDeliveries > 0 ? round(($onTimeDeliveries / $completedDeliveries) * 100, 1) : 0;

        // Average delivery time (in hours)
        $avgDeliveryTime = (clone $baseQuery)
            ->where('status_muat', 'selesai')
            ->whereNotNull('waktu_muat')
            ->whereNotNull('waktu_selesai_muat')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, waktu_muat, waktu_selesai_muat)) as avg_time')
            ->value('avg_time') ?? 0;

        return [
            'total_deliveries' => $totalDeliveries,
            'completed_deliveries' => $completedDeliveries,
            'pending_deliveries' => $pendingDeliveries,
            'completion_rate' => $completionRate,
            'on_time_deliveries' => $onTimeDeliveries,
            'on_time_rate' => $onTimeRate,
            'avg_delivery_time' => round($avgDeliveryTime, 1),
        ];
    }

    public function getDriverPerformanceData(): array
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "{$this->selectedYear}-{$this->selectedMonth}-01")->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return DB::table('delivery_order')
            ->join('users', 'delivery_order.id_user', '=', 'users.id')
            ->whereBetween('delivery_order.tanggal_delivery', [$startDate, $endDate])
            ->select([
                'users.name as driver_name',
                'users.no_induk as driver_id',
                DB::raw('COUNT(delivery_order.id) as total_deliveries'),
                DB::raw('COUNT(CASE WHEN delivery_order.status_muat = "selesai" THEN 1 END) as completed_deliveries'),
                DB::raw('ROUND((COUNT(CASE WHEN delivery_order.status_muat = "selesai" THEN 1 END) / COUNT(delivery_order.id)) * 100, 1) as completion_rate'),
                DB::raw('AVG(CASE WHEN delivery_order.status_muat = "selesai" AND delivery_order.waktu_muat IS NOT NULL AND delivery_order.waktu_selesai_muat IS NOT NULL THEN TIMESTAMPDIFF(HOUR, delivery_order.waktu_muat, delivery_order.waktu_selesai_muat) END) as avg_delivery_time')
            ])
            ->groupBy('users.id', 'users.name', 'users.no_induk')
            ->orderBy('total_deliveries', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function getVehicleUtilizationData(): array
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "{$this->selectedYear}-{$this->selectedMonth}-01")->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return DB::table('delivery_order')
            ->join('kendaraans', 'delivery_order.id_kendaraan', '=', 'kendaraans.id')
            ->whereBetween('delivery_order.tanggal_delivery', [$startDate, $endDate])
            ->select([
                'kendaraans.no_pol_kendaraan as vehicle_plate',
                'kendaraans.tipe as vehicle_type',
                DB::raw('COUNT(delivery_order.id) as total_trips'),
                DB::raw('COUNT(CASE WHEN delivery_order.status_muat = "selesai" THEN 1 END) as completed_trips'),
                DB::raw('ROUND((COUNT(CASE WHEN delivery_order.status_muat = "selesai" THEN 1 END) / COUNT(delivery_order.id)) * 100, 1) as utilization_rate')
            ])
            ->groupBy('kendaraans.id', 'kendaraans.no_pol_kendaraan', 'kendaraans.tipe')
            ->orderBy('total_trips', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function getDailyDeliveryTrendData(): array
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "{$this->selectedYear}-{$this->selectedMonth}-01")->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $dailyData = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $dayDeliveries = DB::table('delivery_order')->whereDate('tanggal_delivery', $current)->count();
            $dayCompleted = DB::table('delivery_order')
                ->whereDate('tanggal_delivery', $current)
                ->where('status_muat', 'selesai')
                ->count();

            $dailyData[] = [
                'date' => $current->format('Y-m-d'),
                'day' => $current->format('d'),
                'total_deliveries' => $dayDeliveries,
                'completed_deliveries' => $dayCompleted,
            ];

            $current->addDay();
        }

        return $dailyData;
    }

    public function getDeliveryStatusDistribution(): array
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "{$this->selectedYear}-{$this->selectedMonth}-01")->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $query = DB::table('delivery_order')->whereBetween('tanggal_delivery', [$startDate, $endDate]);

        if ($this->selectedDriver) {
            $query->where('id_user', $this->selectedDriver);
        }

        if ($this->selectedVehicle) {
            $query->where('id_kendaraan', $this->selectedVehicle);
        }

        $results = $query->select([
            'status_muat',
            DB::raw('COUNT(*) as count')
        ])
            ->groupBy('status_muat')
            ->get();

        $distribution = [];
        foreach ($results as $item) {
            $label = match ($item->status_muat) {
                'pending' => 'Load Order Issued',
                'muat' => 'Load Confirmed',
                'selesai' => 'Loading Complete',
                default => ucfirst($item->status_muat),
            };
            $distribution[$label] = $item->count;
        }

        return $distribution;
    }

    public function getGeographicalDistribution(): array
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "{$this->selectedYear}-{$this->selectedMonth}-01")->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return DB::table('delivery_order')
            ->join('transaksi_penjualan', 'delivery_order.id_transaksi', '=', 'transaksi_penjualan.id')
            ->leftJoin('subdistricts', 'transaksi_penjualan.id_subdistrict', '=', 'subdistricts.id')
            ->leftJoin('districts', 'subdistricts.district_id', '=', 'districts.id')
            ->leftJoin('regencies', 'districts.regency_id', '=', 'regencies.id')
            ->whereBetween('delivery_order.tanggal_delivery', [$startDate, $endDate])
            ->select([
                DB::raw('COALESCE(regencies.name, "Unknown") as area'),
                DB::raw('COUNT(delivery_order.id) as delivery_count')
            ])
            ->groupBy('regencies.name')
            ->orderBy('delivery_count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }
}
