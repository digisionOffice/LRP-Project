{{--
    This Blade file renders a "stamp" to show the payment status.
    Location: resources/views/infolists/components/payment-stamp.blade.php
--}}
@php
    $record = $getRecord();
    $status = $record->status;
@endphp

<div class="flex flex-col items-center justify-center p-4 space-y-2">

    @if ($status === 'paid')
        {{-- State 1: PAID (Green) --}}
        <div class="relative inline-block border-2 rounded-lg py-2 px-6 text-center" style="border-color: #10b981;">
            <div class="text-2xl font-black uppercase tracking-wider transform -rotate-12" style="color: #10b981;">
                Sudah Dibayar
            </div>
        </div>
        {{-- Payer and Date Info (shown below the stamp) --}}
        <div class="text-center text-xs text-gray-500 dark:text-gray-400">
            <div>
                Oleh: {{ $record->paidBy?->name ?? 'N/A' }}
            </div>
            @if ($record->paid_at)
                <div>
                    Pada: {{ $record->paid_at->format('d M Y, H:i') }}
                </div>
            @endif
        </div>

    @elseif ($status === 'approved')
        {{-- State 2: AWAITING PAYMENT (Warning/Orange) --}}
        <div class="relative inline-block border-2 rounded-lg py-2 px-6 text-center" style="border-color: #f59e0b;">
            <div class="text-xl font-bold uppercase tracking-wider transform -rotate-12" style="color: #f59e0b;">
                Menunggu Pembayaran
            </div>
        </div>

    @elseif ($status === 'rejected')
        {{-- State 3: REJECTED (Danger/Red) --}}
        <div class="relative inline-block border-2 rounded-lg py-2 px-6 text-center" style="border-color: #ef4444;">
            <div class="text-xl font-bold uppercase tracking-wider transform -rotate-12" style="color: #ef4444;">
                Tidak Disetujui Manager
            </div>
        </div>

    @else
        {{-- State 4: AWAITING APPROVAL (Info/Blue) --}}
        <div class="relative inline-block border-2 rounded-lg py-2 px-6 text-center" style="border-color: #3b82f6;">
            <div class="text-xl font-bold uppercase tracking-wider transform -rotate-12" style="color: #3b82f6;">
                Menunggu Approval
            </div>
        </div>
    @endif
</div>
