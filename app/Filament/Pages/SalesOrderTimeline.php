<?php

namespace App\Filament\Pages;

use App\Models\TransaksiPenjualan;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\MaxWidth;

class SalesOrderTimeline extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Timeline Pesanan Penjualan';
    protected static ?string $title = 'Timeline Pesanan Penjualan';
    protected static string $view = 'filament.pages.sales-order-timeline';
    protected static ?int $navigationSort = 2;
    // protected static ?string $navigationGroup = 'Manajemen Keuangan';

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TransaksiPenjualan::query()
                    ->with([
                        'pelanggan',
                        'penjualanDetails.item.kategori',
                        'penjualanDetails.item.satuan',
                        'tbbm',
                    ])
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('pelanggan.nama')
                    ->label('Nama Pelanggan')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('penjualanDetails.item.name')
                    ->label('Jenis BBM')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(function ($record) {
                        return $record->penjualanDetails->pluck('item.name')->unique()->join(', ');
                    }),

                Tables\Columns\TextColumn::make('total_volume')
                    ->label('Volume BBM')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' L')
                    ->getStateUsing(function ($record) {
                        return $record->penjualanDetails->sum('volume_item');
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('kode')
                    ->label('Nomor SO')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('tbbm.nama')
                    ->label('Lokasi TBBM')
                    ->searchable()
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal Pesanan')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id_pelanggan')
                    ->label('Pelanggan')
                    ->options(fn() => \App\Models\Pelanggan::pluck('nama', 'id'))
                    ->searchable(),

                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view_timeline')
                    ->label('Lihat Timeline')
                    ->icon('heroicon-o-clock')
                    ->url(fn(TransaksiPenjualan $record): string => "/admin/sales-order-timeline-detail?record={$record->id}")
                    ->openUrlInNewTab(false),
            ])
            ->bulkActions([])
            ->defaultSort('tanggal', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
