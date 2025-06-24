# Expense Request Auto Posting Integration

## Overview

Sistem auto posting untuk Expense Request telah diintegrasikan dengan PostingRule system yang ada di halaman `/admin/posting-rules`. Integrasi ini memungkinkan pembuatan jurnal otomatis berdasarkan aturan posting yang telah dikonfigurasi.

## Komponen yang Dimodifikasi

### 1. **ExpenseRequest Model** (`app/Models/ExpenseRequest.php`)

#### Perubahan:
- **Import JournalingService**: Menambahkan import untuk service auto posting
- **Method createJournalEntry()**: Direvisi untuk menggunakan PostingRule system
- **Helper Methods**: Menambahkan method untuk mendukung posting rules:
  - `getTransactionAmount()`: Mengembalikan approved_amount atau requested_amount
  - `getTransactionDate()`: Mengembalikan approved_at atau created_at
  - `getTransactionCode()`: Mengembalikan request_number

#### Implementasi Auto Posting:
```php
public function createJournalEntry(): ?Journal
{
    if ($this->journal_id) {
        return $this->journal; // Journal already exists
    }

    try {
        $journalingService = new JournalingService();
        $journalingService->postTransaction('ExpenseRequest', $this);
        
        // Find the created journal
        $journal = Journal::where('source_type', 'ExpenseRequest')
            ->where('source_id', $this->id)
            ->latest()
            ->first();
            
        if ($journal) {
            $this->update(['journal_id' => $journal->id]);
            return $journal;
        }
        
        return null;
    } catch (\Exception $e) {
        Log::error('Failed to create journal entry for expense request', [
            'expense_request_id' => $this->id,
            'error' => $e->getMessage()
        ]);
        return null;
    }
}
```

### 2. **PostingRuleEntry Model** (`app/Models/PostingRuleEntry.php`)

#### Perubahan:
- **Enhanced calculateAmount()**: Menambahkan fallback logic untuk ExpenseRequest
- **Fallback Logic**: Jika approved_amount null, menggunakan requested_amount

```php
case 'SourceValue':
    $value = data_get($sourceModel, $this->source_property, 0);
    
    // Special handling for ExpenseRequest approved_amount fallback
    if ($value === null && $this->source_property === 'approved_amount' && $sourceModel instanceof \App\Models\ExpenseRequest) {
        $value = $sourceModel->requested_amount;
    }
    
    return (float) $value;
```

### 3. **JournalingService** (`app/Services/JournalingService.php`)

#### Perubahan:
- **Enhanced createJournalFromRule()**: Menambahkan helper methods untuk field mapping
- **Helper Methods**:
  - `getTransactionDate()`: Mapping tanggal transaksi berdasarkan model type
  - `getReferenceNumber()`: Mapping reference number berdasarkan model type
  - `generateJournalNumber()`: Generate nomor jurnal otomatis

### 4. **ExpenseRequestPostingRulesSeeder** (`database/seeders/ExpenseRequestPostingRulesSeeder.php`)

#### Fitur:
- **Posting Rules per Kategori**: Membuat aturan posting untuk setiap kategori expense
- **Automatic Journal Entries**: Debit akun beban, Credit kas
- **Kategori yang Didukung**:
  - Tank Truck Maintenance (5110)
  - License Fee (5120)
  - Business Travel (5130)
  - Utilities (5140)
  - Other Expenses (5150)

#### Struktur Posting Rules:
```php
// Contoh untuk Tank Truck Maintenance
PostingRule::create([
    'rule_name' => 'Expense Request - Tank Truck Maintenance',
    'source_type' => 'ExpenseRequest',
    'trigger_condition' => ['category' => 'tank_truck_maintenance'],
    'description' => 'Auto posting untuk expense request perawatan truk tangki',
    'is_active' => true,
    'priority' => 1,
]);

// Debit: Biaya Perawatan Truk Tangki (5110)
PostingRuleEntry::create([
    'posting_rule_id' => $rule->id,
    'account_id' => $maintenanceAccount->id,
    'dc_type' => 'Debit',
    'amount_type' => 'SourceValue',
    'source_property' => 'approved_amount',
    'description_template' => 'Biaya Perawatan Truk Tangki - {source.request_number}',
]);

// Credit: Kas (1110)
PostingRuleEntry::create([
    'posting_rule_id' => $rule->id,
    'account_id' => $kasAccount->id,
    'dc_type' => 'Credit',
    'amount_type' => 'SourceValue',
    'source_property' => 'approved_amount',
    'description_template' => 'Pembayaran Biaya Perawatan - {source.request_number}',
]);
```

### 5. **ComprehensiveSeeder** (`database/seeders/ComprehensiveSeeder.php`)

#### Perubahan:
- **Updated Chart of Accounts**: Menambahkan akun 1110 (Kas & Bank) dan akun beban spesifik
- **Integrated Posting Rules Seeder**: Memanggil ExpenseRequestPostingRulesSeeder
- **Account Mapping**: Menggunakan kode akun yang sesuai dengan posting rules

## Workflow Auto Posting

### 1. **Saat Expense Request Disetujui**
1. User melakukan approval di Filament Resource
2. Status berubah menjadi 'approved'
3. Method `createJournalEntry()` dipanggil otomatis
4. JournalingService mencari posting rules yang sesuai
5. Sistem membuat journal entries berdasarkan rules
6. Journal status: 'Draft' (siap untuk posting)

### 2. **Saat Expense Request Dibayar**
1. User menandai sebagai 'paid' di Filament Resource
2. Method `postJournalEntry()` dipanggil
3. Journal status berubah menjadi 'Posted'
4. Transaksi tercatat di sistem akuntansi

## Konfigurasi Akun

### Chart of Accounts untuk Expense Request:
- **1110**: Kas & Bank (Credit account)
- **5110**: Beban Perawatan Truk Tangki
- **5120**: Beban Lisensi & Perizinan
- **5130**: Beban Perjalanan Dinas
- **5140**: Beban Utilitas
- **5150**: Beban Lain-lain

## Cara Menjalankan Seeder

```bash
# Menjalankan comprehensive seeder (termasuk posting rules)
php artisan db:seed --class=ComprehensiveSeeder

# Atau menjalankan posting rules seeder saja
php artisan db:seed --class=ExpenseRequestPostingRulesSeeder
```

## Testing

### 1. **Test Approval Workflow**
1. Buat expense request baru
2. Submit untuk approval
3. Approve dengan jumlah tertentu
4. Cek apakah journal entry terbuat dengan status 'Draft'
5. Tandai sebagai 'paid'
6. Cek apakah journal status berubah menjadi 'Posted'

### 2. **Test Posting Rules**
1. Akses halaman `/admin/posting-rules`
2. Cek apakah posting rules untuk ExpenseRequest sudah ada
3. Edit rules jika diperlukan
4. Test dengan berbagai kategori expense request

## Troubleshooting

### 1. **Journal Tidak Terbuat**
- Cek apakah posting rules aktif
- Cek apakah akun yang diperlukan sudah ada
- Cek log error di `storage/logs/laravel.log`

### 2. **Journal Tidak Balanced**
- Cek konfigurasi posting rule entries
- Pastikan total debit = total credit
- Cek calculation logic di PostingRuleEntry

### 3. **Account Tidak Ditemukan**
- Jalankan seeder untuk membuat akun yang diperlukan
- Cek mapping akun di ExpenseRequest model
- Update posting rules jika diperlukan

## Keuntungan Integrasi

1. **Konsistensi**: Menggunakan sistem posting rules yang sama untuk semua transaksi
2. **Fleksibilitas**: Posting rules dapat dimodifikasi tanpa mengubah kode
3. **Audit Trail**: Semua journal entries memiliki referensi ke posting rule
4. **Maintainability**: Centralized logic untuk auto posting
5. **Scalability**: Mudah menambahkan rules baru untuk transaksi lain
