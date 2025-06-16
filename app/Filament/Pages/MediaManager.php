<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\MaxWidth;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Tables\Actions\DeleteAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Item;

class MediaManager extends Page implements HasTable, HasForms, HasActions
{
    use InteractsWithTable;
    use InteractsWithForms;
    use InteractsWithActions;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Pengelola Media';
    protected static ?string $title = 'Pengelola Media';
    protected static string $view = 'filament.pages.media-manager';
    protected static ?int $navigationSort = 10;

    public string $activeTab = 'all';
    public string $viewMode = 'grid';

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function mount(): void
    {
        $this->activeTab = request()->get('tab', 'all');
        $this->viewMode = request()->get('view', 'grid');
    }

    public function updatedActiveTab(): void
    {
        $this->resetTable();
    }

    public function updatedViewMode(): void
    {
        $this->resetTable();
    }

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Action::make('uploadMedia')
    //             ->label('Unggah Media')
    //             ->icon('heroicon-o-cloud-arrow-up')
    //             ->color('primary')
    //             ->form([
    //                 FileUpload::make('files')
    //                     ->label('Pilih File')
    //                     ->multiple()
    //                     ->acceptedFileTypes(['image/*', 'application/pdf'])
    //                     ->maxSize(10240) // 10MB
    //                     ->helperText('Unggah gambar atau file PDF (maksimal 10MB per file)')
    //                     ->required(),

    //                 Select::make('model_type')
    //                     ->label('Asosiasi dengan Model')
    //                     ->options([
    //                         'none' => 'Tanpa Asosiasi',
    //                         'user' => 'Pengguna',
    //                         'item' => 'Item',
    //                     ])
    //                     ->default('none')
    //                     ->reactive(),

    //                 Select::make('model_id')
    //                     ->label('Pilih Instance Model')
    //                     ->options(function (callable $get) {
    //                         $modelType = $get('model_type');
    //                         if ($modelType === 'user') {
    //                             return User::pluck('name', 'id');
    //                         } elseif ($modelType === 'item') {
    //                             return Item::pluck('name', 'id');
    //                         }
    //                         return [];
    //                     })
    //                     ->visible(fn(callable $get) => $get('model_type') !== 'none')
    //                     ->searchable(),

    //                 Select::make('collection')
    //                     ->label('Koleksi')
    //                     ->options(function (callable $get) {
    //                         $modelType = $get('model_type');
    //                         if ($modelType === 'user') {
    //                             return [
    //                                 'avatar' => 'Avatar',
    //                                 'documents' => 'Dokumen',
    //                             ];
    //                         } elseif ($modelType === 'item') {
    //                             return [
    //                                 'images' => 'Gambar Produk',
    //                                 'documents' => 'Dokumen',
    //                             ];
    //                         }
    //                         return ['default' => 'Default'];
    //                     })
    //                     ->default('default')
    //                     ->visible(fn(callable $get) => $get('model_type') !== 'none'),

    //                 TextInput::make('alt_text')
    //                     ->label('Teks Alt (untuk gambar)')
    //                     ->helperText('Deskripsikan gambar untuk aksesibilitas'),

    //                 Textarea::make('description')
    //                     ->label('Deskripsi')
    //                     ->rows(3)
    //                     ->helperText('Deskripsi opsional untuk file media'),
    //             ])
    //             ->action(function (array $data): void {
    //                 $this->uploadMediaFiles($data);
    //             }),
    //     ];
    // }

    protected function uploadMediaFiles(array $data): void
    {
        try {
            $files = $data['files'] ?? [];
            $modelType = $data['model_type'] ?? 'none';
            $modelId = $data['model_id'] ?? null;
            $collection = $data['collection'] ?? 'default';
            $altText = $data['alt_text'] ?? '';
            $description = $data['description'] ?? '';

            $uploadedCount = 0;

            // Handle file uploads properly
            if (!empty($files)) {
                foreach ($files as $filePath) {
                    if ($modelType !== 'none' && $modelId) {
                        // Associate with specific model
                        $model = match ($modelType) {
                            'user' => User::find($modelId),
                            'item' => Item::find($modelId),
                            default => null,
                        };

                        if ($model) {
                            $model->addMedia(Storage::disk('public')->path($filePath))
                                ->withCustomProperties([
                                    'alt_text' => $altText,
                                    'description' => $description,
                                    'uploaded_by' => auth()->user()->id,
                                ])
                                ->toMediaCollection($collection);
                            $uploadedCount++;
                        }
                    } else {
                        // Create standalone media - associate with current user
                        $user = auth()->user();
                        $user->addMedia(Storage::disk('public')->path($filePath))
                            ->withCustomProperties([
                                'alt_text' => $altText,
                                'description' => $description,
                                'uploaded_by' => auth()->user()->id,
                                'standalone' => true,
                            ])
                            ->toMediaCollection('standalone');
                        $uploadedCount++;
                    }
                }
            }

            Notification::make()
                ->title('Media berhasil diunggah')
                ->body("{$uploadedCount} file berhasil diunggah.")
                ->success()
                ->send();

            $this->resetTable();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Unggah gagal')
                ->body('Terjadi kesalahan saat mengunggah file: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function table(Table $table): Table
    {
        $baseTable = match ($this->activeTab) {
            'images' => $this->getImagesTable($table),
            'documents' => $this->getDocumentsTable($table),
            'all' => $this->getAllMediaTable($table),
            default => $this->getAllMediaTable($table),
        };

        if ($this->viewMode === 'grid') {
            return $baseTable->contentGrid([
                'md' => 2,
                // 'lg' => 3,
            ]);
        }

        return $baseTable;
    }

    protected function getAllMediaTable(Table $table): Table
    {
        return $table
            ->query(
                Media::query()
                    ->with(['model'])
                    ->latest()
            )
            ->columns($this->getTableColumns())
            ->filters($this->getTableFilters())
            ->actions($this->getTableActions())
            ->bulkActions($this->getBulkActions())
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([12, 24, 48, 96]);
    }

    protected function getImagesTable(Table $table): Table
    {
        return $table
            ->query(
                Media::query()
                    ->with(['model'])
                    ->where('mime_type', 'like', 'image/%')
                    ->latest()
            )
            ->columns($this->getTableColumns())
            ->filters($this->getTableFilters())
            ->actions($this->getTableActions())
            ->bulkActions($this->getBulkActions())
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([12, 24, 48, 96]);
    }

    protected function getDocumentsTable(Table $table): Table
    {
        return $table
            ->query(
                Media::query()
                    ->with(['model'])
                    ->where('mime_type', 'not like', 'image/%')
                    ->latest()
            )
            ->columns($this->getTableColumns())
            ->filters($this->getTableFilters())
            ->actions($this->getTableActions())
            ->bulkActions($this->getBulkActions())
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([12, 24, 48, 96]);
    }

    protected function getTableColumns(): array
    {
        if ($this->viewMode === 'grid') {
            return [
                Tables\Columns\Layout\Grid::make(3)
                    ->schema([
                        Tables\Columns\Layout\Stack::make([
                            Tables\Columns\ImageColumn::make('url')
                                ->label('')
                                ->getStateUsing(function (Media $record): string {
                                    if (str_starts_with($record->mime_type, 'image/')) {
                                        return $record->getUrl();
                                    }
                                    return asset('images/file-icon.svg');
                                })
                                ->size(150)
                                ->square(),

                            Tables\Columns\TextColumn::make('name')
                                ->label('')
                                ->weight('bold')
                                ->limit(20)
                                ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                                    return $column->getState();
                                }),

                            Tables\Columns\TextColumn::make('file_name')
                                ->label('')
                                ->size('sm')
                                ->color('gray')
                                ->limit(25)
                                ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                                    return $column->getState();
                                }),

                                Tables\Columns\TextColumn::make('mime_type')
                                ->label('')
                                ->size('sm')
                                ->color('gray')
                                ->limit(25)
                                ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                                    return $column->getState();
                                }),

                                Tables\Columns\TextColumn::make('human_readable_size')
                                ->label('')
                                ->size('sm')
                                ->color('gray')
                                ->limit(25)
                                ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                                    return $column->getState();
                                }),
                        ]),
                    ]),
            ];
        }

        // List view columns remain unchanged
        return [
            Tables\Columns\ImageColumn::make('url')
                ->label('Pratinjau')
                ->getStateUsing(function (Media $record): string {
                    if (str_starts_with($record->mime_type, 'image/')) {
                        return $record->getUrl();
                    }
                    return asset('images/file-icon.png');
                })
                ->size(60)
                ->square(),

            Tables\Columns\TextColumn::make('name')
                ->label('Nama')
                ->searchable()
                ->sortable()
                ->copyable()
                ->description(fn(Media $record): string => $record->file_name),

            Tables\Columns\TextColumn::make('mime_type')
                ->label('Tipe')
                ->badge()
                ->color(fn(string $state): string => str_starts_with($state, 'image/') ? 'success' : 'info')
                ->formatStateUsing(fn(string $state): string => strtoupper(explode('/', $state)[1] ?? $state)),

            Tables\Columns\TextColumn::make('human_readable_size')
                ->label('Ukuran')
                ->sortable(),

            Tables\Columns\TextColumn::make('collection_name')
                ->label('Koleksi')
                ->badge()
                ->color('gray')
                ->formatStateUsing(fn(string $state): string => ucfirst($state)),

            Tables\Columns\TextColumn::make('model_type')
                ->label('Model Terkait')
                ->formatStateUsing(function (?string $state): string {
                    if (!$state) return 'Tidak Ada';
                    return class_basename($state);
                })
                ->description(function (Media $record): ?string {
                    if ($record->model) {
                        return $record->model->name ?? $record->model->title ?? "ID: {$record->model->id}";
                    }
                    return null;
                }),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Diunggah')
                ->dateTime('M j, Y H:i')
                ->sortable()
                ->since()
                ->tooltip(fn(Media $record): string => $record->created_at->format('F j, Y \a\t g:i A')),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('mime_type')
                ->label('Tipe File')
                ->options([
                    'image/jpeg' => 'JPEG',
                    'image/png' => 'PNG',
                    'image/gif' => 'GIF',
                    'image/webp' => 'WebP',
                    'application/pdf' => 'PDF',
                ])
                ->multiple(),

            Tables\Filters\SelectFilter::make('collection_name')
                ->label('Koleksi')
                ->options([
                    'avatar' => 'Avatar',
                    'images' => 'Gambar Produk',
                    'documents' => 'Dokumen',
                    'standalone' => 'Mandiri',
                    'default' => 'Default',
                ])
                ->multiple(),

            Tables\Filters\SelectFilter::make('model_type')
                ->label('Model Terkait')
                ->options([
                    'App\Models\User' => 'Pengguna',
                    'App\Models\Item' => 'Item',
                ])
                ->multiple(),

            Tables\Filters\Filter::make('created_at')
                ->form([
                    \Filament\Forms\Components\DatePicker::make('from')
                        ->label('Dari Tanggal'),
                    \Filament\Forms\Components\DatePicker::make('until')
                        ->label('Sampai Tanggal'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['from'],
                            fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['until'],
                            fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        );
                }),

            Tables\Filters\Filter::make('size')
                ->form([
                    \Filament\Forms\Components\TextInput::make('min_size')
                        ->label('Ukuran Min (KB)')
                        ->numeric(),
                    \Filament\Forms\Components\TextInput::make('max_size')
                        ->label('Ukuran Maks (KB)')
                        ->numeric(),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['min_size'],
                            fn(Builder $query, $size): Builder => $query->where('size', '>=', $size * 1024),
                        )
                        ->when(
                            $data['max_size'],
                            fn(Builder $query, $size): Builder => $query->where('size', '<=', $size * 1024),
                        );
                }),
        ];
    }

    protected function getTableActions(): array
    {
        if ($this->viewMode === 'grid') {
            return [
                Tables\Actions\Action::make('view')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->url(fn(Media $record): string => $record->getUrl())
                    ->openUrlInNewTab(),

                    // edit detail in new tab
                    Tables\Actions\Action::make('edit')
                    ->label('Edit Detail')
                    ->icon('heroicon-o-pencil')
                    ->form([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required(),

                        TextInput::make('alt_text')
                            ->label('Teks Alt')
                            ->default(fn(Media $record): string => $record->getCustomProperty('alt_text', '')),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->default(fn(Media $record): string => $record->getCustomProperty('description', '')),
                    ])
                    ->fillForm(fn(Media $record): array => [
                        'name' => $record->name,
                        'alt_text' => $record->getCustomProperty('alt_text', ''),
                        'description' => $record->getCustomProperty('description', ''),
                    ])
            ];
        }

        return [
            Tables\Actions\Action::make('view')
                ->label('Lihat')
                ->icon('heroicon-o-eye')
                ->url(fn(Media $record): string => $record->getUrl())
                ->openUrlInNewTab(),

            Tables\Actions\Action::make('download')
                ->label('Unduh')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn(Media $record): string => $record->getUrl())
                ->openUrlInNewTab(),

            Tables\Actions\Action::make('edit')
                ->label('Edit Detail')
                ->icon('heroicon-o-pencil')
                ->form([
                    TextInput::make('name')
                        ->label('Nama')
                        ->required(),

                    TextInput::make('alt_text')
                        ->label('Teks Alt')
                        ->default(fn(Media $record): string => $record->getCustomProperty('alt_text', '')),

                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(3)
                        ->default(fn(Media $record): string => $record->getCustomProperty('description', '')),
                ])
                ->fillForm(fn(Media $record): array => [
                    'name' => $record->name,
                    'alt_text' => $record->getCustomProperty('alt_text', ''),
                    'description' => $record->getCustomProperty('description', ''),
                ])
                ->action(function (array $data, Media $record): void {
                    $record->update(['name' => $data['name']]);
                    $record->setCustomProperty('alt_text', $data['alt_text'] ?? '');
                    $record->setCustomProperty('description', $data['description'] ?? '');
                    $record->save();

                    Notification::make()
                        ->title('Media details updated')
                        ->success()
                        ->send();
                }),

            Tables\Actions\Action::make('regenerate')
                ->label('Regenerate Conversions')
                ->icon('heroicon-o-arrow-path')
                ->action(function (Media $record): void {
                    try {
                        $record->clearMediaConversions();
                        $record->model->registerMediaConversions();

                        Notification::make()
                            ->title('Conversions regenerated')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Failed to regenerate conversions')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn(Media $record): bool => str_starts_with($record->mime_type, 'image/')),

            Tables\Actions\DeleteAction::make()
                ->label('Delete')
                ->requiresConfirmation(),
        ];
    }

    protected function getBulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Delete Selected')
                    ->requiresConfirmation()
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                        $records->each(fn(Media $record) => $record->delete());

                        Notification::make()
                            ->title('Selected media deleted')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\BulkAction::make('regenerateConversions')
                    ->label('Regenerate Conversions')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                        $successCount = 0;
                        $errorCount = 0;

                        $records->each(function (Media $record) use (&$successCount, &$errorCount): void {
                            try {
                                if (str_starts_with($record->mime_type, 'image/')) {
                                    $record->clearMediaConversions();
                                    $record->model->registerMediaConversions();
                                    $successCount++;
                                }
                            } catch (\Exception $e) {
                                $errorCount++;
                            }
                        });

                        if ($successCount > 0) {
                            Notification::make()
                                ->title("Regenerated conversions for {$successCount} file(s)")
                                ->success()
                                ->send();
                        }

                        if ($errorCount > 0) {
                            Notification::make()
                                ->title("Failed to regenerate {$errorCount} file(s)")
                                ->warning()
                                ->send();
                        }
                    }),

                Tables\Actions\BulkAction::make('downloadSelected')
                    ->label('Download Selected')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                        // For now, just show a notification
                        // In a real implementation, you'd create a ZIP file
                        Notification::make()
                            ->title('Download feature')
                            ->body('Bulk download feature would be implemented here')
                            ->info()
                            ->send();
                    }),
            ]),
        ];
    }

    public function getMediaStats(): array
    {
        $totalMedia = Media::count();
        $totalImages = Media::where('mime_type', 'like', 'image/%')->count();
        $totalDocuments = Media::where('mime_type', 'not like', 'image/%')->count();
        $totalSize = Media::sum('size');

        return [
            'total' => $totalMedia,
            'images' => $totalImages,
            'documents' => $totalDocuments,
            'size' => $this->formatBytes($totalSize),
        ];
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}



