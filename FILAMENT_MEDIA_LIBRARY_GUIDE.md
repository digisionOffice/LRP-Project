# Filament Spatie Media Library Integration Guide

## Overview

This guide explains how to use the Filament Spatie Media Library plugin that has been successfully installed and configured in your Laravel project.

## What's Installed

### Packages
- **spatie/laravel-medialibrary** (v11.13.0) - Core media library package
- **filament/spatie-laravel-media-library-plugin** (v3.3.21) - Filament integration

### Database
- **media** table - Stores all media files metadata
- Migration: `2025_06_11_034309_create_media_table.php`

### Environment Configuration
- `FILAMENT_FILESYSTEM_DISK=public` - Uses public disk for media storage
- Storage link created for public access

## Models with Media Support

### 1. User Model
**Collections:**
- `avatar` - Single profile picture (JPEG, PNG, GIF, WebP)
- `documents` - Multiple documents (PDF, JPEG, PNG)

**Conversions:**
- `thumb` - 150x150px for avatars
- `preview` - 300x300px for avatars

### 2. Item Model
**Collections:**
- `images` - Multiple product images (JPEG, PNG, GIF, WebP)
- `documents` - Supporting documents (PDF, JPEG, PNG)

**Conversions:**
- `thumb` - 150x150px for product images
- `preview` - 400x400px for product images
- `large` - 800x800px for product images

## Filament Resources Updated

### UserResource
- **Form:** Avatar upload field with image editor and crop functionality
- **Table:** Avatar column with circular thumbnail display

### ItemResource
- **Form:** 
  - Product images upload (max 5 files, reorderable)
  - Supporting documents upload (max 3 files)
- **Table:** Product image thumbnail column

## Usage Examples

### In Filament Forms

```php
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

// Single image upload
SpatieMediaLibraryFileUpload::make('avatar')
    ->label('Profile Picture')
    ->collection('avatar')
    ->image()
    ->imageEditor()
    ->imageCropAspectRatio('1:1')
    ->maxSize(2048),

// Multiple images with reordering
SpatieMediaLibraryFileUpload::make('images')
    ->label('Product Images')
    ->collection('images')
    ->image()
    ->multiple()
    ->reorderable()
    ->maxFiles(5)
    ->imageEditor(),

// Document upload
SpatieMediaLibraryFileUpload::make('documents')
    ->label('Documents')
    ->collection('documents')
    ->acceptedFileTypes(['application/pdf', 'image/jpeg'])
    ->multiple()
    ->maxFiles(3)
```

### In Filament Tables

```php
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

// Circular avatar
SpatieMediaLibraryImageColumn::make('avatar')
    ->label('Avatar')
    ->collection('avatar')
    ->conversion('thumb')
    ->circular()
    ->size(40),

// Product image
SpatieMediaLibraryImageColumn::make('images')
    ->label('Image')
    ->collection('images')
    ->conversion('thumb')
    ->size(50)
```

### In Blade Templates

```php
// Display single image
@if($user->getFirstMedia('avatar'))
    <img src="{{ $user->getFirstMediaUrl('avatar', 'thumb') }}" alt="Avatar">
@endif

// Display all images
@foreach($item->getMedia('images') as $image)
    <img src="{{ $image->getUrl('preview') }}" alt="Product Image">
@endforeach
```

### Programmatic Usage

```php
// Add media to a model
$user->addMediaFromRequest('avatar')
    ->toMediaCollection('avatar');

// Add with custom properties
$item->addMediaFromRequest('image')
    ->withCustomProperties(['alt' => 'Product photo'])
    ->toMediaCollection('images');

// Get media
$avatar = $user->getFirstMedia('avatar');
$images = $item->getMedia('images');

// Get URLs
$avatarUrl = $user->getFirstMediaUrl('avatar', 'thumb');
$imageUrls = $item->getMedia('images')->map(fn($media) => $media->getUrl('preview'));
```

## File Storage

- **Location:** `storage/app/public/` (accessible via `/storage/` URL)
- **Conversions:** Automatically generated and stored alongside originals
- **Naming:** Automatic UUID-based naming to prevent conflicts

## Adding Media Support to New Models

1. **Add traits and interface:**
```php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class YourModel extends Model implements HasMedia
{
    use InteractsWithMedia;
}
```

2. **Define collections:**
```php
public function registerMediaCollections(): void
{
    $this->addMediaCollection('photos')
        ->acceptsMimeTypes(['image/jpeg', 'image/png']);
}
```

3. **Define conversions:**
```php
public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('thumb')
        ->width(150)
        ->height(150)
        ->performOnCollections('photos');
}
```

## Best Practices

1. **File Size Limits:** Set appropriate `maxSize()` limits (in KB)
2. **File Types:** Use `acceptedFileTypes()` to restrict uploads
3. **Collections:** Use meaningful collection names for organization
4. **Conversions:** Create multiple sizes for different use cases
5. **Validation:** Always validate uploads in your forms
6. **Performance:** Use conversions instead of resizing on-the-fly

## Troubleshooting

### Common Issues

1. **Storage link missing:** Run `php artisan storage:link`
2. **Permission errors:** Check storage directory permissions
3. **Image processing:** Ensure GD or ImageMagick is installed
4. **File not found:** Verify `FILAMENT_FILESYSTEM_DISK` setting

### Useful Commands

```bash
# Clear media cache
php artisan media-library:clear

# Regenerate conversions
php artisan media-library:regenerate

# Check storage link
ls -la public/storage
```

## Next Steps

1. **Test the implementation** by creating/editing users and items
2. **Add media support** to other models as needed
3. **Customize conversions** based on your requirements
4. **Implement media validation** rules
5. **Add media management** features as needed

The media library is now fully integrated and ready to use in your Filament admin panel!
