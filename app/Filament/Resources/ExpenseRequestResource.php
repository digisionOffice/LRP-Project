<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseRequestResource\Pages;
use App\Models\ExpenseRequest;
use App\Models\Akun;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExpenseRequestResource extends Resource
{
    protected static ?string $model = ExpenseRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Manajemen Keuangan';

    protected static ?string $navigationLabel = 'Permintaan Pengeluaran';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Permintaan')
                    ->description('Detail dasar permintaan pengeluaran')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('request_number')
                                    ->label('Nomor Permintaan')
                                    ->readOnly()
                                    ->placeholder('Dibuat otomatis')
                                    ->helperText('Akan dibuat secara otomatis saat pembuatan'),

                                Forms\Components\Select::make('category')
                                    ->label('Kategori Pengeluaran')
                                    ->options([
                                        'tank_truck_maintenance' => 'Perawatan Truk Tangki',
                                        'license_fee' => 'Biaya Lisensi',
                                        'business_travel' => 'Perjalanan Dinas',
                                        'utilities' => 'Utilitas',
                                        'other' => 'Pengeluaran Lainnya',
                                    ])
                                    ->required()
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $set('request_number', ExpenseRequest::generateRequestNumber($state));

                                            // Auto-select default account for category
                                            $defaultAccount = ExpenseRequest::getDefaultAccountForCategory($state);
                                            if ($defaultAccount) {
                                                $set('account_id', $defaultAccount->id);
                                            }
                                        }
                                    }),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('account_id')
                                    ->label('Akun Pengeluaran')
                                    ->options(function () {
                                        return Akun::where('kategori_akun', 'Beban')
                                            ->pluck('nama_akun', 'id')
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('Pilih akun yang sesuai untuk kategori pengeluaran ini'),

                                Forms\Components\Placeholder::make('account_info')
                                    ->label('Info Akun')
                                    ->content(function (callable $get) {
                                        $accountId = $get('account_id');
                                        if ($accountId) {
                                            $account = Akun::find($accountId);
                                            return $account ? "Kode: {$account->kode_akun}" : '';
                                        }
                                        return 'Pilih akun terlebih dahulu';
                                    })
                                    ->visible(fn(callable $get) => $get('account_id')),
                            ]),

                        Forms\Components\TextInput::make('title')
                            ->label('Judul Permintaan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Deskripsi singkat pengeluaran')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi Detail')
                            ->required()
                            ->rows(3)
                            ->placeholder('Berikan informasi detail tentang permintaan pengeluaran')
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('requested_amount')
                                    ->label('Jumlah Diminta (Rp)')
                                    ->required()
                                    ->numeric()
                                    // decimal
                                    ->prefix('Rp')
                                    ->placeholder('0')
                                    ->formatStateUsing(fn($state) => $state ? number_format($state, 2, ',', '.') : '')
                                    ->dehydrateStateUsing(fn($state) => (float) str_replace(['.', ','], '', $state)),

                                Forms\Components\Select::make('priority')
                                    ->label('Tingkat Prioritas')
                                    ->options([
                                        'low' => 'Rendah',
                                        'medium' => 'Sedang',
                                        'high' => 'Tinggi',
                                        'urgent' => 'Mendesak',
                                    ])
                                    ->required()
                                    ->default('medium'),

                                Forms\Components\DatePicker::make('needed_by_date')
                                    ->label('Dibutuhkan Tanggal')
                                    ->placeholder('Kapan pengeluaran ini dibutuhkan?')
                                    ->default(now())
                                // ->minDate(now())
                                ,
                                // hidden user auth id for input
                                Forms\Components\Hidden::make('user_id')
                                    ->default(auth()->id()),
                            ])
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Dokumen Pendukung')
                    ->description('Unggah dokumen pendukung untuk permintaan ini')
                    ->icon('heroicon-o-paper-clip')
                    ->schema([
                        Forms\Components\FileUpload::make('supporting_documents')
                            ->label('Dokumen Pendukung')
                            ->multiple()
                            ->disk('public')
                            ->directory('expense-requests')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'image/jpeg',
                                'image/png',
                            ])
                            ->maxSize(10240)
                            ->helperText('Unggah faktur, penawaran, atau dokumen pendukung lainnya. Maksimal 10MB per file.')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Forms\Components\Hidden::make('requested_by')
                    ->default(fn() => \Illuminate\Support\Facades\Auth::id()),

                Forms\Components\Hidden::make('requested_date')
                    ->default(now()),

                Forms\Components\Hidden::make('status')
                    ->default('submitted'),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('request_number')
                    ->label('No. Permintaan')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'tank_truck_maintenance' => 'warning',
                        'license_fee' => 'info',
                        'business_travel' => 'success',
                        'utilities' => 'primary',
                        'other' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($record) => match ($record->category) {
                        'tank_truck_maintenance' => 'Perawatan Truk Tangki',
                        'license_fee' => 'Biaya Lisensi',
                        'business_travel' => 'Perjalanan Dinas',
                        'utilities' => 'Utilitas',
                        'other' => 'Pengeluaran Lainnya',
                        default => $record->category_label ?? $record->category,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('requestedBy.name')
                    ->label('Diminta Oleh')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('account.nama_akun')
                    ->label('Akun')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('requested_amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn($record) => $record->status_color ?? 'gray')
                    ->formatStateUsing(fn($record) => match ($record->status) {
                        'draft' => 'Draft',
                        'submitted' => 'Diajukan',
                        'under_review' => 'Sedang Ditinjau',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'paid' => 'Dibayar',
                        default => $record->status_label ?? $record->status,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioritas')
                    ->badge()
                    ->color(fn($record) => $record->priority_color ?? 'gray')
                    ->formatStateUsing(fn($record) => match ($record->priority) {
                        'low' => 'Rendah',
                        'medium' => 'Sedang',
                        'high' => 'Tinggi',
                        'urgent' => 'Mendesak',
                        default => $record->priority_label ?? $record->priority,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('requested_date')
                    ->label('Diminta')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('needed_by_date')
                    ->label('Dibutuhkan')
                    ->date('d M Y')
                    ->placeholder('Tidak ditentukan')
                    ->sortable(),

                Tables\Columns\TextColumn::make('approvedBy.name')
                    ->label('Disetujui Oleh')
                    ->placeholder('Tertunda')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Tanggal Disetujui')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Belum disetujui')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'tank_truck_maintenance' => 'Perawatan Truk Tangki',
                        'license_fee' => 'Biaya Lisensi',
                        'business_travel' => 'Perjalanan Dinas',
                        'utilities' => 'Utilitas',
                        'other' => 'Pengeluaran Lainnya',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Diajukan',
                        'under_review' => 'Sedang Ditinjau',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'paid' => 'Dibayar',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('priority')
                    ->label('Prioritas')
                    ->options([
                        'low' => 'Rendah',
                        'medium' => 'Sedang',
                        'high' => 'Tinggi',
                        'urgent' => 'Mendesak',
                    ])
                    ->multiple(),

                Tables\Filters\Filter::make('my_requests')
                    ->label('Permintaan Saya')
                    ->query(fn($query) => $query->where('requested_by', \Illuminate\Support\Facades\Auth::id()))
                    ->toggle(),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->recordUrl(fn ($record) => static::getUrl('view', ['record' => $record]))
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    // Rule 1: Hide if status is 'approved' or 'rejected'
                    ->visible(fn (ExpenseRequest $record): bool => 
                        !in_array($record->status, ['approved', 'rejected', 'paid'])
                    ),
                Tables\Actions\DeleteAction::make()
                    // Rule 2: Only show if status is 'submitted'
                    ->visible(fn (ExpenseRequest $record): bool => 
                        $record->status === 'submitted'
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records) {
                            $records->filter(fn ($record) => $record->status === 'submitted')
                                    ->each(fn ($record) => $record->delete());

                            Notification::make()
                                ->title('Beberapa item telah dihapus.')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenseRequests::route('/'),
            'create' => Pages\CreateExpenseRequest::route('/create'),
            'edit' => Pages\EditExpenseRequest::route('/{record}/edit'),
            'view' => Pages\ViewExpenseRequest::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'submitted')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }
}
