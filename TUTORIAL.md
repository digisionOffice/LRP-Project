# Attendance System Migration Tutorial

## Prerequisites

- **PHP**: 8.1 or higher
- **Laravel**: 10.x
- **Filament**: 3.x
- **Database**: MySQL 8.0 or PostgreSQL 13+
- **Node.js**: 18.x or higher (for asset compilation)
- **Composer**: 2.x

## Step 1: Composer Dependencies

Install the required packages:

```bash
composer require spatie/laravel-permission
composer require filament/filament
```

## Step 2: Copy Files

Copy the following files to your new project with their exact destination paths:

### Database Migrations
- `database/migrations/2025_06_01_000001_create_shift_table.php`
- `database/migrations/2025_05_12_000003_create_jadwal_kerja_table.php`
- `database/migrations/2025_06_01_000002_update_jadwal_kerja_table.php`
- `database/migrations/2025_06_01_000003_create_absensi_table.php`
- `database/migrations/2025_06_01_000004_add_photo_metadata_to_absensi_table.php`
- `database/migrations/2025_06_01_000012_add_periode_to_absensi_table.php`
- `database/migrations/2025_06_01_000013_add_geolocation_to_entitas_table.php`
- `database/migrations/2025_06_01_000004_add_role_to_users_table.php`
- `database/migrations/2025_06_01_000005_add_supervisor_id_to_karyawan_table.php`

### Models
- `app/Models/Absensi.php`
- `app/Models/Schedule.php`
- `app/Models/Shift.php`
- `app/Models/Karyawan.php` (update existing)
- `app/Models/User.php` (update existing)

### Services
- `app/Services/GeofencingService.php`
- `app/Services/PhotoMetadataService.php`

### Controllers
- `app/Http/Controllers/GeofencingController.php`

### Policies
- `app/Policies/AbsensiPolicy.php`

### Filament Resources & Components
- `app/Filament/Karyawan/Resources/AbsensiResource.php`
- `app/Filament/Karyawan/Resources/AbsensiResource/Pages/` (all pages)
- `app/Filament/Karyawan/Pages/AbsensiDashboard.php`
- `app/Filament/Karyawan/Widgets/AbsensiOverview.php`
- `app/Filament/Karyawan/Widgets/RecentAttendance.php`
- `app/Filament/Karyawan/Widgets/UpcomingSchedule.php`
- `app/Filament/Widgets/AttendanceOverview.php`
- `app/Filament/Widgets/AttendanceAlertsWidget.php`
- `app/Filament/Widgets/AbsensiOverviewWidget.php`

### Frontend Assets
- `public/js/absensi-geolocation.js`
- `public/js/camera-metadata.js`
- `resources/js/geolocation.js`
- `resources/js/camera-upload.js`
- `resources/js/karyawan-geolocation.js`
- `resources/views/filament/karyawan/attendance-scripts.blade.php`

### Configuration
- `config/app_constants.php`

## Step 3: Database Migration

Run the database migrations:

```bash
php artisan migrate
```

## Step 4: Frontend Assets

### Update vite.config.js

Add the attendance JavaScript files to your Vite configuration:

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/geolocation.js',
                'resources/js/camera-upload.js',
                'resources/js/karyawan-geolocation.js',
            ],
            refresh: true,
        }),
    ],
});
```

### Compile Assets

```bash
npm install
npm run build
```

## Step 5: Update Routes

Add the following routes to `routes/web.php`:

```php
// Geofencing validation route for karyawan panel
Route::post('/karyawan/validate-geofencing', [App\Http\Controllers\GeofencingController::class, 'validateAttendanceLocation'])
    ->middleware(['auth'])
    ->name('karyawan.validate-geofencing');
```

## Step 6: Update Service Providers

### AuthServiceProvider

Add the policy mapping in `app/Providers/AuthServiceProvider.php`:

```php
protected $policies = [
    \App\Models\Absensi::class => \App\Policies\AbsensiPolicy::class,
];
```

### FilamentServiceProvider

Register the custom pages and widgets in your Filament service provider:

```php
// In your Filament panel configuration
->pages([
    \App\Filament\Karyawan\Pages\AbsensiDashboard::class,
])
->widgets([
    \App\Filament\Karyawan\Widgets\AbsensiOverview::class,
    \App\Filament\Karyawan\Widgets\RecentAttendance::class,
    \App\Filament\Karyawan\Widgets\UpcomingSchedule::class,
    \App\Filament\Widgets\AttendanceOverview::class,
    \App\Filament\Widgets\AttendanceAlertsWidget::class,
])
```

## Step 7: Storage Configuration

Ensure the storage is properly linked:

```bash
php artisan storage:link
```

Create the required directories:

```bash
mkdir -p storage/app/public/absensi/masuk
mkdir -p storage/app/public/absensi/keluar
```

## Step 8: Caching

Clear all caches and optimize:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Step 9: Permissions Setup

If using Spatie Laravel Permission, create the necessary roles:

```bash
php artisan tinker
```

```php
use Spatie\Permission\Models\Role;

Role::create(['name' => 'admin']);
Role::create(['name' => 'supervisor']);
Role::create(['name' => 'karyawan']);
```

## Step 10: Testing

1. Create test users with appropriate roles
2. Create test entities with geolocation data
3. Create test employees (karyawan) linked to users
4. Create test shifts and schedules
5. Test the attendance creation process

## Configuration Notes

### Geofencing Setup

Update your `entitas` table with location coordinates:

```sql
UPDATE entitas SET 
    latitude = -6.200000, 
    longitude = 106.816666, 
    radius = 100, 
    enable_geofencing = true 
WHERE id = 1;
```

### Environment Variables

Add to your `.env` file:

```env
FILESYSTEM_DISK=public
```

## Troubleshooting

### Common Issues

1. **GPS not working**: Ensure HTTPS is enabled for geolocation API
2. **File uploads failing**: Check storage permissions and disk configuration
3. **Widgets not showing**: Verify widget registration in Filament panel
4. **Geofencing errors**: Check entitas table has proper coordinates

### Debug Mode

Enable debug mode to see detailed error messages:

```env
APP_DEBUG=true
```

## Security Considerations

1. Ensure proper role-based access control
2. Validate all geolocation data server-side
3. Implement proper file upload validation
4. Use HTTPS in production for geolocation features

## Performance Optimization

1. Add database indexes for frequently queried fields
2. Use eager loading for relationships
3. Implement caching for geofencing calculations
4. Optimize image uploads with compression

This completes the attendance system migration. The system includes geolocation tracking, photo metadata capture, split shift support, and comprehensive attendance management features.
