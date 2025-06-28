{{--
    This Blade file is for a custom infolist component.
    Location: resources/views/infolists/components/expense-amount-display.blade.php
--}}
@php
    // The $getRecord() helper gives us access to the full model record.
    $record = $getRecord();
@endphp

{{-- A grid that shows 1 column on small screens and 2 columns on medium screens and up --}}
{{-- It only uses 2 columns if the approved_amount exists and is greater than 0 --}}
<div class="grid grid-cols-1 @if($record->approved_amount > 0) md:grid-cols-2 @endif gap-6 py-4">

    <!-- Requested Amount Card -->
    <div class="flex flex-col items-center justify-center p-4">
        <div class="text-sm font-medium text-gray-500 dark:text-gray-400 tracking-wider">
            DIAJUKAN
        </div>
        <div class="flex items-start mt-1">
            <span class="text-7xl font-black tracking-tight" style="color: #f59e0b;">
                Rp&nbsp;
            </span>
            <span class="text-3xl font-semibold mt-2 mr-1" style="color: #f59e0b;">
                {{ number_format($record->requested_amount, 0, ',', '.') }}
            </span>
            
            
        </div>
    </div>

    <!-- Approved Amount Card (only shows if there is an approved amount) -->
    @if($record->approved_amount > 0)
        <div class="flex flex-col items-center justify-center p-4">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400 tracking-wider">
                DISETUJUI
            </div>
            {{-- UPDATED: Layout changed to flexbox with top alignment for the "Rp" prefix --}}
            <div class="flex items-start mt-1">
                <span class="text-7xl font-black tracking-tight" style="color: #10b981;">
                    Rp&nbsp;
                </span>
                <span class="text-3xl font-semibold mt-2 mr-1" style="color: #10b981;">
                    {{ number_format($record->approved_amount, 0, ',', '.') }}
                </span>
            </div>
        </div>
    @endif
</div>
