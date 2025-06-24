<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Akun extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'akun';

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'kategori_akun',
        'tipe_akun',
        'saldo_awal',
        'created_by',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'saldo_awal' => 'decimal:2',
    ];

    // Relationships
    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class, 'account_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeByCategory($query, $category)
    {
        return $query->where('kategori_akun', $category);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('tipe_akun', $type);
    }

    // Helper methods
    public function getCurrentBalance($endDate = null)
    {
        $query = $this->journalEntries()
            ->whereHas('journal', function ($q) use ($endDate) {
                $q->where('status', 'Posted');
                if ($endDate) {
                    $q->where('transaction_date', '<=', $endDate);
                }
            });

        $totalDebit = $query->sum('debit');
        $totalCredit = $query->sum('credit');

        $balance = $this->saldo_awal ?? 0;

        if ($this->tipe_akun === 'Debit') {
            $balance += $totalDebit - $totalCredit;
        } else {
            $balance += $totalCredit - $totalDebit;
        }

        return $balance;
    }
}
