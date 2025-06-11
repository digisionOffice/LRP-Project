<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Jabatan;
use App\Models\Divisi;
use App\Models\Entitas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?string $navigationLabel = 'Users & Employees';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Account Information')
                    ->description('Basic user account settings and authentication details')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('avatar')
                            ->label('Profile Picture')
                            ->collection('avatar')
                            ->image()
                            ->imageEditor()
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('300')
                            ->imageResizeTargetHeight('300')
                            ->helperText('Upload a profile picture (recommended: square image, max 2MB)')
                            ->maxSize(2048)
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Full Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Enter the user\'s full name')
                                    ->helperText('This will be displayed throughout the system')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('email')
                                    ->label('Email Address')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->placeholder('user@company.com')
                                    ->helperText('Used for login and notifications')
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('password')
                                    ->label('Password')
                                    ->password()
                                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                                    ->dehydrated(fn($state) => filled($state))
                                    ->required(fn(string $context): bool => $context === 'create')
                                    ->placeholder('Enter secure password')
                                    ->helperText(
                                        fn(string $context): string =>
                                        $context === 'create'
                                            ? 'Minimum 8 characters recommended'
                                            : 'Leave blank to keep current password'
                                    )
                                    ->minLength(8)
                                    ->columnSpan(1),

                                Forms\Components\Select::make('roles')
                                    ->label('System Role')
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->placeholder('Select user role(s)')
                                    ->helperText('Determines system permissions and access level')
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Account Status')
                            ->helperText('Enable or disable user access to the system')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->collapsible()
                    ->persistCollapsed(),

                Forms\Components\Section::make('Employee Information')
                    ->description('Employee details and organizational structure')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('no_induk')
                                    ->label('Employee ID')
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(100)
                                    ->placeholder('e.g., EMP001, SYS001')
                                    ->helperText('Unique identifier for the employee')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('hp')
                                    ->label('Phone Number')
                                    ->tel()
                                    ->maxLength(100)
                                    ->placeholder('e.g., +62 812-3456-7890')
                                    ->helperText('Contact number for the employee')
                                    ->columnSpan(1),

                                Forms\Components\Select::make('id_entitas')
                                    ->label('Entity')
                                    ->relationship('entitas', 'nama')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Select entity (optional)')
                                    ->helperText('Business entity or company branch')
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('id_jabatan')
                                    ->label('Position')
                                    ->relationship('jabatan', 'nama')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Select employee position')
                                    ->helperText('Job title or position in the organization')
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('nama')
                                            ->label('Position Name')
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->columnSpan(1),

                                Forms\Components\Select::make('id_divisi')
                                    ->label('Division')
                                    ->relationship('divisi', 'nama')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Select division')
                                    ->helperText('Department or division within the organization')
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('nama')
                                            ->label('Division Name')
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->collapsible()
                    ->persistCollapsed(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('avatar')
                    ->label('Avatar')
                    ->collection('avatar')
                    ->conversion('thumb')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl(url('/images/default-avatar.png'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('no_induk')
                    ->label('Employee ID')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Employee ID copied!')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('name')
                    ->label('Full Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Email copied!')
                    ->icon('heroicon-m-envelope'),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'admin' => 'warning',
                        'sales' => 'success',
                        'operational' => 'info',
                        'driver' => 'primary',
                        'finance' => 'secondary',
                        'administration' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'super_admin' => 'Super Admin',
                        'admin' => 'Administrator',
                        'sales' => 'Sales Manager',
                        'operational' => 'Operational Manager',
                        'driver' => 'Driver',
                        'finance' => 'Finance Manager',
                        'administration' => 'Administration Staff',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->searchable()
                    ->sortable()
                    ->separator(', '),

                Tables\Columns\TextColumn::make('jabatan.nama')
                    ->label('Position')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->placeholder('No Position')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('divisi.nama')
                    ->label('Division')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->placeholder('No Division')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('hp')
                    ->label('Phone')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Phone number copied!')
                    ->icon('heroicon-m-phone')
                    ->placeholder('No Phone')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('entitas.nama')
                    ->label('Entity')
                    ->badge()
                    ->color('warning')
                    ->placeholder('No Entity')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->placeholder('All Roles'),

                Tables\Filters\SelectFilter::make('id_jabatan')
                    ->label('Position')
                    ->relationship('jabatan', 'nama')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->placeholder('All Positions'),

                Tables\Filters\SelectFilter::make('id_divisi')
                    ->label('Division')
                    ->relationship('divisi', 'nama')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->placeholder('All Divisions'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Account Status')
                    ->placeholder('All Users')
                    ->trueLabel('Active Users')
                    ->falseLabel('Inactive Users'),

                Tables\Filters\Filter::make('has_employee_data')
                    ->label('Has Employee Data')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('no_induk'))
                    ->toggle(),

                Tables\Filters\TrashedFilter::make()
                    ->label('Deleted Users'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }
}
