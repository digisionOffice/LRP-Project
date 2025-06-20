<?php

namespace App\Filament\Resources\AbsensiResource\Pages;

use App\Filament\Resources\AbsensiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAbsensis extends ListRecords
{
    protected static string $resource = AbsensiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge(fn () => $this->getModel()::count()),

            'today' => Tab::make('Hari Ini')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('tanggal_absensi', today()))
                ->badge(fn () => $this->getModel()::whereDate('tanggal_absensi', today())->count()),

            'this_week' => Tab::make('Minggu Ini')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('tanggal_absensi', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ]))
                ->badge(fn () => $this->getModel()::whereBetween('tanggal_absensi', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count()),

            'this_month' => Tab::make('Bulan Ini')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('tanggal_absensi', [
                    now()->startOfMonth(),
                    now()->endOfMonth()
                ]))
                ->badge(fn () => $this->getModel()::whereBetween('tanggal_absensi', [
                    now()->startOfMonth(),
                    now()->endOfMonth()
                ])->count()),

            'pending_approval' => Tab::make('Menunggu Persetujuan')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('approved_at'))
                ->badge(fn () => $this->getModel()::whereNull('approved_at')->count())
                ->badgeColor('warning'),
        ];
    }
}
