# Integrasi Expense Request dengan Sistem Akuntansi

## Overview
Implementasi integrasi expense request dengan sistem akuntansi untuk auto-posting jurnal telah berhasil diselesaikan. Sistem ini memungkinkan expense request terhubung dengan chart of accounts dan otomatis membuat jurnal entry saat disetujui dan dibayar.

## Fitur yang Diimplementasikan

### 1. **Database Schema Updates**
- Menambahkan kolom `account_id` dan `journal_id` ke tabel `expense_requests`
- Foreign key constraints ke tabel `akun` dan `journals`
- Indexes untuk performa yang optimal

### 2. **Chart of Accounts untuk Expense**
Akun-akun baru yang ditambahkan:
- **1110** - Kas & Bank (Aset)
- **5110** - Beban Perawatan Kendaraan (Beban)
- **5120** - Beban Lisensi & Perizinan (Beban)
- **5130** - Beban Perjalanan Dinas (Beban)
- **5140** - Beban Utilitas (Beban)
- **5150** - Beban Lain-lain (Beban)

### 3. **Mapping Kategori ke Akun**
Setiap kategori expense request dipetakan ke akun yang sesuai:
- `tank_truck_maintenance` → 5110 (Beban Perawatan Kendaraan)
- `license_fee` → 5120 (Beban Lisensi & Perizinan)
- `business_travel` → 5130 (Beban Perjalanan Dinas)
- `utilities` → 5140 (Beban Utilitas)
- `other` → 5150 (Beban Lain-lain)

### 4. **Auto-posting Logic**
- **Saat Approved**: Membuat jurnal dengan status "Draft"
  - Debit: Akun Beban sesuai kategori
  - Credit: Kas & Bank (1110)
- **Saat Paid**: Mengubah status jurnal menjadi "Posted"

### 5. **Filament Resource Enhancements**
- Field pemilihan akun dengan auto-select berdasarkan kategori
- Kolom akun di tabel expense requests
- Action "Tandai Dibayar" dengan auto-posting jurnal
- Validasi dan helper text untuk user experience

### 6. **Configuration Management**
File konfigurasi `config/expense-accounts.php` untuk:
- Mapping kategori ke akun
- Default cash account
- Journal settings
- Required accounts list

## Files yang Dimodifikasi/Ditambahkan

### Database
- `database/migrations/2025_06_23_000001_add_accounting_fields_to_expense_requests_table.php`
- `database/seeders/ExpenseAccountSeeder.php`
- `database/seeders/ExpenseRequestTestSeeder.php`
- `database/seeders/ComprehensiveSeeder.php` (updated)

### Models
- `app/Models/ExpenseRequest.php` (enhanced with accounting methods)

### Filament Resources
- `app/Filament/Resources/ExpenseRequestResource.php` (enhanced form and actions)

### Configuration
- `config/expense-accounts.php` (new configuration file)

## Workflow Expense Request dengan Akuntansi

### 1. **Pembuatan Expense Request**
1. User memilih kategori expense
2. Sistem otomatis memilih akun yang sesuai
3. User dapat mengubah akun jika diperlukan
4. Form disimpan dengan referensi akun

### 2. **Approval Process**
1. Manager/Admin mereview expense request
2. Saat di-approve, sistem otomatis:
   - Membuat jurnal entry dengan status "Draft"
   - Debit akun beban sesuai kategori
   - Credit akun kas/bank
   - Update status expense request

### 3. **Payment Process**
1. Finance menandai expense sebagai "Dibayar"
2. Sistem otomatis:
   - Mengubah status jurnal menjadi "Posted"
   - Update timestamp pembayaran
   - Jurnal siap untuk laporan keuangan

## Testing dan Validasi

### Data Test
- 10 expense requests dengan berbagai kategori dan status
- Jurnal entries otomatis untuk yang approved/paid
- Validasi mapping akun sesuai kategori

### Validasi Sistem
- ✅ Migrasi database berhasil
- ✅ Seeder akun expense berhasil
- ✅ Seeder test data berhasil
- ✅ Form Filament dengan auto-select akun
- ✅ Actions approve dan mark paid
- ✅ Auto-posting jurnal

## Cara Penggunaan

### 1. **Setup Initial**
```bash
# Jalankan migrasi
php artisan migrate

# Seed akun expense
php artisan db:seed --class=ExpenseAccountSeeder

# (Optional) Seed test data
php artisan db:seed --class=ExpenseRequestTestSeeder
```

### 2. **Membuat Expense Request**
1. Akses Filament Admin → Manajemen Keuangan → Permintaan Pengeluaran
2. Klik "Create"
3. Pilih kategori (akun akan otomatis terpilih)
4. Isi detail expense request
5. Submit

### 3. **Approval dan Payment**
1. Manager/Admin dapat approve dari tabel actions
2. Finance dapat mark as paid dari tabel actions
3. Jurnal otomatis dibuat dan di-posting

## Keamanan dan Validasi

### Validasi Form
- Kategori wajib dipilih
- Akun wajib dipilih
- Jumlah harus valid
- Justifikasi diperlukan

### Keamanan
- Foreign key constraints
- Soft deletes untuk audit trail
- User permissions via Filament Shield
- Validation pada model level

## Monitoring dan Reporting

### Dashboard Metrics
- Total expense requests per kategori
- Total amount per akun
- Status approval workflow
- Journal posting status

### Reports
- Expense by category
- Expense by account
- Journal entries from expenses
- Approval workflow analytics

## Maintenance

### Regular Tasks
- Review mapping kategori ke akun
- Monitor journal posting accuracy
- Audit expense approval workflow
- Update chart of accounts sesuai kebutuhan

### Troubleshooting
- Cek foreign key constraints jika ada error
- Validasi mapping akun di config file
- Monitor journal entry balance (debit = credit)
- Review user permissions untuk approval

## Future Enhancements

### Possible Improvements
1. **Budget Control**: Integrasi dengan budget planning
2. **Multi-level Approval**: Workflow approval bertingkat
3. **Expense Analytics**: Dashboard analytics yang lebih detail
4. **Mobile App**: Interface mobile untuk field staff
5. **Integration**: Integrasi dengan sistem payroll/HR
6. **Automated Notifications**: Email/SMS notifications untuk approval
7. **Document Management**: Upload dan manage supporting documents
8. **Recurring Expenses**: Template untuk expense yang berulang

---

**Status**: ✅ **COMPLETED**  
**Date**: 2025-06-23  
**Version**: 1.0  
**Author**: Augment Agent
