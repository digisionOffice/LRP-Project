<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IsoCertificationResource\Pages;
use App\Models\IsoCertification;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;

// Import components for cleaner code
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\Action;

class IsoCertificationResource extends Resource
{
    protected static ?string $model = IsoCertification::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Sertifikasi ISO';

    public static function form(Form $form): Form
    {
        // --- ADDED: Helper to generate a range of years for the dropdowns ---
        $yearRange = range(now()->year - 20, now()->year + 40);
        $yearOptions = array_combine($yearRange, $yearRange);

        return $form
            ->schema([
                Section::make('Detail Sertifikasi ISO')
                    ->schema([
                        FileUpload::make('logo_path')
                            ->label('Logo Sertifikasi')
                            ->disk('public')
                            ->directory('iso-certifications')
                            ->image()
                            ->required()
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
                                $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                                $sluggedFileName = Str::slug($fileName, '-');
                                return "{$sluggedFileName}-" . now()->timestamp . ".{$file->getClientOriginalExtension()}";
                            })
                            ->columnSpanFull(),
                        
                        Select::make('name')
                            ->label('Nama Sertifikat')
                            ->options(IsoCertification::CERTIFICATE_NAMES) // Assumes this constant exists on your model
                            ->required()
                            ->searchable(),

                        TextInput::make('certificate_number')
                            ->label('Nomor Sertifikat')
                            ->helperText("Contoh: 'GMS16290027'")
                            ->maxLength(255),

                        // --- UPDATED: Replaced TextInput with a Select for a better UX ---
                        Select::make('active_year')
                            ->label('Tahun Aktif')
                            ->options($yearOptions)
                            ->default(now()->year)
                            ->required()
                            ->searchable(),

                        // --- UPDATED: Replaced TextInput with a Select for a better UX ---
                        Select::make('end_year')
                            ->label('Tahun Berakhir')
                            ->options($yearOptions)
                            ->default(now()->year + 1)
                            ->required()
                            ->searchable(),
                            
                        Toggle::make('is_active')
                            ->label('Tampilkan di Dokumen')
                            ->default(true)
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->disk('public')
                    ->action(
                        Action::make('view_logo')
                            ->label('Lihat Logo')
                            ->modalContent(fn (IsoCertification $record): HtmlString => new HtmlString("<img src='{$record->logo_url}' alt='Logo' />"))
                            ->modalSubmitAction(false)
                            ->modalCancelAction(false)
                    ),
                TextColumn::make('name')
                    ->label('Nama Sertifikat')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('certificate_number')
                    ->label('Nomor Sertifikat')
                    ->searchable(),
                TextColumn::make('active_year')
                    ->label('Periode')
                    ->formatStateUsing(fn ($record) => "{$record->active_year} - {$record->end_year}"),
                ToggleColumn::make('is_active')
                    ->label('Aktif'),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIsoCertifications::route('/'),
            'create' => Pages\CreateIsoCertification::route('/create'),
            'edit' => Pages\EditIsoCertification::route('/{record}/edit'),
            'view' => Pages\ViewIsoCertification::route('/{record}'),
        ];
    }    
}
