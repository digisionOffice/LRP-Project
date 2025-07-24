<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NumberingSettingResource\Pages;
use App\Models\NumberingSetting;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Illuminate\Support\HtmlString;
use Carbon\Carbon;

// Import all components for cleaner code
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;

class NumberingSettingResource extends Resource
{
    protected static ?string $model = NumberingSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-hashtag';
    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        // --- ADDED: Define placeholders in one place for DRY code ---
        $placeholders = [
            '{PREFIX}', '{SUFFIX}', '{YEAR}', '{YEAR_SHORT}', 
            '{MONTH}', '{MONTH_ROMAN}', '{DAY}', '{SEQUENCE}'
        ];

        return $form
            ->schema([
                Section::make('Detail Pengaturan Penomoran')
                    ->schema([
                        Placeholder::make('format_preview')
                            ->label('Contoh Hasil')
                            ->content(function (Get $get): HtmlString {
                                $preview = self::generatePreview($get('format') ?? '', $get);
                                return new HtmlString(
                                    "<div class='w-full text-center p-2 rounded-lg bg-gray-100 dark:bg-gray-800'>
                                        <span class='text-lg font-mono font-bold text-primary-500'>{$preview}</span>
                                    </div>"
                                );
                            })
                            ->columnSpanFull(),

                        Select::make('type')
                            ->label('Tipe')
                            ->options(NumberingSetting::TYPE_LABELS)
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->searchable()
                            ->helperText("Pilih entitas yang akan diatur penomorannya."),
                        
                        TextInput::make('prefix')
                            ->label('Prefix')
                            ->helperText("Contoh: 'SPH', 'EXP'.")
                            ->required()
                            ->maxLength(255)
                            ->live(debounce: 500),

                        TextInput::make('suffix')
                            ->label('Suffix (Opsional)')
                            ->helperText("Contoh: 'LRP', 'FIN'.")
                            ->maxLength(255)
                            ->live(debounce: 500),
                        
                        TextInput::make('sequence_digits')
                            ->label('Jumlah Digit Urutan')
                            ->numeric()->required()->default(4)
                            ->minValue(1)->maxValue(10)
                            ->live(debounce: 500),
                        
                        Select::make('reset_frequency')
                            ->label('Reset Urutan Setiap')
                            ->options([
                                'daily' => 'Setiap Hari',
                                'monthly' => 'Setiap Bulan',
                                'yearly' => 'Setiap Tahun',
                                'never' => 'Tidak Pernah (Lanjut Terus)',
                            ])
                            ->required(),

                        // --- UPDATED: Added a default format ---
                        TextInput::make('format')
                            ->label('Format Penomoran')
                            ->placeholder('{PREFIX}/{YEAR_SHORT}/{SEQUENCE}')
                            ->default('{SEQUENCE}/{PREFIX}/{MONTH_ROMAN}/{YEAR}')
                            ->live(debounce: 500)
                            ->required()
                            ->columnSpanFull()
                            ->helperText("Gunakan placeholder di bawah ini. Anda bisa copy-paste. Contoh: " . implode(', ', $placeholders)),

                        TextInput::make('last_sequence')
                            ->label('Urutan Terakhir')
                            ->numeric()
                            ->default(0)
                            ->readOnly(),
                        DatePicker::make('last_reset_date')
                            ->label('Tanggal Reset Terakhir')
                            ->default(now())
                            ->readOnly(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => NumberingSetting::TYPE_LABELS[$state] ?? $state),
                Tables\Columns\TextColumn::make('prefix')->label('Prefix'),
                Tables\Columns\TextColumn::make('format')->label('Format'),
                Tables\Columns\TextColumn::make('last_sequence')->label('Urutan Terakhir'),
                Tables\Columns\TextColumn::make('reset_frequency')->label('Frekuensi Reset')->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    /**
     * Helper function to generate a preview string for the form.
     */
    private static function generatePreview(string $format, Get $get): string
    {
        if (empty($format)) {
            return 'Mohon isi format...';
        }

        $now = Carbon::now();
        $sequence = 1;
        $digits = $get('sequence_digits') ?? 4;

        $replacements = [
            '{PREFIX}' => $get('prefix') ?? 'PREFIX',
            '{SUFFIX}' => $get('suffix') ?? 'SUFFIX',
            '{YEAR}' => $now->format('Y'),
            '{YEAR_SHORT}' => $now->format('y'),
            '{MONTH}' => $now->format('m'),
            '{MONTH_ROMAN}' => self::toRoman($now->month),
            '{DAY}' => $now->format('d'),
            '{SEQUENCE}' => str_pad($sequence, $digits, '0', STR_PAD_LEFT),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $format);
    }

    /**
     * Helper function to convert a number to Roman numerals for preview.
     */
    private static function toRoman($number): string
    {
        $map = ['M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1];
        $returnValue = '';
        while ($number > 0) {
            foreach ($map as $roman => $int) {
                if($number >= $int) {
                    $number -= $int;
                    $returnValue .= $roman;
                    break;
                }
            }
        }
        return $returnValue;
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
            'index' => Pages\ListNumberingSettings::route('/'),
            'create' => Pages\CreateNumberingSetting::route('/create'),
            'edit' => Pages\EditNumberingSetting::route('/{record}/edit'),
            'view' => Pages\ViewNumberingSetting::route('/{record}'),
        ];
    }    
}
