{{--
    This Blade file renders clickable helper buttons for the format builder.
--}}
@php
    $placeholders = [
        '{PREFIX}', '{SUFFIX}', '/', '-', '.',
        '{YEAR}', '{YEAR_SHORT}', '{MONTH}',
        '{MONTH_ROMAN}', '{DAY}', '{SEQUENCE}'
    ];
@endphp

{{-- 
    This div provides a shared context for the Alpine.js logic.
    We'll find the input field within the same "field wrapper" as this view.
--}}
<div x-data="{
    insertText(text) {
        // Find the input element. This is more robust than using an ID.
        let input = this.$el.closest('[data-field-wrapper]').previousElementSibling.querySelector('input');
        
        if (!input) return;

        let start = input.selectionStart;
        let end = input.selectionEnd;
        let currentVal = input.value;
        
        // Insert the placeholder text at the cursor's position
        input.value = currentVal.substring(0, start) + text + currentVal.substring(end);
        
        // Set the cursor position after the inserted text and dispatch an update event
        let newPos = start + text.length;
        input.setSelectionRange(newPos, newPos);
        input.focus();
        input.dispatchEvent(new Event('input'));
    }
}" class="flex flex-wrap gap-2 pt-2">
    @foreach ($placeholders as $placeholder)
        <button
            type="button"
            x-on:click="insertText('{{ $placeholder }}')"
            class="px-2 py-1 text-xs font-mono font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300"
        >
            {{ $placeholder }}
        </button>
    @endforeach
</div>
