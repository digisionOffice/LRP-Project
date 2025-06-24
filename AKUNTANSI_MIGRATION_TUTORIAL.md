# Tutorial Migrasi Modul Akuntansi (Accounting Module)

Tutorial ini akan memandu Anda untuk mengimplementasikan modul Akuntansi lengkap dari proyek Laravel 10 / Filament 3 yang sudah ada ke proyek baru dengan stack yang sama.

## Prerequisites

Pastikan Anda memiliki:
- Laravel 10.x
- Filament 3.x
- PHP 8.1 atau lebih tinggi
- MySQL 8.0 atau MariaDB 10.3+
- Composer 2.x

## Step 1: Composer Dependencies

Jalankan perintah berikut untuk menginstall dependencies yang diperlukan:

```bash
composer require filament/filament
```

**Catatan:** Modul ini menggunakan fitur-fitur standar Laravel dan Filament, sehingga tidak memerlukan package tambahan khusus.

## Step 2: Copy Files

Salin semua file berikut ke proyek tujuan Anda. **PENTING:** Pastikan Anda menyalin SEMUA file part yang dihasilkan untuk kode konsolidasi:

### File yang harus disalin:
- `migrated_feature_part_1.txt` - Models dan Migrations
- `migrated_feature_part_2.txt` - Filament Resources  
- `migrated_feature_part_3.txt` - Filament Pages dan Services
- `migrated_feature_part_4.txt` - Views dan Seeders

### Struktur direktori yang akan dibuat:
```
app/
├── Models/
│   ├── Akun.php
│   ├── Journal.php
│   ├── JournalEntry.php
│   ├── PostingRule.php
│   ├── PostingRuleEntry.php
│   ├── Inventory.php
│   └── SalesTransaction.php (jika belum ada)
├── Filament/
│   ├── Resources/
│   │   ├── AkunResource.php
│   │   ├── JournalResource.php
│   │   ├── InventoryResource.php
│   │   └── PostingRuleResource.php
│   └── Pages/
│       ├── GeneralLedger.php
│       ├── IncomeStatement.php
│       └── BalanceSheet.php
├── Services/
│   └── JournalingService.php
database/
├── migrations/
│   ├── 2025_02_20_183708_create_data_master_table.php (untuk tabel akun)
│   ├── 2025_06_17_182532_create_accounting_tables.php
│   ├── 2025_06_17_182636_create_journal_tables.php
│   ├── 2025_06_17_185243_create_posting_rules_tables.php
│   ├── 2025_06_17_190602_add_error_status_to_journals_table.php
│   └── 2025_06_18_090110_update_akun_saldo_awal_column_type.php
└── seeders/
    ├── AccountingSeeder.php
    └── PostingRulesSeeder.php
resources/
└── views/
    └── filament/
        └── pages/
            ├── general-ledger.blade.php
            ├── income-statement.blade.php
            └── balance-sheet.blade.php
```

## Step 3: Database Migration

Jalankan migrasi database:

```bash
php artisan migrate
```

**Urutan migrasi yang benar:**
1. `create_data_master_table.php` (membuat tabel akun)
2. `create_accounting_tables.php` (membuat tabel inventories dan sales_transactions)
3. `create_journal_tables.php` (membuat tabel journals dan journal_entries)
4. `create_posting_rules_tables.php` (membuat tabel posting_rules dan posting_rule_entries)
5. `add_error_status_to_journals_table.php` (menambah status Error ke journals)
6. `update_akun_saldo_awal_column_type.php` (update tipe data saldo_awal)

## Step 4: Database Seeding

### Update DatabaseSeeder.php

Tambahkan seeder ke file `database/seeders/DatabaseSeeder.php`:

```php
public function run(): void
{
    $this->call([
        // ... seeder lain yang sudah ada
        AccountingSeeder::class,
        PostingRulesSeeder::class,
    ]);
}
```

### Jalankan Seeding

```bash
php artisan db:seed --class=AccountingSeeder
php artisan db:seed --class=PostingRulesSeeder
```

Atau jalankan semua seeder:

```bash
php artisan db:seed
```

## Step 5: Frontend Assets

Jika Anda menggunakan custom CSS, pastikan untuk mengcompile assets:

```bash
npm run build
```

Atau untuk development:

```bash
npm run dev
```

## Step 6: Caching

Bersihkan cache untuk memastikan semua perubahan terdeteksi:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## Step 7: Registering Components

### Panel Provider Configuration

Daftarkan semua komponen di Panel Provider Anda (biasanya `app/Providers/Filament/AdminPanelProvider.php`):

```php
use App\Filament\Resources\AkunResource;
use App\Filament\Resources\JournalResource;
use App\Filament\Resources\InventoryResource;
use App\Filament\Resources\PostingRuleResource;
use App\Filament\Pages\GeneralLedger;
use App\Filament\Pages\IncomeStatement;
use App\Filament\Pages\BalanceSheet;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... konfigurasi lain
        ->resources([
            // ... resources lain
            AkunResource::class,
            JournalResource::class,
            InventoryResource::class,
            PostingRuleResource::class,
        ])
        ->pages([
            // ... pages lain
            GeneralLedger::class,
            IncomeStatement::class,
            BalanceSheet::class,
        ]);
}
```

## Step 8: Verifikasi Installation

### Cek Menu Navigation

Setelah instalasi, Anda harus melihat grup menu "Akuntansi" dengan item:
- Chart of Accounts (Akun)
- Jurnal
- Inventaris
- Aturan Posting (Posting Rules)
- Buku Besar
- Laporan Laba Rugi
- Neraca

### Test Basic Functionality

1. **Chart of Accounts**: Buka menu Chart of Accounts dan pastikan data akun sudah ter-seed
2. **Posting Rules**: Cek menu Aturan Posting untuk melihat aturan yang sudah dibuat
3. **Reports**: Test laporan Buku Besar, Laba Rugi, dan Neraca

## Troubleshooting

### Error: Table 'akun' doesn't exist
- Pastikan migrasi `create_data_master_table.php` sudah dijalankan
- Cek apakah tabel `akun` sudah dibuat di database

### Error: Class not found
- Jalankan `composer dump-autoload`
- Pastikan namespace di file-file sudah benar

### Navigation menu tidak muncul
- Pastikan resources dan pages sudah didaftarkan di Panel Provider
- Clear cache dengan `php artisan config:clear`

### Data seeder error
- Pastikan migrasi sudah dijalankan sebelum seeding
- Cek apakah ada konflik data yang sudah ada

## Fitur Utama Modul Akuntansi

### 1. Chart of Accounts (Akun)
- Manajemen akun keuangan dengan kategori: Aset, Kewajiban, Ekuitas, Pendapatan, Beban
- Tipe akun: Debit/Kredit
- Saldo awal untuk setiap akun

### 2. Journal Management
- Pembuatan jurnal manual
- Auto-posting berdasarkan aturan posting
- Validasi keseimbangan debit-kredit
- Status jurnal: Draft, Posted, Cancelled, Error

### 3. Posting Rules (Aturan Posting)
- Aturan otomatis untuk posting transaksi
- Kondisi pemicu yang fleksibel
- Perhitungan jumlah: Fixed, SourceValue, Calculated
- Prioritas eksekusi

### 4. Inventory Management
- Tracking stok produk
- Perhitungan nilai inventaris
- Integrasi dengan sistem penjualan

### 5. Financial Reports
- **Buku Besar**: Laporan detail transaksi per akun
- **Laporan Laba Rugi**: Income statement dengan periode tertentu
- **Neraca**: Balance sheet per tanggal tertentu

### 6. Journaling Service
- Service class untuk automasi posting
- Expression parser untuk perhitungan kompleks
- Error handling dan logging

## Catatan Penting

1. **Backup Database**: Selalu backup database sebelum menjalankan migrasi
2. **Testing**: Test semua fitur di environment development sebelum production
3. **Permissions**: Pastikan user memiliki permission yang sesuai untuk mengakses modul akuntansi
4. **Data Integrity**: Modul ini menggunakan foreign key constraints untuk menjaga integritas data

## Support

Jika mengalami masalah selama instalasi, periksa:
1. Log Laravel di `storage/logs/laravel.log`
2. Error message di browser console
3. Database connection dan permissions
4. PHP version compatibility

Modul Akuntansi ini dirancang untuk memberikan foundation yang solid untuk sistem akuntansi dalam aplikasi Laravel/Filament Anda.
