<?php

namespace App\Filament\Resources\DeliveryOrderResource\Pages;

use App\Filament\Resources\DeliveryOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class ViewDeliveryOrder extends ViewRecord
{
    protected static string $resource = DeliveryOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('print')
                ->label('Print')
                ->icon('heroicon-o-printer')
                ->action(function ($record) {
                    try {
                        // Generate dynamic filename
                        $filename = 'delivery_order_' . $record->id . '_' . now()->format('Ymd_His') . '.pdf';

                        // Load the PDF view with the record data
                        $pdf = Pdf::loadView('pdf.delivery_order', ['record' => $record])
                            ->setPaper('a4', 'portrait');

                        // Stream the PDF as a download
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, $filename);
                    } catch (\Exception $e) {
                        // Log the error for debugging
                        Log::error('Failed to generate PDF: ' . $e->getMessage());

                        // Notify the user of the error
                        // $this->notify('error', 'Failed to generate PDF. Please try again.');
                        return;
                    }
                }),
        ];
    }
}
