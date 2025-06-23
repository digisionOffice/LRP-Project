# Panduan Lengkap Testing Cypress untuk Modul Akuntansi

## 📋 Ringkasan Test Suite

Test suite Cypress ini dirancang khusus untuk menguji **Modul Akuntansi (Jurnal Umum)** pada aplikasi Laravel dengan Filament. Test suite mencakup 3 kategori utama pengujian:

### 🎯 Cakupan Testing

1. **CRUD Operations** - Pengujian Create, Read, Update, Delete jurnal
2. **Form Validation** - Pengujian validasi form dan business logic
3. **Table & UX** - Pengujian interaksi tabel, pencarian, filter, dan user experience

### 📊 Statistik Test Cases

-   **Total Test Files**: 3 file utama
-   **Total Test Cases**: 25+ skenario pengujian
-   **Coverage**: CRUD, Validasi, UX, Accessibility
-   **Browser Support**: Chrome, Firefox, Edge

## 🚀 Quick Start

### 1. Instalasi Dependencies

```bash
# Install semua dependencies
npm install

# Atau install Cypress secara terpisah
npm install --save-dev cypress cypress-axe cypress-terminal-report
```

### 2. Setup Environment

```bash
# Pastikan Laravel app berjalan di terminal pertama
php artisan serve

# Di terminal kedua, setup database testing
php artisan migrate --env=testing
php artisan db:seed --env=testing

# Pastikan server berjalan di http://localhost:8000
curl http://localhost:8000
```

### 3. Jalankan Test

```bash
# Mode interaktif (GUI)
npm run cypress:open

# Mode headless (CI/CD)
npm run test:e2e

# Test khusus journal module
npm run test:journal
```

## 📁 Struktur File Test

```
cypress/
├── e2e/
│   ├── journal-crud.cy.js          # Test CRUD operations
│   ├── journal-validation.cy.js    # Test form validation
│   └── journal-table-ux.cy.js      # Test table & UX features
├── fixtures/
│   └── journal-test-data.json      # Test data fixtures
├── support/
│   ├── commands.js                 # Custom Cypress commands
│   └── e2e.js                      # Global configuration
├── README.md                       # Dokumentasi test suite
└── cypress.config.js               # Konfigurasi Cypress
```

## 🧪 Detail Test Scenarios

### A. CRUD Operations (`journal-crud.cy.js`)

#### ✅ Create Journal

-   [x] Membuat jurnal baru dengan data valid
-   [x] Membuat jurnal dengan berbagai source types
-   [x] Menambah/mengurangi entri jurnal dengan repeater
-   [x] Validasi auto-generate nomor jurnal

#### ✅ Read Journal

-   [x] Menampilkan daftar jurnal dengan kolom yang benar
-   [x] Melihat detail jurnal lengkap
-   [x] Verifikasi badge status dengan warna yang sesuai

#### ✅ Update Journal

-   [x] Edit jurnal dengan status Draft
-   [x] Pembatasan edit untuk jurnal Posted
-   [x] Validasi perubahan data

#### ✅ Delete Journal

-   [x] Hapus jurnal individual
-   [x] Bulk delete dengan pembatasan status

### B. Form Validation (`journal-validation.cy.js`)

#### ✅ Required Fields

-   [x] Validasi field wajib (tanggal, deskripsi, akun)
-   [x] Validasi minimal 2 entri jurnal
-   [x] Clear validation errors saat field diisi

#### ✅ Business Logic

-   [x] Validasi balance jurnal (debit = kredit)
-   [x] Prevent debit dan kredit keduanya zero/filled
-   [x] Real-time balance calculation
-   [x] Validasi negative amounts

#### ✅ Form State Management

-   [x] Preserve form data saat validation fails
-   [x] Reset form functionality
-   [x] Loading state during submission

### C. Table & UX (`journal-table-ux.cy.js`)

#### ✅ Search & Filter

-   [x] Search berdasarkan nomor jurnal/referensi
-   [x] Filter berdasarkan status dan source type
-   [x] Multiple filters simultaneously
-   [x] Real-time search

#### ✅ Sorting & Pagination

-   [x] Sort berdasarkan kolom
-   [x] Pagination navigation
-   [x] Items per page configuration

#### ✅ Responsive Design

-   [x] Mobile viewport adaptation
-   [x] Tablet viewport adaptation
-   [x] Touch-friendly interactions

## 🛠️ Custom Commands

### Authentication

```javascript
cy.loginAs("admin@example.com");
```

### Test Data Management

```javascript
cy.createTestJournal(options); // Buat journal test
cy.createTestJournals(count); // Buat multiple journals
cy.clearAllJournals(); // Hapus semua test journals
cy.createTestAccounts(); // Buat accounts untuk testing
```

### Form Helpers

```javascript
cy.fillJournalForm(data); // Isi form journal
cy.verifyJournalBalance(id); // Verifikasi balance
cy.waitForTableLoad(); // Tunggu table load
```

### Validation Helpers

```javascript
cy.verifyNotification(type, msg); // Verifikasi notification
cy.shouldHaveValidationError(msg); // Assert validation error
cy.shouldHaveRowCount(count); // Assert jumlah row
```

## ⚙️ Konfigurasi Environment

### Environment Variables

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

### Browser Configuration

```javascript
// Untuk Chrome
launchOptions.args.push("--disable-web-security");
launchOptions.args.push("--no-sandbox");

// Untuk headless mode
launchOptions.args.push("--window-size=1280,720");
```

## 🔧 Setup Backend untuk Testing

### 1. Test API Endpoints

Tambahkan controller dan routes untuk test data management:

```php
// app/Http/Controllers/TestJournalController.php
// app/Http/Controllers/TestAccountController.php
// routes/api.php (test endpoints)
```

### 2. Test Data Seeder

```php
// database/seeders/CypressTestSeeder.php
class CypressTestSeeder extends Seeder
{
    public function run()
    {
        // Create test users
        // Create test accounts
        // Create test journals
    }
}
```

### 3. Environment Configuration

```env
# .env.testing
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

## 🚨 Troubleshooting

### Common Issues

#### 1. Test Timeout

```javascript
// Increase timeout in cypress.config.js
defaultCommandTimeout: 15000;
```

#### 2. Element Not Found

```javascript
// Pastikan data-testid attributes ada
<input data-testid="transaction-date" />
```

#### 3. API Endpoints Not Found

```bash
# Pastikan routes/api.php sudah dibuat
# Pastikan controllers sudah dibuat
php artisan route:list --name=test
```

#### 4. Authentication Issues

```javascript
// Pastikan session management bekerja
cy.session([email, password], () => {
    // Login logic
});
```

### Debug Mode

```bash
# Jalankan dengan debug mode
npx cypress run --headed --no-exit

# Atau gunakan dalam test
cy.debug()
cy.pause()
```

### Performance Issues

```javascript
// Monitor performance
beforeEach(() => {
    cy.window().then((win) => {
        win.performance.mark("test-start");
    });
});
```

## 📈 CI/CD Integration

### GitHub Actions

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
                  start: php artisan serve
                  wait-on: "http://localhost:8000"
                  spec: "cypress/e2e/journal-*.cy.js"
```

### GitLab CI

```yaml
e2e-tests:
    stage: test
    script:
        - npm install
        - npm run build
        - php artisan serve &
        - npm run test:e2e
    artifacts:
        when: always
        paths:
            - cypress/screenshots/
            - cypress/videos/
```

## 📊 Test Reports

### Generate Reports

```bash
# Dengan Mochawesome reporter
npm install --save-dev mochawesome mochawesome-merge mochawesome-report-generator

# Jalankan test dengan reporter
npx cypress run --reporter mochawesome
```

### Coverage Reports

```bash
# Install coverage tools
npm install --save-dev @cypress/code-coverage nyc

# Generate coverage report
npx nyc report --reporter=html
```

## 🎯 Best Practices

### 1. Test Data Management

-   Gunakan API endpoints untuk create/cleanup
-   Isolasi test data antar test cases
-   Cleanup otomatis setelah test

### 2. Selectors

-   Gunakan `data-testid` attributes
-   Hindari CSS classes atau text content
-   Gunakan semantic selectors

### 3. Assertions

-   Gunakan assertions yang spesifik
-   Test business logic, bukan hanya UI
-   Verifikasi state aplikasi

### 4. Performance

-   Gunakan `cy.intercept()` untuk mock API
-   Batch operations untuk test data
-   Optimize waiting strategies

## 📚 Resources

-   [Cypress Documentation](https://docs.cypress.io/)
-   [Filament Testing Guide](https://filamentphp.com/docs/3.x/panels/testing)
-   [Laravel Testing](https://laravel.com/docs/testing)
-   [Cypress Best Practices](https://docs.cypress.io/guides/references/best-practices)

## 🤝 Contributing

1. Tambahkan test cases baru sesuai fitur
2. Update custom commands untuk pattern berulang
3. Maintain dokumentasi test scenarios
4. Follow naming conventions yang ada

---

**Happy Testing! 🎉**
