<?php

namespace App\Filament\Pages;

use App\Models\TransaksiPenjualan;
use App\Models\DeliveryOrder;
use App\Models\PengirimanDriver;
use App\Models\UangJalan;
use App\Models\FakturPajak;
use App\Models\Item;
use App\Models\Pelanggan;
use App\Models\Kendaraan;
use App\Models\Tbbm;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\MaxWidth;

class FuelDeliveryDashboard extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationLabel = 'Fuel Delivery Dashboard';
    protected static ?string $title = 'Fuel Delivery Dashboard';
    protected static string $view = 'filament.pages.fuel-delivery-dashboard';
    protected static ?int $navigationSort = 1;

    public string $activeTab = 'sales';

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function mount(): void
    {
        $this->activeTab = request()->get('tab', 'sales');
    }

    public function updatedActiveTab(): void
    {
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return match ($this->activeTab) {
            'sales' => $this->getSalesTable($table),
            'operations' => $this->getOperationsTable($table),
            'administration' => $this->getAdministrationTable($table),
            'driver' => $this->getDriverTable($table),
            'finance' => $this->getFinanceTable($table),
            default => $this->getSalesTable($table),
        };
    }

    protected function getSalesTable(Table $table): Table
    {
        return $table
            ->query(
                TransaksiPenjualan::query()
                    ->with([
                        'pelanggan',
                        'penjualanDetails.item.kategori',
                        'penjualanDetails.item.satuan',
                        'tbbm',
                        'subdistrict.district.regency.province'
                    ])
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('pelanggan.nama')
                    ->label('Customer Name')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('penjualanDetails.item.name')
                    ->label('Fuel Type')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(function ($record) {
                        return $record->penjualanDetails->pluck('item.name')->unique()->join(', ');
                    }),

                Tables\Columns\TextColumn::make('total_volume')
                    ->label('Fuel Volume')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' L')
                    ->getStateUsing(function ($record) {
                        return $record->penjualanDetails->sum('volume_item');
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('alamat')
                    ->label('Delivery Location')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('nomor_po')
                    ->label('PO Number')
                    ->searchable()
                    ->copyable()
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('top_pembayaran')
                    ->label('Payment Terms')
                    ->formatStateUsing(fn($state) => $state ? "{$state} days" : 'Cash')
                    ->badge()
                    ->color(fn($state) => $state > 30 ? 'warning' : 'success'),

                Tables\Columns\TextColumn::make('kode')
                    ->label('SO Number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('tbbm.nama')
                    ->label('TBBM Location')
                    ->searchable()
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Order Date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\IconColumn::make('has_attachment')
                    ->label('File')
                    ->boolean()
                    ->getStateUsing(fn($record) => $record->hasAttachment())
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn($record) => $record->hasAttachment()
                        ? 'Attachment: ' . $record->attachment_original_name
                        : 'No attachment'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id_pelanggan')
                    ->label('Customer')
                    ->options(Pelanggan::pluck('nama', 'id'))
                    ->searchable(),

                Tables\Filters\SelectFilter::make('fuel_type')
                    ->label('Fuel Type')
                    ->options(Item::whereHas('kategori', function ($query) {
                        $query->where('nama', 'like', '%BBM%');
                    })->pluck('name', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn(Builder $query, $value): Builder => $query->whereHas(
                                'penjualanDetails',
                                fn(Builder $query): Builder => $query->where('id_item', $value)
                            )
                        );
                    }),

                Tables\Filters\SelectFilter::make('id_tbbm')
                    ->label('TBBM Location')
                    ->options(Tbbm::pluck('nama', 'id'))
                    ->searchable(),

                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label('Until Date'),
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
                Tables\Actions\Action::make('view')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => route('filament.admin.resources.transaksi-penjualans.view', ['record' => $record]))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->url(fn($record) => route('filament.admin.resources.transaksi-penjualans.edit', ['record' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('tanggal', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    protected function getOperationsTable(Table $table): Table
    {
        return $table
            ->query(
                DeliveryOrder::query()
                    ->with([
                        'transaksi.pelanggan',
                        'user.jabatan',
                        'user.divisi',
                        'kendaraan'
                    ])
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('transaksi.kode')
                    ->label('SO Number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->description(fn($record) => $record->transaksi ? 'Customer: ' . $record->transaksi->pelanggan?->nama : null),

                Tables\Columns\TextColumn::make('kendaraan.no_pol_kendaraan')
                    ->label('Truck License Plate')
                    ->searchable()
                    ->copyable()
                    ->formatStateUsing(fn($state) => strtoupper($state))
                    ->placeholder('Not Assigned'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Driver Name')
                    ->searchable()
                    ->placeholder('Not Assigned')
                    ->description(fn($record) => $record->user ? 'ID: ' . $record->user->no_induk . ' | ' . $record->user->jabatan?->nama : null),

                Tables\Columns\SelectColumn::make('status_muat')
                    ->label('Loading Status')
                    ->options([
                        'pending' => 'Load Order Issued',
                        'muat' => 'Load Confirmed',
                        'selesai' => 'Loading Complete',
                    ])
                    ->selectablePlaceholder(false),

                Tables\Columns\TextColumn::make('status_muat')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'muat' => 'info',
                        'selesai' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Load Order Issued',
                        'muat' => 'Load Confirmed',
                        'selesai' => 'Loading Complete',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('tanggal_delivery')
                    ->label('Delivery Date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('waktu_muat')
                    ->label('Loading Start')
                    ->dateTime('H:i')
                    ->placeholder('Not Started'),

                Tables\Columns\TextColumn::make('waktu_selesai_muat')
                    ->label('Loading Complete')
                    ->dateTime('H:i')
                    ->placeholder('Not Completed'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_muat')
                    ->label('Loading Status')
                    ->options([
                        'pending' => 'Load Order Issued',
                        'muat' => 'Load Confirmed',
                        'selesai' => 'Loading Complete',
                    ]),

                Tables\Filters\SelectFilter::make('id_user')
                    ->label('Driver')
                    ->options(User::whereHas('jabatan', function ($query) {
                        $query->where('nama', 'like', '%driver%');
                    })->pluck('name', 'id'))
                    ->searchable(),

                Tables\Filters\SelectFilter::make('id_kendaraan')
                    ->label('Vehicle')
                    ->options(Kendaraan::pluck('no_pol_kendaraan', 'id'))
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View DO')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => route('filament.admin.resources.delivery-orders.view', ['record' => $record]))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('viewSalesOrder')
                    ->label('View SO')
                    ->icon('heroicon-o-document-text')
                    ->url(fn($record) => $record->transaksi ? route('filament.admin.resources.transaksi-penjualans.view', ['record' => $record->transaksi]) : null)
                    ->visible(fn($record) => $record->transaksi !== null)
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('updateStatus')
                    ->label('Update Status')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        \Filament\Forms\Components\Select::make('status_muat')
                            ->label('Loading Status')
                            ->options([
                                'pending' => 'Load Order Issued',
                                'muat' => 'Load Confirmed',
                                'selesai' => 'Loading Complete',
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data, $record): void {
                        $record->update($data);
                        \Filament\Notifications\Notification::make()
                            ->title('Status updated successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('tanggal_delivery', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    protected function getAdministrationTable(Table $table): Table
    {
        return $table
            ->query(
                DeliveryOrder::query()
                    ->with([
                        'transaksi.pelanggan',
                        'user.jabatan',
                        'user.divisi'
                    ])
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('transaksi.kode')
                    ->label('SO Number')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('no_segel')
                    ->label('Seal Number')
                    ->searchable()
                    ->copyable()
                    ->placeholder('Not Set'),

                Tables\Columns\TextColumn::make('do_signatory_name')
                    ->label('DO Signatory Name')
                    ->searchable()
                    ->placeholder('Not Set'),

                Tables\Columns\IconColumn::make('do_print_status')
                    ->label('DO Print Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('driver_allowance_amount')
                    ->label('Driver Allowance')
                    ->money('IDR')
                    ->placeholder('Not Set'),

                Tables\Columns\IconColumn::make('allowance_receipt_status')
                    ->label('Allowance Receipt')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('allowance_receipt_time')
                    ->label('Receipt Time')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Not Received'),

                Tables\Columns\TextColumn::make('fuel_usage_notes')
                    ->label('Fuel Usage Notes')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    })
                    ->placeholder('No Notes'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('do_print_status')
                    ->label('DO Print Status')
                    ->placeholder('All')
                    ->trueLabel('Printed')
                    ->falseLabel('Not Printed'),

                Tables\Filters\TernaryFilter::make('allowance_receipt_status')
                    ->label('Allowance Receipt Status')
                    ->placeholder('All')
                    ->trueLabel('Received')
                    ->falseLabel('Not Received'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View DO')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => route('filament.admin.resources.delivery-orders.view', ['record' => $record]))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('edit')
                    ->label('Edit DO')
                    ->icon('heroicon-o-pencil')
                    ->url(fn($record) => route('filament.admin.resources.delivery-orders.edit', ['record' => $record]))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('printDO')
                    ->label('Print DO')
                    ->icon('heroicon-o-printer')
                    ->action(function ($record): void {
                        $record->update(['do_print_status' => true]);
                        \Filament\Notifications\Notification::make()
                            ->title('DO marked as printed')
                            ->success()
                            ->send();
                    })
                    ->visible(fn($record) => !$record->do_print_status),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    protected function getDriverTable(Table $table): Table
    {
        return $table
            ->query(
                PengirimanDriver::query()
                    ->with([
                        'deliveryOrder.transaksi.pelanggan',
                        'deliveryOrder.user.jabatan',
                        'deliveryOrder.user.divisi'
                    ])
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('deliveryOrder.transaksi.kode')
                    ->label('SO Number')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('totalisator_awal')
                    ->label('Initial Totalizer')
                    ->numeric(decimalPlaces: 2)
                    ->placeholder('Not Set'),

                Tables\Columns\TextColumn::make('waktu_mulai')
                    ->label('Delivery Start Time')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('Not Started'),

                Tables\Columns\TextColumn::make('totalisator_tiba')
                    ->label('Arrival Totalizer')
                    ->numeric(decimalPlaces: 2)
                    ->placeholder('Not Set'),

                Tables\Columns\TextColumn::make('waktu_tiba')
                    ->label('Location Arrival Time')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('Not Arrived'),

                Tables\Columns\ImageColumn::make('foto_pengiriman')
                    ->label('Delivery Photo')
                    ->circular()
                    ->size(40)
                    ->placeholder('No Photo'),

                Tables\Columns\TextColumn::make('totalisator_pool_return')
                    ->label('Pool Return Totalizer')
                    ->numeric(decimalPlaces: 2)
                    ->placeholder('Not Set'),

                Tables\Columns\TextColumn::make('waktu_pool_arrival')
                    ->label('Pool Arrival Time')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('Not Returned'),

                Tables\Columns\IconColumn::make('deliveryOrder.do_handover_status')
                    ->label('DO Handover Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_photo')
                    ->label('Has Delivery Photo')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('foto_pengiriman')),

                Tables\Filters\TernaryFilter::make('deliveryOrder.do_handover_status')
                    ->label('DO Handover Status')
                    ->placeholder('All')
                    ->trueLabel('Handed Over')
                    ->falseLabel('Not Handed Over'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => route('filament.admin.resources.pengiriman-drivers.view', ['record' => $record]))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->url(fn($record) => route('filament.admin.resources.pengiriman-drivers.edit', ['record' => $record]))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('uploadPhoto')
                    ->label('Upload Photo')
                    ->icon('heroicon-o-camera')
                    ->form([
                        \Filament\Forms\Components\FileUpload::make('foto_pengiriman')
                            ->label('Delivery Photo')
                            ->image()
                            ->required(),
                    ])
                    ->action(function (array $data, $record): void {
                        $record->update($data);
                        \Filament\Notifications\Notification::make()
                            ->title('Photo uploaded successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('waktu_mulai', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    protected function getFinanceTable(Table $table): Table
    {
        return $table
            ->query(
                DeliveryOrder::query()
                    ->with([
                        'transaksi.pelanggan',
                        'transaksi.fakturPajak'
                    ])
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('transaksi.kode')
                    ->label('SO Number')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice Number')
                    ->searchable()
                    ->copyable()
                    ->placeholder('Not Generated'),

                Tables\Columns\TextColumn::make('tax_invoice_number')
                    ->label('Tax Invoice Number')
                    ->searchable()
                    ->copyable()
                    ->placeholder('Not Generated'),

                Tables\Columns\IconColumn::make('invoice_delivery_status')
                    ->label('Invoice Delivery Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\IconColumn::make('invoice_archive_status')
                    ->label('Invoice Archive Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\IconColumn::make('invoice_confirmation_status')
                    ->label('Invoice Confirmation')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('invoice_confirmation_time')
                    ->label('Confirmation Time')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Not Confirmed'),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'partial' => 'info',
                        'paid' => 'success',
                        'overdue' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'pending' => 'Pending',
                        'partial' => 'Partial',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                    ]),

                Tables\Filters\TernaryFilter::make('invoice_delivery_status')
                    ->label('Invoice Delivery Status')
                    ->placeholder('All')
                    ->trueLabel('Delivered')
                    ->falseLabel('Not Delivered'),

                Tables\Filters\TernaryFilter::make('invoice_archive_status')
                    ->label('Invoice Archive Status')
                    ->placeholder('All')
                    ->trueLabel('Archived')
                    ->falseLabel('Not Archived'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View DO')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => route('filament.admin.resources.delivery-orders.view', ['record' => $record]))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('generateInvoice')
                    ->label('Generate Invoice')
                    ->icon('heroicon-o-document-text')
                    ->action(function ($record): void {
                        // Generate invoice logic here
                        $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($record->id, 4, '0', STR_PAD_LEFT);
                        $record->update(['invoice_number' => $invoiceNumber]);

                        \Filament\Notifications\Notification::make()
                            ->title('Invoice generated successfully')
                            ->body("Invoice number: {$invoiceNumber}")
                            ->success()
                            ->send();
                    })
                    ->visible(fn($record) => !$record->invoice_number),

                Tables\Actions\Action::make('updatePaymentStatus')
                    ->label('Update Payment')
                    ->icon('heroicon-o-banknotes')
                    ->form([
                        \Filament\Forms\Components\Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'pending' => 'Pending',
                                'partial' => 'Partial',
                                'paid' => 'Paid',
                                'overdue' => 'Overdue',
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data, $record): void {
                        $record->update($data);
                        \Filament\Notifications\Notification::make()
                            ->title('Payment status updated successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
