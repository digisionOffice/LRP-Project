<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use App\Models\Akun;
use App\Models\JournalEntry;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class BalanceSheet extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationLabel = 'Neraca';

    protected static ?string $title = 'Laporan Neraca';

    protected static ?string $navigationGroup = 'Akuntansi';

    protected static ?int $navigationSort = 32;

    protected static string $view = 'filament.pages.balance-sheet';

    public ?array $data = [];
    public $report_date = null;

    public function mount(): void
    {
        $this->form->fill([
            'report_date' => now(),
        ]);

        $this->report_date = now()->format('Y-m-d');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Tanggal Laporan')
                    ->schema([
                        Forms\Components\DatePicker::make('report_date')
                            ->label('Tanggal Laporan')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->report_date = $state;
                            }),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function getBalanceSheetData(): array
    {
        if (!$this->report_date) {
            return [
                'assets' => [],
                'liabilities' => [],
                'equity' => [],
                'total_assets' => 0,
                'total_liabilities' => 0,
                'total_equity' => 0,
                'is_balanced' => false,
            ];
        }

        // Get asset accounts
        $assetAccounts = Akun::where('kategori_akun', 'Aset')->orderBy('kode_akun')->get();
        $assets = [];
        $totalAssets = 0;

        foreach ($assetAccounts as $account) {
            $balance = $this->getAccountBalance($account, $this->report_date);
            if ($balance != 0) {
                $assets[] = [
                    'account' => $account,
                    'balance' => $balance,
                ];
                $totalAssets += $balance;
            }
        }

        // Get liability accounts
        $liabilityAccounts = Akun::where('kategori_akun', 'Kewajiban')->orderBy('kode_akun')->get();
        $liabilities = [];
        $totalLiabilities = 0;

        foreach ($liabilityAccounts as $account) {
            $balance = $this->getAccountBalance($account, $this->report_date);
            if ($balance != 0) {
                $liabilities[] = [
                    'account' => $account,
                    'balance' => $balance,
                ];
                $totalLiabilities += $balance;
            }
        }

        // Get equity accounts
        $equityAccounts = Akun::where('kategori_akun', 'Ekuitas')->orderBy('kode_akun')->get();
        $equity = [];
        $totalEquity = 0;

        foreach ($equityAccounts as $account) {
            $balance = $this->getAccountBalance($account, $this->report_date);
            if ($balance != 0) {
                $equity[] = [
                    'account' => $account,
                    'balance' => $balance,
                ];
                $totalEquity += $balance;
            }
        }

        // Calculate retained earnings (accumulated net income)
        $retainedEarnings = $this->getRetainedEarnings($this->report_date);
        if ($retainedEarnings != 0) {
            $equity[] = [
                'account' => (object) [
                    'kode_akun' => '3999',
                    'nama_akun' => 'Laba Ditahan',
                ],
                'balance' => $retainedEarnings,
            ];
            $totalEquity += $retainedEarnings;
        }

        $isBalanced = abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01;

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'total_liabilities_equity' => $totalLiabilities + $totalEquity,
            'is_balanced' => $isBalanced,
        ];
    }

    private function getAccountBalance($account, $reportDate)
    {
        $entries = JournalEntry::where('account_id', $account->id)
            ->whereHas('journal', function (Builder $query) use ($reportDate) {
                $query->where('status', 'Posted')
                    ->where('transaction_date', '<=', Carbon::parse($reportDate)->endOfDay());
            })
            ->get();

        $totalDebit = $entries->sum('debit');
        $totalCredit = $entries->sum('credit');

        $balance = $account->saldo_awal ?? 0;

        if ($account->tipe_akun === 'Debit') {
            $balance += $totalDebit - $totalCredit;
        } else {
            $balance += $totalCredit - $totalDebit;
        }

        return $balance;
    }

    private function getRetainedEarnings($reportDate)
    {
        // Calculate accumulated net income up to report date
        $revenueAccounts = Akun::where('kategori_akun', 'Pendapatan')->get();
        $expenseAccounts = Akun::where('kategori_akun', 'Beban')->get();

        $totalRevenue = 0;
        $totalExpense = 0;

        foreach ($revenueAccounts as $account) {
            $entries = JournalEntry::where('account_id', $account->id)
                ->whereHas('journal', function (Builder $query) use ($reportDate) {
                    $query->where('status', 'Posted')
                        ->where('transaction_date', '<=', Carbon::parse($reportDate)->endOfDay());
                })
                ->get();

            $totalRevenue += $entries->sum('credit') - $entries->sum('debit');
        }

        foreach ($expenseAccounts as $account) {
            $entries = JournalEntry::where('account_id', $account->id)
                ->whereHas('journal', function (Builder $query) use ($reportDate) {
                    $query->where('status', 'Posted')
                        ->where('transaction_date', '<=', Carbon::parse($reportDate)->endOfDay());
                })
                ->get();

            $totalExpense += $entries->sum('debit') - $entries->sum('credit');
        }

        return $totalRevenue - $totalExpense;
    }
}
