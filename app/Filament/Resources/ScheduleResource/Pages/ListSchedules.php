<?php

namespace App\Filament\Resources\ScheduleResource\Pages;

use App\Filament\Resources\ScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSchedules extends ListRecords
{
    protected static string $resource = ScheduleResource::class;

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
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('tanggal_jadwal', today()))
                ->badge(fn () => $this->getModel()::whereDate('tanggal_jadwal', today())->count()),

            'this_week' => Tab::make('Minggu Ini')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('tanggal_jadwal', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ]))
                ->badge(fn () => $this->getModel()::whereBetween('tanggal_jadwal', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count()),

            'this_month' => Tab::make('Bulan Ini')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('tanggal_jadwal', [
                    now()->startOfMonth(),
                    now()->endOfMonth()
                ]))
                ->badge(fn () => $this->getModel()::whereBetween('tanggal_jadwal', [
                    now()->startOfMonth(),
                    now()->endOfMonth()
                ])->count()),

            'pending_approval' => Tab::make('Menunggu Persetujuan')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_approved', false))
                ->badge(fn () => $this->getModel()::where('is_approved', false)->count())
                ->badgeColor('warning'),
        ];
    }
}
