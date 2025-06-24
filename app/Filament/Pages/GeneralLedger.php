<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use App\Models\Akun;
use App\Models\JournalEntry;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class GeneralLedger extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected $listeners = ['refreshTable' => '$refresh'];

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Buku Besar';

    protected static ?string $title = 'Laporan Buku Besar';

    protected static ?string $navigationGroup = 'Akuntansi';

    protected static string $view = 'filament.pages.general-ledger';

    protected static ?int $navigationSort = 30;

    public ?array $data = [];
    public $account_id = null;
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
                Forms\Components\Section::make('Filter Laporan')
                    ->schema([
                        Forms\Components\Select::make('account_id')
                            ->label('Pilih Akun')
                            ->options(Akun::all()->pluck('nama_akun', 'id'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->account_id = $state;
                                $this->resetTable();
                            }),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->start_date = $state;
                                $this->resetTable();
                            }),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Akhir')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->end_date = $state;
                                $this->resetTable();
                            }),
                    ])
                    ->columns(3),
            ])
            ->statePath('data');
    }

    protected function getTableQuery(): Builder
    {
        $query = JournalEntry::query()->with(['journal', 'account']);

        if (!$this->account_id || !$this->start_date || !$this->end_date) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->where('account_id', $this->account_id)
            ->whereHas('journal', function (Builder $query) {
                $query->where('status', 'Posted')
                    ->whereBetween('transaction_date', [
                        Carbon::parse($this->start_date)->startOfDay(),
                        Carbon::parse($this->end_date)->endOfDay(),
                    ]);
            });
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('journal.transaction_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('journal.journal_number')
                    ->label('No. Jurnal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('journal.reference_number')
                    ->label('Referensi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50),
                Tables\Columns\TextColumn::make('debit')
                    ->label('Debit')
                    ->money('IDR')
                    ->getStateUsing(fn($record) => $record->debit > 0 ? $record->debit : null),
                Tables\Columns\TextColumn::make('credit')
                    ->label('Credit')
                    ->money('IDR')
                    ->getStateUsing(fn($record) => $record->credit > 0 ? $record->credit : null),
            ])
            ->defaultSort('journal.transaction_date', 'asc')
            ->paginated([10, 25, 50, 100]);
    }

    public function getSelectedAccount()
    {
        return $this->account_id ? Akun::find($this->account_id) : null;
    }

    public function getAccountSummary(): array
    {
        $selectedAccount = $this->getSelectedAccount();
        if (!$selectedAccount) {
            return [];
        }

        $entries = $this->getTableQuery()->get();
        $totalDebit = $entries->sum('debit');
        $totalCredit = $entries->sum('credit');

        // Calculate opening balance
        $openingBalance = $selectedAccount->saldo_awal ?? 0;
        $entriesBeforeStartDate = JournalEntry::with(['journal', 'account'])
            ->where('account_id', $this->account_id)
            ->whereHas('journal', function (Builder $query) {
                $query->where('status', 'Posted')
                    ->where('transaction_date', '<', Carbon::parse($this->start_date)->startOfDay());
            })
            ->get();

        foreach ($entriesBeforeStartDate as $entry) {
            if ($selectedAccount->tipe_akun === 'Debit') {
                $openingBalance += $entry->debit - $entry->credit;
            } else {
                $openingBalance += $entry->credit - $entry->debit;
            }
        }

        // Calculate ending balance
        if ($selectedAccount->tipe_akun === 'Debit') {
            $endingBalance = $openingBalance + $totalDebit - $totalCredit;
        } else {
            $endingBalance = $openingBalance + $totalCredit - $totalDebit;
        }

        return [
            'account' => $selectedAccount,
            'opening_balance' => $openingBalance,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'ending_balance' => $endingBalance,
        ];
    }

    // Livewire property watchers
    // Livewire property watchers
    public function updatedAccountId()
    {
        $this->resetTable();
    }

    public function updatedStartDate()
    {
        $this->resetTable();
    }

    public function updatedEndDate()
    {
        $this->resetTable();
    }
}
