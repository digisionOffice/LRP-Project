<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Journal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'journals';

    protected $fillable = [
        'journal_number',
        'transaction_date',
        'reference_number',
        'source_type',
        'source_id',
        'description',
        'status',
        'posting_rule_id',
        'created_by',
    ];

    protected $dates = ['deleted_at', 'transaction_date'];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    // Relationships
    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function source()
    {
        return $this->morphTo();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postingRule()
    {
        return $this->belongsTo(PostingRule::class, 'posting_rule_id');
    }

    // Scopes
    public function scopePosted($query)
    {
        return $query->where('status', 'Posted');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'Draft');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    // Helper methods
    public function getTotalDebitAttribute()
    {
        return $this->journalEntries->sum('debit');
    }

    public function getTotalCreditAttribute()
    {
        return $this->journalEntries->sum('credit');
    }

    public function isBalanced()
    {
        return $this->getTotalDebitAttribute() == $this->getTotalCreditAttribute();
    }

    // Auto-generate journal number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($journal) {
            if (empty($journal->journal_number)) {
                $journal->journal_number = static::generateJournalNumber();
            }
        });
    }

    public static function generateJournalNumber()
    {
        $year = date('Y');
        $month = date('m');
        $lastJournal = static::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastJournal ? (int)substr($lastJournal->journal_number, -4) + 1 : 1;

        return sprintf('JRN-%s%s-%04d', $year, $month, $sequence);
    }
}
