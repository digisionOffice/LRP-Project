<?php

namespace App\Traits;

use Carbon\Carbon;

trait HasWidgetFilters
{
    /**
     * Get filtered date range for widgets
     */
    protected function getFilteredDateRange(): array
    {
        // Default to current month
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        return [
            'start' => $startOfMonth,
            'end' => $endOfMonth,
        ];
    }

    /**
     * Get filtered date range for a specific period
     */
    protected function getDateRangeForPeriod(string $period = 'month'): array
    {
        $now = Carbon::now();

        switch ($period) {
            case 'today':
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                ];
            case 'week':
                return [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek(),
                ];
            case 'month':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth(),
                ];
            case 'year':
                return [
                    'start' => $now->copy()->startOfYear(),
                    'end' => $now->copy()->endOfYear(),
                ];
            default:
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth(),
                ];
        }
    }
}
