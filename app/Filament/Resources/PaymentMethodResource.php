<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentMethodResource\Pages;
use App\Models\PaymentMethod;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

// --- ADDED: Importing all components for cleaner code ---
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Metode Pembayaran';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detail Metode Pembayaran')
                    ->schema([
                        Select::make('method_name')
                            ->label('Nama Metode')
                            ->options(PaymentMethod::METHOD_NAME_LABELS) // Reads options from the model
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->searchable()
                            ->columnSpanFull(),
                            
                        TextInput::make('bank_name')
                            ->label('Nama Bank')
                            ->maxLength(255),
                        TextInput::make('account_number')
                            ->label('Nomor Rekening')
                            ->maxLength(255),
                        TextInput::make('account_name')
                            ->label('Atas Nama')
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->required(),
                        Textarea::make('notes')
                            ->label('Catatan Tambahan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('method_name')
                    ->label('Nama Metode')
                    ->searchable()
                    ->weight('bold')
                    ->formatStateUsing(fn (string $state): string => PaymentMethod::METHOD_NAME_LABELS[$state] ?? $state),
                TextColumn::make('bank_name')
                    ->label('Nama Bank')
                    ->searchable(),
                TextColumn::make('account_number')
                    ->label('Nomor Rekening')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('account_name')
                    ->label('Atas Nama')
                    ->searchable(),
                ToggleColumn::make('is_active')
                    ->label('Aktif'),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListPaymentMethods::route('/'),
            'create' => Pages\CreatePaymentMethod::route('/create'),
            'edit' => Pages\EditPaymentMethod::route('/{record}/edit'),
            'view' => Pages\ViewPaymentMethod::route('/{record}'),
        ];
    }    
}
