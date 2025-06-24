# Cypress Test Suite untuk Modul Akuntansi (Jurnal)

## Deskripsi

Test suite Cypress yang komprehensif untuk menguji fungsionalitas Modul Akuntansi, khususnya fitur Jurnal Umum pada aplikasi Laravel dengan Filament. Test suite ini mencakup pengujian CRUD operations, validasi form, dan interaksi user interface.

## Struktur Test Suite

### 1. File Test Utama

- **`journal-crud.cy.js`** - Pengujian operasi CRUD (Create, Read, Update, Delete)
- **`journal-validation.cy.js`** - Pengujian validasi form dan business logic
- **`journal-table-ux.cy.js`** - Pengujian interaksi tabel, pencarian, filter, dan UX

### 2. File Support

- **`commands.js`** - Custom Cypress commands untuk reusability
- **`e2e.js`** - Konfigurasi global dan setup untuk E2E testing

### 3. Konfigurasi

- **`cypress.config.js`** - Konfigurasi utama Cypress
- **`package.json`** - Dependencies dan scripts untuk menjalankan test

## Instalasi dan Setup

### 1. Install Dependencies

```bash
npm install
```

### 2. Setup Environment

Pastikan aplikasi Laravel berjalan di `http://localhost:8000` dan database test sudah dikonfigurasi.

### 3. Setup Test Data

Buat endpoint API untuk test data management di aplikasi Laravel:

```php
// routes/api.php (untuk testing)
Route::group(['prefix' => 'test', 'middleware' => 'auth'], function () {
    Route::post('journals', [TestJournalController::class, 'create']);
    Route::post('journals/batch', [TestJournalController::class, 'createBatch']);
    Route::delete('journals/clear', [TestJournalController::class, 'clear']);
    Route::post('accounts/batch', [TestAccountController::class, 'createBatch']);
});
```

## Menjalankan Test

### 1. Interactive Mode (Cypress GUI)

```bash
# Buka Cypress Test Runner
npm run cypress:open

# Buka khusus untuk test journal
npm run test:journal:open
```

### 2. Headless Mode (CI/CD)

```bash
# Jalankan semua test E2E
npm run test:e2e

# Jalankan khusus test journal
npm run test:journal

# Jalankan dengan browser spesifik
npm run cypress:run:chrome
npm run cypress:run:firefox
```

### 3. Development Mode

```bash
# Jalankan dengan headed mode untuk debugging
npm run test:e2e:headed
```

## Skenario Test yang Dicakup

### A. CRUD Operations (`journal-crud.cy.js`)

#### Create Journal
- ✅ Membuat jurnal baru dengan data valid
- ✅ Membuat jurnal dengan berbagai tipe sumber (Sale, Purchase, Payment, Receipt, ManualAdjust)
- ✅ Menambah dan menghapus entri jurnal menggunakan repeater
- ✅ Validasi auto-generate nomor jurnal

#### Read Journal
- ✅ Menampilkan daftar jurnal dengan kolom yang benar
- ✅ Melihat detail jurnal lengkap
- ✅ Verifikasi badge status dengan warna yang sesuai
- ✅ Pagination dan navigasi data

#### Update Journal
- ✅ Edit jurnal dengan status Draft
- ✅ Pembatasan edit untuk jurnal Posted
- ✅ Validasi perubahan data

#### Delete Journal
- ✅ Hapus jurnal individual
- ✅ Bulk delete dengan pembatasan status
- ✅ Konfirmasi penghapusan

### B. Form Validation (`journal-validation.cy.js`)

#### Required Fields
- ✅ Validasi field wajib (tanggal, deskripsi, akun, deskripsi entri)
- ✅ Validasi minimal 2 entri jurnal
- ✅ Clear validation errors saat field diisi

#### Data Format
- ✅ Validasi format tanggal
- ✅ Validasi numeric untuk debit/kredit
- ✅ Validasi maksimal length untuk text fields

#### Business Logic
- ✅ Validasi balance jurnal (debit = kredit)
- ✅ Prevent debit dan kredit keduanya zero
- ✅ Prevent debit dan kredit keduanya terisi
- ✅ Validasi negative amounts
- ✅ Real-time balance calculation

#### Account Selection
- ✅ Prevent duplicate account selection
- ✅ Validasi account aktif

#### Form State Management
- ✅ Preserve form data saat validation fails
- ✅ Reset form functionality
- ✅ Loading state during submission

### C. Table & UX Features (`journal-table-ux.cy.js`)

#### Search Functionality
- ✅ Search berdasarkan nomor jurnal
- ✅ Search berdasarkan nomor referensi
- ✅ Handle empty search results
- ✅ Clear search functionality
- ✅ Real-time search

#### Filter Functionality
- ✅ Filter berdasarkan status
- ✅ Filter berdasarkan source type
- ✅ Multiple filters simultaneously
- ✅ Reset filters
- ✅ Filter count dan active indicators

#### Sorting & Pagination
- ✅ Sort berdasarkan kolom
- ✅ Maintain sort saat filtering
- ✅ Pagination navigation
- ✅ Items per page
- ✅ Page information display

#### Responsive Design
- ✅ Mobile viewport adaptation
- ✅ Tablet viewport adaptation
- ✅ Touch-friendly interactions

#### UX Features
- ✅ Loading states
- ✅ Tooltips untuk truncated content
- ✅ Keyboard navigation
- ✅ Empty states dan contextual help

## Custom Commands

### Authentication
```javascript
cy.loginAs('admin@example.com') // Login sebagai user
```

### Test Data Management
```javascript
cy.createTestJournal(options)     // Buat journal test
cy.createTestJournals(count)      // Buat multiple journals
cy.clearAllJournals()             // Hapus semua test journals
cy.createTestAccounts()           // Buat accounts untuk testing
```

### Form Helpers
```javascript
cy.fillJournalForm(data)          // Isi form journal dengan data
cy.verifyJournalBalance(id)       // Verifikasi balance journal
cy.waitForTableLoad()             // Tunggu table selesai load
```

### Validation Helpers
```javascript
cy.verifyNotification(type, msg)  // Verifikasi notification
cy.shouldHaveValidationError(msg) // Assert validation error
cy.shouldHaveRowCount(count)      // Assert jumlah row table
```

## Environment Variables

```javascript
// cypress.config.js
env: {
  adminEmail: 'admin@example.com',
  adminPassword: 'password',
  apiUrl: 'http://localhost:8000/api',
  enableAccessibilityTests: true,
  testDataCleanup: true
}
```

## Best Practices

### 1. Test Data Management
- Gunakan API endpoints untuk create/cleanup test data
- Isolasi test data antar test cases
- Cleanup otomatis setelah test selesai

### 2. Selectors
- Gunakan `data-testid` attributes untuk selectors yang stabil
- Hindari selectors berdasarkan CSS classes atau text content
- Gunakan semantic selectors yang meaningful

### 3. Assertions
- Gunakan assertions yang spesifik dan meaningful
- Verifikasi state aplikasi, bukan hanya DOM elements
- Test business logic, bukan hanya UI interactions

### 4. Performance
- Gunakan `cy.intercept()` untuk mock API calls saat diperlukan
- Batch operations untuk setup test data
- Optimize waiting strategies

## Troubleshooting

### Common Issues

1. **Test timeout**: Increase `defaultCommandTimeout` di config
2. **Element not found**: Pastikan `data-testid` attributes ada di aplikasi
3. **API endpoints**: Pastikan test API endpoints sudah dibuat
4. **Authentication**: Pastikan session management bekerja dengan benar

### Debug Mode

```bash
# Jalankan dengan debug mode
npx cypress run --headed --no-exit

# Atau gunakan cy.debug() dalam test
cy.debug()
cy.pause()
```

## Integrasi CI/CD

### GitHub Actions Example

```yaml
name: E2E Tests
on: [push, pull_request]
jobs:
  cypress-run:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: cypress-io/github-action@v5
        with:
          build: npm run build
          start: npm run dev
          wait-on: 'http://localhost:8000'
          spec: 'cypress/e2e/journal-*.cy.js'
```

## Kontribusi

1. Tambahkan test cases baru sesuai dengan fitur yang dikembangkan
2. Update custom commands jika ada pattern yang berulang
3. Maintain dokumentasi test scenarios
4. Follow naming conventions yang sudah ada

## Referensi

- [Cypress Documentation](https://docs.cypress.io/)
- [Filament Testing](https://filamentphp.com/docs/3.x/panels/testing)
- [Laravel Testing](https://laravel.com/docs/testing)
