<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostingRuleResource\Pages;
use App\Models\PostingRule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PostingRuleResource extends Resource
{
    protected static ?string $model = PostingRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Aturan Posting';

    protected static ?string $modelLabel = 'Aturan Posting';

    protected static ?string $pluralModelLabel = 'Aturan Posting';

    protected static ?string $navigationGroup = 'Akuntansi';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Aturan')
                    ->schema([
                        Forms\Components\TextInput::make('rule_name')
                            ->label('Nama Aturan')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('source_type')
                            ->label('Tipe Sumber')
                            ->required()
                            ->options([
                                'Sale' => 'Penjualan',
                                'Purchase' => 'Pembelian',
                                'Payment' => 'Pembayaran',
                                'Receipt' => 'Penerimaan',
                                'ManualAdjust' => 'Penyesuaian Manual',
                            ])
                            ->native(false),
                        Forms\Components\KeyValue::make('trigger_condition')
                            ->label('Kondisi Pemicu')
                            ->keyLabel('Field')
                            ->valueLabel('Value')
                            ->addActionLabel('Tambah Kondisi'),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        Forms\Components\TextInput::make('priority')
                            ->label('Prioritas')
                            ->numeric()
                            ->default(0)
                            ->helperText('Semakin kecil angka, semakin tinggi prioritas'),
                        Forms\Components\Hidden::make('created_by')
                            ->default(auth()->id()),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail Entri Jurnal')
                    ->schema([
                        Forms\Components\Repeater::make('postingRuleEntries')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('account_id')
                                    ->label('Akun')
                                    ->relationship('account', 'nama_akun')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(2),
                                Forms\Components\Select::make('dc_type')
                                    ->label('Tipe')
                                    ->required()
                                    ->options([
                                        'Debit' => 'Debit',
                                        'Credit' => 'Credit',
                                    ])
                                    ->native(false),
                                Forms\Components\Select::make('amount_type')
                                    ->label('Tipe Jumlah')
                                    ->required()
                                    ->options([
                                        'Fixed' => 'Jumlah Tetap',
                                        'SourceValue' => 'Nilai dari Source',
                                        'Calculated' => 'Perhitungan',
                                    ])
                                    ->live()
                                    ->native(false),
                                Forms\Components\TextInput::make('fixed_amount')
                                    ->label('Jumlah Tetap')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->visible(fn (Forms\Get $get) => $get('amount_type') === 'Fixed'),
                                Forms\Components\TextInput::make('source_property')
                                    ->label('Property Source')
                                    ->visible(fn (Forms\Get $get) => $get('amount_type') === 'SourceValue')
                                    ->helperText('Contoh: total_amount, subtotal'),
                                Forms\Components\Textarea::make('calculation_expression')
                                    ->label('Ekspresi Perhitungan')
                                    ->visible(fn (Forms\Get $get) => $get('amount_type') === 'Calculated')
                                    ->helperText('Contoh: sale_items.sum(quantity * unit_cost)')
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('description_template')
                                    ->label('Template Deskripsi')
                                    ->helperText('Gunakan {source.field} untuk placeholder')
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Urutan')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->columns(3)
                            ->defaultItems(2)
                            ->addActionLabel('Tambah Entri')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rule_name')
                    ->label('Nama Aturan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('source_type')
                    ->label('Tipe Sumber')
                    ->badge(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioritas')
                    ->sortable(),
                Tables\Columns\TextColumn::make('postingRuleEntries_count')
                    ->label('Jumlah Entri')
                    ->counts('postingRuleEntries'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('source_type')
                    ->options([
                        'Sale' => 'Penjualan',
                        'Purchase' => 'Pembelian',
                        'Payment' => 'Pembayaran',
                        'Receipt' => 'Penerimaan',
                        'ManualAdjust' => 'Penyesuaian Manual',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPostingRules::route('/'),
            'create' => Pages\CreatePostingRule::route('/create'),
            'view' => Pages\ViewPostingRule::route('/{record}'),
            'edit' => Pages\EditPostingRule::route('/{record}/edit'),
        ];
    }
}
