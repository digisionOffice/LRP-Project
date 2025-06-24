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

class IncomeStatement extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Laporan Laba Rugi';

    protected static ?string $title = 'Laporan Laba Rugi';

    protected static ?string $navigationGroup = 'Akuntansi';

    protected static ?int $navigationSort = 31;

    protected static string $view = 'filament.pages.income-statement';

    public ?array $data = [];
    public $start_date = null;
    public $end_date = null;

    public function mount(): void
    {
        $this->form->fill([
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
        ]);

        $this->start_date = now()->startOfMonth()->format('Y-m-d');
        $this->end_date = now()->endOfMonth()->format('Y-m-d');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Periode Laporan')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->start_date = $state;
                            }),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Akhir')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->end_date = $state;
                            }),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function getIncomeStatementData(): array
    {
        if (!$this->start_date || !$this->end_date) {
            return [
                'revenues' => [],
                'expenses' => [],
                'total_revenue' => 0,
                'total_expense' => 0,
                'net_income' => 0,
            ];
        }

        // Get revenue accounts (Pendapatan)
        $revenueAccounts = Akun::where('kategori_akun', 'Pendapatan')->get();
        $revenues = [];
        $totalRevenue = 0;

        foreach ($revenueAccounts as $account) {
            $balance = $this->getAccountBalance($account, $this->start_date, $this->end_date);
            if ($balance != 0) {
                $revenues[] = [
                    'account' => $account,
                    'balance' => $balance,
                ];
                $totalRevenue += $balance;
            }
        }

        // Get expense accounts (Beban)
        $expenseAccounts = Akun::where('kategori_akun', 'Beban')->get();
        $expenses = [];
        $totalExpense = 0;

        foreach ($expenseAccounts as $account) {
            $balance = $this->getAccountBalance($account, $this->start_date, $this->end_date);
            if ($balance != 0) {
                $expenses[] = [
                    'account' => $account,
                    'balance' => $balance,
                ];
                $totalExpense += $balance;
            }
        }

        $netIncome = $totalRevenue - $totalExpense;

        return [
            'revenues' => $revenues,
            'expenses' => $expenses,
            'total_revenue' => $totalRevenue,
            'total_expense' => $totalExpense,
            'net_income' => $netIncome,
        ];
    }

    private function getAccountBalance($account, $startDate, $endDate)
    {
        $entries = JournalEntry::where('account_id', $account->id)
            ->whereHas('journal', function (Builder $query) use ($startDate, $endDate) {
                $query->where('status', 'Posted')
                    ->whereBetween('transaction_date', [
                        Carbon::parse($startDate)->startOfDay(),
                        Carbon::parse($endDate)->endOfDay(),
                    ]);
            })
            ->get();

        $totalDebit = $entries->sum('debit');
        $totalCredit = $entries->sum('credit');

        // For revenue accounts (credit normal balance), return credit - debit
        // For expense accounts (debit normal balance), return debit - credit
        if ($account->kategori_akun === 'Pendapatan') {
            return $totalCredit - $totalDebit;
        } else {
            return $totalDebit - $totalCredit;
        }
    }
}
