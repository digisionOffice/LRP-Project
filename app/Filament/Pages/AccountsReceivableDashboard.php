<?php

namespace App\Filament\Pages;

use App\Models\Pelanggan;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AccountsReceivableDashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Piutang Usaha';
    protected static ?string $title = 'Ringkasan Status Piutang Usaha';
    protected static string $view = 'filament.pages.accounts-receivable-dashboard';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationGroup = 'Laporan & Analitik';

    public ?string $selectedCustomer = null;
    public ?string $selectedPeriod = null;

    public function mount(): void
    {
        $this->selectedPeriod = 'current_month';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('selectedPeriod')
                    ->label('Periode')
                    ->options([
                        'current_month' => 'Bulan Ini',
                        'last_month' => 'Bulan Lalu',
                        'last_3_months' => '3 Bulan Terakhir',
                        'last_6_months' => '6 Bulan Terakhir',
                        'current_year' => 'Tahun Ini',
                        'all_time' => 'Semua Waktu',
                    ])
                    ->default('current_month')
                    ->live(),

                Select::make('selectedCustomer')
                    ->label('Pelanggan (Opsional)')
                    ->options(Pelanggan::pluck('nama', 'id'))
                    ->searchable()
                    ->placeholder('Semua Pelanggan')
                    ->live(),
            ])
            ->columns(2);
    }

    public function getDateRange(): array
    {
        $now = Carbon::now();

        return match ($this->selectedPeriod) {
            'current_month' => [$now->startOfMonth()->copy(), $now->endOfMonth()->copy()],
            'last_month' => [$now->subMonth()->startOfMonth()->copy(), $now->endOfMonth()->copy()],
            'last_3_months' => [$now->subMonths(3)->startOfMonth()->copy(), Carbon::now()->endOfMonth()],
            'last_6_months' => [$now->subMonths(6)->startOfMonth()->copy(), Carbon::now()->endOfMonth()],
            'current_year' => [$now->startOfYear()->copy(), $now->endOfYear()->copy()],
            'all_time' => [Carbon::createFromDate(2020, 1, 1), Carbon::now()->endOfMonth()],
            default => [$now->startOfMonth()->copy(), $now->endOfMonth()->copy()],
        };
    }

    public function getReceivableKpiData(): array
    {
        [$startDate, $endDate] = $this->getDateRange();

        $query = DB::table('delivery_order')
            ->whereBetween('tanggal_delivery', [$startDate, $endDate]);

        if ($this->selectedCustomer) {
            $query->join('transaksi_penjualan', 'delivery_order.id_transaksi', '=', 'transaksi_penjualan.id')
                ->where('transaksi_penjualan.id_pelanggan', $this->selectedCustomer);
        }

        $totalInvoices = $query->count();
        $paidInvoices = $query->clone()->where('payment_status', 'paid')->count();
        $pendingInvoices = $query->clone()->whereIn('payment_status', ['pending', 'partial'])->count();
        $overdueInvoices = $query->clone()->where('payment_status', 'overdue')->count();

        // Calculate total amounts (assuming we have amount fields)
        $totalAmount = DB::table('delivery_order')
            ->join('transaksi_penjualan', 'delivery_order.id_transaksi', '=', 'transaksi_penjualan.id')
            ->join('penjualan_detail', 'transaksi_penjualan.id', '=', 'penjualan_detail.id_transaksi_penjualan')
            ->whereBetween('delivery_order.tanggal_delivery', [$startDate, $endDate])
            ->sum(DB::raw('penjualan_detail.volume_item * penjualan_detail.harga_jual'));

        $paidAmount = DB::table('delivery_order')
            ->join('transaksi_penjualan', 'delivery_order.id_transaksi', '=', 'transaksi_penjualan.id')
            ->join('penjualan_detail', 'transaksi_penjualan.id', '=', 'penjualan_detail.id_transaksi_penjualan')
            ->whereBetween('delivery_order.tanggal_delivery', [$startDate, $endDate])
            ->where('delivery_order.payment_status', 'paid')
            ->sum(DB::raw('penjualan_detail.volume_item * penjualan_detail.harga_jual'));

        $outstandingAmount = $totalAmount - $paidAmount;

        $collectionRate = $totalAmount > 0 ? round(($paidAmount / $totalAmount) * 100, 1) : 0;

        return [
            'total_invoices' => $totalInvoices,
            'paid_invoices' => $paidInvoices,
            'pending_invoices' => $pendingInvoices,
            'overdue_invoices' => $overdueInvoices,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'outstanding_amount' => $outstandingAmount,
            'collection_rate' => $collectionRate,
        ];
    }

    public function getAgingAnalysisData(): array
    {
        [$startDate, $endDate] = $this->getDateRange();
        $now = Carbon::now();

        $receivables = DB::table('delivery_order')
            ->join('transaksi_penjualan', 'delivery_order.id_transaksi', '=', 'transaksi_penjualan.id')
            ->join('pelanggan', 'transaksi_penjualan.id_pelanggan', '=', 'pelanggan.id')
            ->join('penjualan_detail', 'transaksi_penjualan.id', '=', 'penjualan_detail.id_transaksi_penjualan')
            ->whereBetween('delivery_order.tanggal_delivery', [$startDate, $endDate])
            ->whereIn('delivery_order.payment_status', ['pending', 'partial', 'overdue'])
            ->when($this->selectedCustomer, function ($query) {
                $query->where('transaksi_penjualan.id_pelanggan', $this->selectedCustomer);
            })
            ->select([
                'delivery_order.tanggal_delivery',
                'delivery_order.payment_status',
                'transaksi_penjualan.top_pembayaran',
                'penjualan_detail.volume_item',
                'penjualan_detail.harga_jual'
            ])
            ->get();

        $aging = [
            'current' => ['count' => 0, 'amount' => 0],
            '1_30_days' => ['count' => 0, 'amount' => 0],
            '31_60_days' => ['count' => 0, 'amount' => 0],
            '61_90_days' => ['count' => 0, 'amount' => 0],
            'over_90_days' => ['count' => 0, 'amount' => 0],
        ];

        foreach ($receivables as $receivable) {
            $dueDate = Carbon::parse($receivable->tanggal_delivery)
                ->addDays($receivable->top_pembayaran ?? 0);
            $daysOverdue = $now->diffInDays($dueDate, false);

            // Calculate amount
            $amount = $receivable->volume_item * $receivable->harga_jual;

            if ($daysOverdue <= 0) {
                $aging['current']['count']++;
                $aging['current']['amount'] += $amount;
            } elseif ($daysOverdue <= 30) {
                $aging['1_30_days']['count']++;
                $aging['1_30_days']['amount'] += $amount;
            } elseif ($daysOverdue <= 60) {
                $aging['31_60_days']['count']++;
                $aging['31_60_days']['amount'] += $amount;
            } elseif ($daysOverdue <= 90) {
                $aging['61_90_days']['count']++;
                $aging['61_90_days']['amount'] += $amount;
            } else {
                $aging['over_90_days']['count']++;
                $aging['over_90_days']['amount'] += $amount;
            }
        }

        return $aging;
    }

    public function getCustomerRiskAnalysis(): array
    {
        [$startDate, $endDate] = $this->getDateRange();

        return DB::table('delivery_order')
            ->join('transaksi_penjualan', 'delivery_order.id_transaksi', '=', 'transaksi_penjualan.id')
            ->join('pelanggan', 'transaksi_penjualan.id_pelanggan', '=', 'pelanggan.id')
            ->join('penjualan_detail', 'transaksi_penjualan.id', '=', 'penjualan_detail.id_transaksi_penjualan')
            ->whereBetween('delivery_order.tanggal_delivery', [$startDate, $endDate])
            ->select([
                'pelanggan.nama as customer_name',
                DB::raw('COUNT(DISTINCT delivery_order.id) as total_invoices'),
                DB::raw('COUNT(DISTINCT CASE WHEN delivery_order.payment_status = "paid" THEN delivery_order.id END) as paid_invoices'),
                DB::raw('COUNT(DISTINCT CASE WHEN delivery_order.payment_status = "overdue" THEN delivery_order.id END) as overdue_invoices'),
                DB::raw('SUM(penjualan_detail.volume_item * penjualan_detail.harga_jual) as total_amount'),
                DB::raw('SUM(CASE WHEN delivery_order.payment_status = "paid" THEN penjualan_detail.volume_item * penjualan_detail.harga_jual ELSE 0 END) as paid_amount'),
                DB::raw('ROUND((COUNT(DISTINCT CASE WHEN delivery_order.payment_status = "paid" THEN delivery_order.id END) / COUNT(DISTINCT delivery_order.id)) * 100, 1) as payment_rate'),
                DB::raw('CASE
                    WHEN COUNT(DISTINCT CASE WHEN delivery_order.payment_status = "overdue" THEN delivery_order.id END) / COUNT(DISTINCT delivery_order.id) > 0.3 THEN "High"
                    WHEN COUNT(DISTINCT CASE WHEN delivery_order.payment_status = "overdue" THEN delivery_order.id END) / COUNT(DISTINCT delivery_order.id) > 0.1 THEN "Medium"
                    ELSE "Low"
                END as risk_level')
            ])
            ->groupBy('pelanggan.id', 'pelanggan.nama')
            ->orderBy('total_amount', 'desc')
            ->limit(15)
            ->get()
            ->toArray();
    }

    public function getPaymentTrendData(): array
    {
        [$startDate, $endDate] = $this->getDateRange();

        // Get monthly payment trends
        return DB::table('delivery_order')
            ->join('transaksi_penjualan', 'delivery_order.id_transaksi', '=', 'transaksi_penjualan.id')
            ->join('penjualan_detail', 'transaksi_penjualan.id', '=', 'penjualan_detail.id_transaksi_penjualan')
            ->whereBetween('delivery_order.tanggal_delivery', [$startDate, $endDate])
            ->select([
                DB::raw('DATE_FORMAT(delivery_order.tanggal_delivery, "%Y-%m") as month'),
                DB::raw('COUNT(DISTINCT delivery_order.id) as total_invoices'),
                DB::raw('COUNT(DISTINCT CASE WHEN delivery_order.payment_status = "paid" THEN delivery_order.id END) as paid_invoices'),
                DB::raw('SUM(penjualan_detail.volume_item * penjualan_detail.harga_jual) as total_amount'),
                DB::raw('SUM(CASE WHEN delivery_order.payment_status = "paid" THEN penjualan_detail.volume_item * penjualan_detail.harga_jual ELSE 0 END) as paid_amount')
            ])
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->toArray();
    }

    public function getTopOverdueCustomers(): array
    {
        [$startDate, $endDate] = $this->getDateRange();

        return DB::table('delivery_order')
            ->join('transaksi_penjualan', 'delivery_order.id_transaksi', '=', 'transaksi_penjualan.id')
            ->join('pelanggan', 'transaksi_penjualan.id_pelanggan', '=', 'pelanggan.id')
            ->join('penjualan_detail', 'transaksi_penjualan.id', '=', 'penjualan_detail.id_transaksi_penjualan')
            ->whereBetween('delivery_order.tanggal_delivery', [$startDate, $endDate])
            ->where('delivery_order.payment_status', 'overdue')
            ->select([
                'pelanggan.nama as customer_name',
                DB::raw('COUNT(DISTINCT delivery_order.id) as overdue_count'),
                DB::raw('SUM(penjualan_detail.volume_item * penjualan_detail.harga_jual) as overdue_amount'),
                DB::raw('MAX(DATEDIFF(NOW(), DATE_ADD(delivery_order.tanggal_delivery, INTERVAL COALESCE(transaksi_penjualan.top_pembayaran, 0) DAY))) as max_days_overdue')
            ])
            ->groupBy('pelanggan.id', 'pelanggan.nama')
            ->orderBy('overdue_amount', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }
}
