<x-filament-panels::page>
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <div class="space-y-6">
        <!-- Media Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            @php
                $stats = $this->getMediaStats();
            @endphp

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-photo class="h-6 w-6 text-gray-400" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Total Media Files
                                </dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ $stats['total'] }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-camera class="h-6 w-6 text-gray-400" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Images
                                </dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ $stats['images'] }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-document class="h-6 w-6 text-gray-400" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Documents
                                </dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ $stats['documents'] }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-server class="h-6 w-6 text-gray-400" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Total Storage
                                </dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ $stats['size'] }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Tab Navigation and View Controls -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                <!-- Tab Navigation -->
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button wire:click="$set('activeTab', 'all')"
                            class="@if ($activeTab === 'all') border-primary-500 text-primary-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                            <div class="flex items-center space-x-2">
                                <x-heroicon-o-squares-2x2 class="w-5 h-5" />
                                <span>All Media</span>
                            </div>
                        </button>

                        <button wire:click="$set('activeTab', 'images')"
                            class="@if ($activeTab === 'images') border-primary-500 text-primary-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                            <div class="flex items-center space-x-2">
                                <x-heroicon-o-camera class="w-5 h-5" />
                                <span>Images</span>
                            </div>
                        </button>

                        <button wire:click="$set('activeTab', 'documents')"
                            class="@if ($activeTab === 'documents') border-primary-500 text-primary-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                            <div class="flex items-center space-x-2">
                                <x-heroicon-o-document class="w-5 h-5" />
                                <span>Documents</span>
                            </div>
                        </button>
                    </nav>
                </div>

                <!-- View Mode Controls -->
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500 dark:text-gray-400">View:</span>
                    <div class="flex rounded-md shadow-sm">
                        <button wire:click="$set('viewMode', 'grid')"
                            class="@if ($viewMode === 'grid') bg-primary-50 border-primary-500 text-primary-700 @else bg-white border-gray-300 text-gray-500 hover:text-gray-700 @endif relative inline-flex items-center px-3 py-2 rounded-l-md border text-sm font-medium focus:z-10 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                            <x-heroicon-o-squares-2x2 class="w-4 h-4" />
                        </button>
                        <button wire:click="$set('viewMode', 'list')"
                            class="@if ($viewMode === 'list') bg-primary-50 border-primary-500 text-primary-700 @else bg-white border-gray-300 text-gray-500 hover:text-gray-700 @endif relative inline-flex items-center px-3 py-2 rounded-r-md border-t border-r border-b text-sm font-medium focus:z-10 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500 -ml-px">
                            <x-heroicon-o-list-bullet class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="mt-6">
                @if ($activeTab === 'all')
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">All Media Files</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Manage all media files including images and documents.
                                    </p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    @if ($viewMode === 'grid')
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Grid View</span>
                                    @else
                                        <span class="text-xs text-gray-500 dark:text-gray-400">List View</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            @if ($viewMode === 'grid')
                                <div
                                    class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-4">
                                    {{ $this->table }}
                                </div>
                            @else
                                {{ $this->table }}
                            @endif
                        </div>
                    </div>
                @elseif($activeTab === 'images')
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Image Files</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Manage image files including photos, graphics, and other visual content.
                                    </p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    @if ($viewMode === 'grid')
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Grid View</span>
                                    @else
                                        <span class="text-xs text-gray-500 dark:text-gray-400">List View</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            @if ($viewMode === 'grid')
                                <div
                                    class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-4">
                                    {{ $this->table }}
                                </div>
                            @else
                                {{ $this->table }}
                            @endif
                        </div>
                    </div>
                @elseif($activeTab === 'documents')
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Document Files</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Manage document files including PDFs and other non-image files.
                                    </p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    @if ($viewMode === 'grid')
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Grid View</span>
                                    @else
                                        <span class="text-xs text-gray-500 dark:text-gray-400">List View</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            @if ($viewMode === 'grid')
                                <div
                                    class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-4">
                                    {{ $this->table }}
                                </div>
                            @else
                                {{ $this->table }}
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Upload Drop Zone (when no files exist) -->
        @if ($this->getMediaStats()['total'] === 0)
            <div class="text-center py-12">
                <div class="mx-auto h-12 w-12 text-gray-400">
                    <x-heroicon-o-cloud-arrow-up class="h-12 w-12" />
                </div>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No media files</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by uploading your first media file.
                </p>
                <div class="mt-6">
                    {{ ($this->uploadMediaAction)(['size' => 'lg']) }}
                </div>
            </div>
        @endif
    </div>

    @push('styles')
        <style>
            /* Custom styles for grid view */
            .media-grid-item {
                transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            }

            .media-grid-item:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }

            /* Responsive grid adjustments */
            @media (max-width: 640px) {
                .media-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
            }

            @media (min-width: 641px) and (max-width: 768px) {
                .media-grid {
                    grid-template-columns: repeat(3, 1fr);
                }
            }

            @media (min-width: 769px) and (max-width: 1024px) {
                .media-grid {
                    grid-template-columns: repeat(4, 1fr);
                }
            }

            @media (min-width: 1025px) and (max-width: 1280px) {
                .media-grid {
                    grid-template-columns: repeat(6, 1fr);
                }
            }

            @media (min-width: 1281px) {
                .media-grid {
                    grid-template-columns: repeat(8, 1fr);
                }
            }
        </style>
    @endpush
</x-filament-panels::page>
