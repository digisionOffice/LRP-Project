# 🚀 Cypress Quick Start Guide

## ✅ Konfigurasi Berhasil Diperbaiki!

Masalah ES modules vs CommonJS telah diselesaikan. Konfigurasi Cypress sekarang menggunakan `cypress.config.cjs` yang kompatibel dengan project ES modules.

## 📋 Langkah-langkah Menjalankan Test

### 1. Install Dependencies (Jika belum)

```bash
npm install
```

### 2. Start Laravel Server

```bash
# Terminal 1: Start Laravel server
php artisan serve
```

Pastikan server berjalan di `http://localhost:8000`

### 3. Setup Test Data (Opsional)

```bash
# Terminal 2: Setup test environment
php artisan migrate --env=testing
php artisan db:seed --env=testing
```

### 4. Jalankan Cypress Tests

#### Mode Interactive (GUI)
```bash
# Buka Cypress Test Runner
npm run cypress:open

# Atau khusus untuk journal tests
npm run test:journal:open
```

#### Mode Headless (Command Line)
```bash
# Jalankan semua E2E tests
npm run test:e2e

# Jalankan khusus journal tests
npm run test:journal

# Jalankan dengan browser spesifik
npm run cypress:run:chrome
npm run cypress:run:firefox
```

## 🔧 Troubleshooting

### Error: "Cypress could not verify that this server is running"

**Solusi:**
1. Pastikan Laravel server berjalan: `php artisan serve`
2. Cek apakah server accessible: `curl http://localhost:8000`
3. Jika menggunakan port lain, update `baseUrl` di `cypress.config.cjs`

### Error: "Cannot find module"

**Solusi:**
```bash
# Install ulang dependencies
rm -rf node_modules package-lock.json
npm install
```

### Error: Database/API endpoints

**Solusi:**
1. Pastikan file `routes/api.php` sudah dibuat
2. Pastikan controllers `TestJournalController` dan `TestAccountController` sudah dibuat
3. Jalankan: `php artisan route:list --name=test`

## 📁 File yang Sudah Dibuat

### ✅ Test Files
- `cypress/e2e/journal-crud.cy.js` - CRUD operations tests
- `cypress/e2e/journal-validation.cy.js` - Form validation tests  
- `cypress/e2e/journal-table-ux.cy.js` - Table & UX tests

### ✅ Support Files
- `cypress/support/commands.js` - Custom commands
- `cypress/support/e2e.js` - Global configuration
- `cypress/fixtures/journal-test-data.json` - Test data

### ✅ Configuration
- `cypress.config.cjs` - Main Cypress config (CommonJS)
- `cypress/tsconfig.json` - TypeScript config
- `package.json` - Updated with Cypress scripts

### ✅ Backend Support
- `app/Http/Controllers/TestJournalController.php` - Test API endpoints
- `app/Http/Controllers/TestAccountController.php` - Account test endpoints
- `routes/api.php` - Test API routes

### ✅ Documentation
- `cypress/README.md` - Detailed test suite documentation
- `CYPRESS_TESTING_GUIDE.md` - Complete testing guide
- `CYPRESS_QUICK_START.md` - This quick start guide

## 🎯 Next Steps

### 1. Tambahkan Data-Testid Attributes

Tambahkan `data-testid` attributes ke Filament components:

```php
// Di JournalResource.php
Forms\Components\DatePicker::make('transaction_date')
    ->label('Tanggal Transaksi')
    ->required()
    ->extraAttributes(['data-testid' => 'transaction-date']),

Forms\Components\TextInput::make('reference_number')
    ->label('Nomor Referensi')
    ->extraAttributes(['data-testid' => 'reference-number']),
```

### 2. Setup Test User

Buat test user di seeder:

```php
// database/seeders/DatabaseSeeder.php
User::create([
    'name' => 'Admin Test',
    'email' => 'admin@example.com',
    'password' => Hash::make('password'),
    'role' => 'superadmin'
]);
```

### 3. Test API Endpoints

Test apakah API endpoints berfungsi:

```bash
# Test create journal endpoint
curl -X POST http://localhost:8000/api/test/journals \
  -H "Content-Type: application/json" \
  -d '{"transaction_date":"2024-01-15","description":"Test"}'
```

## 🚀 Menjalankan Test Pertama

```bash
# 1. Start Laravel server
php artisan serve

# 2. Di terminal baru, jalankan test
npm run cypress:open

# 3. Pilih E2E Testing
# 4. Pilih browser (Chrome recommended)
# 5. Klik pada journal-crud.cy.js untuk menjalankan test
```

## 📊 Expected Results

Jika setup benar, Anda akan melihat:
- ✅ Cypress Test Runner terbuka
- ✅ Test files terdeteksi
- ✅ Browser terbuka dan navigasi ke aplikasi
- ⚠️ Test mungkin fail karena belum ada data-testid attributes

## 🔄 Development Workflow

1. **Tambahkan data-testid** ke components yang akan ditest
2. **Jalankan test** untuk melihat hasil
3. **Debug** jika ada yang fail
4. **Iterate** sampai semua test pass

## 📞 Support

Jika ada masalah:
1. Cek error message di Cypress Test Runner
2. Lihat browser console untuk JavaScript errors
3. Cek Laravel logs: `tail -f storage/logs/laravel.log`
4. Refer ke dokumentasi lengkap di `CYPRESS_TESTING_GUIDE.md`

---

**Happy Testing! 🎉**

*Test suite ini mencakup 25+ skenario pengujian untuk memastikan kualitas Modul Akuntansi Anda.*
