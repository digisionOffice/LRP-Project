# Invoice Auto Posting Integration

## Overview

Sistem auto posting untuk Invoice telah diintegrasikan dengan PostingRule system. Auto posting dilakukan **saat invoice dibuat** (bukan saat transaksi penjualan) karena:

1. **Revenue Recognition** - Revenue diakui saat invoice diterbitkan
2. **Tax Integration** - PPN dan pajak lainnya sudah pasti saat invoicing
3. **Final Values** - Nilai final sudah pasti (termasuk diskon, biaya tambahan, dll)
4. **Better Audit Trail** - Journal terkait langsung dengan dokumen invoice

## Komponen yang Dimodifikasi

### 1. **Invoice Model** (`app/Models/Invoice.php`)

#### Perubahan:
- **Import JournalingService**: Menambahkan import untuk service auto posting
- **Field journal_id**: Menambahkan ke fillable array
- **Relationship journal()**: Menambahkan relationship ke Journal model
- **Method createJournalEntry()**: Auto posting menggunakan PostingRule system
- **Helper Methods**: Method untuk mendukung posting rules
- **Fixed Accessors**: Memperbaiki accessor untuk menggunakan field database yang benar

#### Implementasi Auto Posting:
```php
public function createJournalEntry(): ?Journal
{
    if ($this->journal_id) {
        return $this->journal; // Journal already exists
    }

    try {
        $journalingService = new JournalingService();
        $journalingService->postTransaction('Invoice', $this);
        
        // Find the created journal
        $journal = Journal::where('source_type', 'Invoice')
            ->where('source_id', $this->id)
            ->latest()
            ->first();
            
        if ($journal) {
            $this->update(['journal_id' => $journal->id]);
            return $journal;
        }
        
        return null;
    } catch (\Exception $e) {
        Log::error('Failed to create journal entry for invoice', [
            'invoice_id' => $this->id,
            'error' => $e->getMessage()
        ]);
        return null;
    }
}
```

### 2. **PostingRuleEntry Model** (`app/Models/PostingRuleEntry.php`)

#### Perubahan:
- **Enhanced calculateAmount()**: Menambahkan special handling untuk Invoice calculated fields
- **Invoice Field Mapping**: Mapping untuk field yang menggunakan accessor

```php
// Special handling for Invoice calculated fields
if ($sourceModel instanceof \App\Models\Invoice) {
    if ($this->source_property === 'total_invoice') {
        $value = $sourceModel->getTotalInvoiceAttribute();
    } elseif ($this->source_property === 'subtotal') {
        $value = $sourceModel->getSubtotalAttribute();
    } elseif ($this->source_property === 'total_pajak') {
        $value = $sourceModel->getTotalPajakAttribute();
    }
}
```

### 3. **JournalingService** (`app/Services/JournalingService.php`)

#### Perubahan:
- **Enhanced helper methods**: Menambahkan support untuk Invoice model
- **Field Mapping**: Mapping field tanggal dan reference number untuk Invoice

### 4. **InvoicePostingRulesSeeder** (`database/seeders/InvoicePostingRulesSeeder.php`)

#### Posting Rules yang Dibuat:

1. **Invoice Penjualan - Tanpa PPN** (Priority: 1)
   - Trigger: `include_ppn = false`
   - Debit: Piutang Dagang (1200) - total_invoice
   - Credit: Pendapatan Penjualan (4100) - subtotal

2. **Invoice Penjualan - Dengan PPN** (Priority: 2)
   - Trigger: `include_ppn = true`
   - Debit: Piutang Dagang (1200) - total_invoice
   - Credit: Pendapatan Penjualan (4100) - subtotal
   - Credit: PPN Keluaran (2110) - total_pajak

3. **Invoice - Biaya Ongkos Angkut** (Priority: 3)
   - Trigger: `include_operasional_kerja = true`
   - Credit: Pendapatan Operasional Kerja (4400) - biaya_operasional_kerja

4. **Invoice - PBBKB** (Priority: 4)
   - Trigger: `include_pbbkb = true`
   - Credit: Pendapatan PBBKB (4300) - biaya_pbbkb

### 5. **CreateInvoice Page** (`app/Filament/Resources/InvoiceResource/Pages/CreateInvoice.php`)

#### Perubahan:
- **afterCreate() method**: Auto posting journal entry saat invoice dibuat

```php
protected function afterCreate(): void
{
    // Auto posting journal entry when invoice is created
    $this->record->createJournalEntry();
}
```

### 6. **ComprehensiveSeeder** (`database/seeders/ComprehensiveSeeder.php`)

#### Perubahan:
- **Enhanced Invoice Seeding**: Menambahkan field untuk posting rules
- **Integrated Posting Rules Seeder**: Memanggil InvoicePostingRulesSeeder
- **Account Creation**: Membuat akun yang diperlukan untuk posting rules

## Chart of Accounts untuk Invoice

### Akun yang Dibuat/Digunakan:
- **1200**: Piutang Dagang (Debit account)
- **4100**: Pendapatan Penjualan (Credit account)
- **2110**: PPN Keluaran (Credit account)
- **4200**: Pendapatan Ongkos Angkut (Credit account)
- **4300**: Pendapatan PBBKB (Credit account)
- **4400**: Pendapatan Operasional Kerja (Credit account)

## Workflow Auto Posting

### 1. **Saat Invoice Dibuat**
1. User membuat invoice di Filament Resource
2. Invoice disimpan ke database
3. Method `afterCreate()` dipanggil otomatis
4. `createJournalEntry()` dieksekusi
5. JournalingService mencari posting rules yang sesuai
6. Sistem membuat journal entries berdasarkan rules
7. Journal status: 'Posted' (jika balanced)

### 2. **Multiple Posting Rules**
Sistem mendukung multiple posting rules untuk satu invoice:
- Rule utama (dengan/tanpa PPN) untuk piutang dan pendapatan
- Rule tambahan untuk biaya operasional (jika include_operasional_kerja = true)
- Rule tambahan untuk PBBKB (jika include_pbbkb = true)

### 3. **Contoh Journal Entries**

#### Invoice dengan PPN + Operasional + PBBKB:
```
Debit:  Piutang Dagang (1200)           Rp 12,210,000
Credit: Pendapatan Penjualan (4100)     Rp 10,000,000
Credit: PPN Keluaran (2110)             Rp  1,100,000
Credit: Pendapatan Operasional (4400)   Rp    500,000
Credit: Pendapatan PBBKB (4300)         Rp    610,000
```

## Cara Menjalankan Seeder

```bash
# Menjalankan comprehensive seeder (termasuk posting rules)
php artisan db:seed --class=ComprehensiveSeeder

# Atau menjalankan posting rules seeder saja
php artisan db:seed --class=InvoicePostingRulesSeeder
```

## Testing

### 1. **Test Invoice Creation**
1. Buat invoice baru melalui Filament admin
2. Cek apakah journal entry terbuat otomatis
3. Verifikasi journal entries sesuai dengan posting rules
4. Cek balance debit = credit

### 2. **Test Different Scenarios**
1. Invoice tanpa PPN
2. Invoice dengan PPN
3. Invoice dengan biaya operasional
4. Invoice dengan PBBKB
5. Invoice dengan kombinasi semua fitur

### 3. **Test Posting Rules Management**
1. Akses halaman `/admin/posting-rules`
2. Cek apakah posting rules untuk Invoice sudah ada
3. Edit rules jika diperlukan
4. Test dengan berbagai kombinasi flag

## Keuntungan Pendekatan Invoicing

1. **Revenue Recognition**: Revenue diakui saat invoice diterbitkan (accrual basis)
2. **Tax Compliance**: PPN dan pajak lainnya sudah pasti dan tercatat
3. **Complete Information**: Semua informasi final (diskon, biaya tambahan) sudah ada
4. **Audit Trail**: Journal terkait langsung dengan dokumen invoice
5. **Flexibility**: Posting rules dapat dimodifikasi tanpa mengubah kode
6. **Consistency**: Menggunakan sistem posting rules yang sama untuk semua transaksi

## Troubleshooting

### 1. **Journal Tidak Terbuat**
- Cek apakah posting rules aktif
- Cek apakah akun yang diperlukan sudah ada
- Cek trigger conditions sesuai dengan data invoice
- Cek log error di `storage/logs/laravel.log`

### 2. **Journal Tidak Balanced**
- Cek konfigurasi posting rule entries
- Pastikan semua rules yang applicable dieksekusi
- Cek calculation logic di PostingRuleEntry

### 3. **Field Tidak Ditemukan**
- Pastikan field yang direferensi di source_property ada di database
- Cek accessor methods di Invoice model
- Update posting rules jika ada perubahan struktur data
