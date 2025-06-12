<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Sales Orders</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    View sales orders and their complete delivery timeline. Click "View Timeline" to see detailed
                    progress.
                </p>
            </div>
            <div class="p-6">
                {{ $this->table }}
            </div>
        </div>
    </div>
</x-filament-panels::page>
