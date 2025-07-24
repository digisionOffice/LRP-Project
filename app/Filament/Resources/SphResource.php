<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SphResource\Pages;
use App\Models\Item;
use App\Models\Pelanggan;
use App\Models\Sph;
use App\Services\SphService;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

// --- Importing all components for cleaner code ---
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Fieldset;

class SphResource extends Resource
{
    protected static ?string $model = Sph::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Sales';
    protected static ?string $navigationLabel = 'SPH (Penawaran)';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make('Informasi SPH & Pelanggan')
                            ->schema([
                                TextInput::make('sph_number')
                                    ->label('Nomor SPH')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->helperText('Nomor SPH akan dibuat otomatis.'),
                                DatePicker::make('sph_date')
                                    ->label('Tanggal SPH')
                                    ->default(now())->required()->live()
                                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('valid_until_date', $state ? Carbon::parse($state)->addDays(7)->toDateString() : null)),
                                DatePicker::make('valid_until_date')
                                    ->label('Berlaku Hingga')
                                    ->default(now()->addDays(7))->required(),
                                
                                Select::make('customer_id')
                                    ->relationship('customer', 'nama', fn (Builder $query) => $query->orderBy('nama', 'asc'))
                                    ->searchable()->preload()->required()
                                    ->label('Pelanggan')->reactive()
                                    ->afterStateUpdated(function (Set $set, ?string $state) {
                                        $customer = Pelanggan::find($state);
                                        $set('opsional_pic', $customer?->pic_nama);
                                    })
                                    ->columnSpan(2),
                                
                                TextInput::make('opsional_pic')
                                    ->label('Contact Person (U.p.)')
                                    ->helperText('Isi jika berbeda dari PIC utama pelanggan.'),
                            ])->columns(3)->collapsible(),

                        Section::make('Detail Item Penawaran')
                            ->schema([
                                Repeater::make('details')
                                    // ->relationship()
                                    ->label('Item Penawaran')
                                    ->schema([
                                        Grid::make(['default' => 1, 'lg' => 12])
                                            ->schema([
                                                Select::make('item_id')->label('Item/Produk')
                                                    ->options(Item::query()->orderBy('name', 'asc')->pluck('name', 'id'))
                                                    ->searchable()->preload()->required()
                                                    ->columnSpan(['lg' => 8]),
                                                TextInput::make('quantity')->label('Kuantitas')->numeric()->required()->default(1)->live(onBlur: true)
                                                    ->columnSpan(['lg' => 4]),
                                                TextInput::make('description')->label('Deskripsi Tambahan')->columnSpanFull(),
                                                
                                                Fieldset::make('Rincian Harga per Unit')
                                                    ->schema([
                                                        ...self::getCalculationFields(),
                                                    ])->columns(3),

                                                Grid::make(2)->schema([
                                                    TextInput::make('price')->label('Harga Jual (per Unit)')->numeric()->prefix('Rp')->readOnly()->dehydrated(),
                                                    TextInput::make('subtotal')->label('Subtotal')->numeric()->prefix('Rp')->readOnly(),
                                                ])->columnSpanFull(),
                                            ]),
                                    ])
                                    ->addActionLabel('Tambah Item')
                                    ->collapsible()
                                    ->live()
                                    ->default([
                                        ['harga_dasar' => 0, 'ppn' => 0, 'oat' => 0, 'price' => 0]
                                    ]),
                            ])
                            ->collapsible(),

                    ])->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Total Penawaran')
                            ->schema([
                                Placeholder::make('grand_total')
                                    ->label('')
                                    ->content(function (Get $get): HtmlString {
                                        $total = collect($get('details'))->sum(function ($item) {
                                            $price = ($item['harga_dasar'] ?? 0) + ($item['ppn'] ?? 0) + ($item['oat'] ?? 0);
                                            return ($item['quantity'] ?? 0) * $price;
                                        });
                                        $formattedTotal = 'Rp ' . number_format($total, 2, ',', '.');
                                        return new HtmlString("<span class=\"text-2xl font-bold text-gray-700 dark:text-gray-200\">{$formattedTotal}</span>");
                                    }),
                            ]),

                        Section::make('Dokumen & Catatan')
                            ->schema([
                                // SpatieMediaLibraryFileUpload::make('dokumen_sph')->label('Unggah Dokumen')->collection('dokumen_sph')->multiple()->maxSize(10240),
                                Textarea::make('terms_and_conditions')->label('Syarat dan Ketentuan')->rows(4),
                                Textarea::make('notes_internal')->label('Catatan Internal')->rows(3),
                            ])
                            ->collapsible(),
                    ])->columnSpan(['lg' => 1]),
            ])->columns(['lg' => 3]);
    }

    /**
     * Helper function to return the calculation fields for the repeater.
     */
    protected static function getCalculationFields(): array
    {
        $updateTotals = function (Get $get, Set $set) {
            $price = (float)($get('harga_dasar') ?? 0) + (float)($get('ppn') ?? 0) + (float)($get('oat') ?? 0);
            $quantity = (float)($get('quantity') ?? 0);
            $set('price', $price);
            $set('subtotal', $price * $quantity);
        };

        return [
            TextInput::make('harga_dasar')->label('Harga Dasar')->numeric()->prefix('Rp')->required()->live(onBlur: true)->afterStateUpdated($updateTotals),
            TextInput::make('ppn')->label('PPN')->numeric()->prefix('Rp')->required()->live(onBlur: true)->afterStateUpdated($updateTotals),
            TextInput::make('oat')->label('OAT')->numeric()->prefix('Rp')->required()->live(onBlur: true)->afterStateUpdated($updateTotals),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sph_number')->label('No. SPH')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('customer.nama')->label('Pelanggan')->searchable(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                    ->color(fn (Sph $record) => $record->status_color)
                    ->formatStateUsing(fn (Sph $record) => $record->status_label),
                Tables\Columns\TextColumn::make('total_amount')->label('Total Penawaran')->money('IDR'),
                Tables\Columns\TextColumn::make('sph_date')->label('Tanggal SPH')->date('d M Y'),
                Tables\Columns\TextColumn::make('createdBy.name')->label('Dibuat Oleh'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Status')->options([
                    'draft' => 'Draft', 'pending_approval' => 'Menunggu Approval',
                    'sent' => 'Terkirim', 'accepted' => 'Diterima',
                    'rejected' => 'Ditolak', 'expired' => 'Kadaluarsa',
                ])->multiple(),
            ])
            ->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListSphs::route('/'),
            'create' => Pages\CreateSph::route('/create'),
            'edit' => Pages\EditSph::route('/{record}/edit'),
            'view' => Pages\ViewSph::route('/{record}'),
        ];
    }
}
