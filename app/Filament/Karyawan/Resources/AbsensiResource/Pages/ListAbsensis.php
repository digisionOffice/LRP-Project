<?php

namespace App\Filament\Karyawan\Resources\AbsensiResource\Pages;

use App\Filament\Karyawan\Resources\AbsensiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\Karyawan;

class ListAbsensis extends ListRecords
{
    protected static string $resource = AbsensiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => static::getResource()::canCreate()),
        ];
    }

    public function getTabs(): array
    {
        $user = Auth::user();
        $karyawan = Karyawan::where('id_user', $user->id)->first();

        if (!$karyawan) {
            return [];
        }

        return [
            'all' => Tab::make('Semua')
                ->badge(fn () => $this->getModel()::where('karyawan_id', $karyawan->id)->count()),

            'this_week' => Tab::make('Minggu Ini')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('tanggal_absensi', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ]))
                ->badge(fn () => $this->getModel()::where('karyawan_id', $karyawan->id)
                    ->whereBetween('tanggal_absensi', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ])->count()),

            'this_month' => Tab::make('Bulan Ini')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('tanggal_absensi', [
                    now()->startOfMonth(),
                    now()->endOfMonth()
                ]))
                ->badge(fn () => $this->getModel()::where('karyawan_id', $karyawan->id)
                    ->whereBetween('tanggal_absensi', [
                        now()->startOfMonth(),
                        now()->endOfMonth()
                    ])->count()),

            'pending_approval' => Tab::make('Menunggu Persetujuan')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('approved_at'))
                ->badge(fn () => $this->getModel()::where('karyawan_id', $karyawan->id)
                    ->whereNull('approved_at')->count())
                ->badgeColor('warning'),
        ];
    }
}
