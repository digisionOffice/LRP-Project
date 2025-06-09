<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseRequestResource\Pages;
use App\Models\ExpenseRequest;
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

    protected static ?string $navigationGroup = 'Financial Management';

    protected static ?string $navigationLabel = 'Expense Requests';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Request Information')
                    ->description('Basic expense request details')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('request_number')
                                    ->label('Request Number')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder('Auto-generated')
                                    ->helperText('Will be generated automatically upon creation'),

                                Forms\Components\Select::make('category')
                                    ->label('Expense Category')
                                    ->options([
                                        'tank_truck_maintenance' => 'Tank Truck Maintenance',
                                        'license_fee' => 'License Fee',
                                        'business_travel' => 'Business Travel',
                                        'utilities' => 'Utilities',
                                        'other' => 'Other Expenses',
                                    ])
                                    ->required()
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $set('request_number', ExpenseRequest::generateRequestNumber($state));
                                        }
                                    }),
                            ]),

                        Forms\Components\TextInput::make('title')
                            ->label('Request Title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Brief description of the expense')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->label('Detailed Description')
                            ->required()
                            ->rows(3)
                            ->placeholder('Provide detailed information about the expense request')
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('requested_amount')
                                    ->label('Requested Amount (Rp)')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->placeholder('0')
                                    ->formatStateUsing(fn($state) => $state ? number_format($state, 0, ',', '.') : '')
                                    ->dehydrateStateUsing(fn($state) => (float) str_replace(['.', ','], '', $state)),

                                Forms\Components\Select::make('priority')
                                    ->label('Priority Level')
                                    ->options([
                                        'low' => 'Low',
                                        'medium' => 'Medium',
                                        'high' => 'High',
                                        'urgent' => 'Urgent',
                                    ])
                                    ->required()
                                    ->default('medium'),

                                Forms\Components\DatePicker::make('needed_by_date')
                                    ->label('Needed By Date')
                                    ->placeholder('When is this expense needed?')
                                    ->minDate(now()),
                            ]),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Justification & Budget')
                    ->description('Provide justification and budget information')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        Forms\Components\Textarea::make('justification')
                            ->label('Business Justification')
                            ->rows(3)
                            ->placeholder('Explain why this expense is necessary for business operations')
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('cost_center')
                                    ->label('Cost Center')
                                    ->placeholder('e.g., Operations, Sales, Admin')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('budget_code')
                                    ->label('Budget Code')
                                    ->placeholder('e.g., MAINT-2024, TRAVEL-Q1')
                                    ->maxLength(255),
                            ]),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Supporting Documents')
                    ->description('Upload supporting documents for this request')
                    ->icon('heroicon-o-paper-clip')
                    ->schema([
                        Forms\Components\FileUpload::make('supporting_documents')
                            ->label('Supporting Documents')
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
                            ->helperText('Upload invoices, quotations, or other supporting documents. Max 10MB per file.')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Forms\Components\Hidden::make('requested_by')
                    ->default(fn() => \Illuminate\Support\Facades\Auth::id()),

                Forms\Components\Hidden::make('requested_date')
                    ->default(now()),

                Forms\Components\Hidden::make('status')
                    ->default('draft'),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('request_number')
                    ->label('Request #')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'tank_truck_maintenance' => 'warning',
                        'license_fee' => 'info',
                        'business_travel' => 'success',
                        'utilities' => 'primary',
                        'other' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($record) => $record->category_label)
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('requestedBy.name')
                    ->label('Requested By')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('requested_amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn($record) => $record->status_color)
                    ->formatStateUsing(fn($record) => $record->status_label)
                    ->sortable(),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Priority')
                    ->badge()
                    ->color(fn($record) => $record->priority_color)
                    ->formatStateUsing(fn($record) => $record->priority_label)
                    ->sortable(),

                Tables\Columns\TextColumn::make('requested_date')
                    ->label('Requested')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('needed_by_date')
                    ->label('Needed By')
                    ->date('d M Y')
                    ->placeholder('Not specified')
                    ->sortable(),

                Tables\Columns\TextColumn::make('approvedBy.name')
                    ->label('Approved By')
                    ->placeholder('Pending')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Approved Date')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Not approved')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Category')
                    ->options([
                        'tank_truck_maintenance' => 'Tank Truck Maintenance',
                        'license_fee' => 'License Fee',
                        'business_travel' => 'Business Travel',
                        'utilities' => 'Utilities',
                        'other' => 'Other Expenses',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'under_review' => 'Under Review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'paid' => 'Paid',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('priority')
                    ->label('Priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ])
                    ->multiple(),

                Tables\Filters\Filter::make('my_requests')
                    ->label('My Requests')
                    ->query(fn($query) => $query->where('requested_by', \Illuminate\Support\Facades\Auth::id()))
                    ->toggle(),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('submit')
                    ->label('Submit')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'submitted',
                            'submitted_at' => now(),
                        ]);
                    })
                    ->visible(fn($record) => $record->canBeSubmitted()),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('approved_amount')
                            ->label('Approved Amount (Rp)')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\Textarea::make('approval_notes')
                            ->label('Approval Notes')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'approved',
                            'approved_amount' => $data['approved_amount'],
                            'approval_notes' => $data['approval_notes'],
                            'approved_by' => \Illuminate\Support\Facades\Auth::id(),
                            'approved_at' => now(),
                        ]);
                    })
                    ->visible(fn($record) => $record->canBeApproved()),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                            'approved_by' => \Illuminate\Support\Facades\Auth::id(),
                            'reviewed_at' => now(),
                        ]);
                    })
                    ->visible(fn($record) => $record->canBeRejected()),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => $record->isEditable()),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
