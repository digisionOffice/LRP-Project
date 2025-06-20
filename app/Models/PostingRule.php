<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostingRule extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'posting_rules';

    protected $fillable = [
        'rule_name',
        'source_type',
        'trigger_condition',
        'description',
        'is_active',
        'priority',
        'created_by',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'trigger_condition' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    // Relationships
    public function postingRuleEntries()
    {
        return $this->hasMany(PostingRuleEntry::class)->orderBy('sort_order');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function journals()
    {
        return $this->hasMany(Journal::class, 'posting_rule_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySourceType($query, $sourceType)
    {
        return $query->where('source_type', $sourceType);
    }

    public function scopeOrderedByPriority($query)
    {
        return $query->orderBy('priority', 'asc');
    }

    // Helper methods
    public function evaluateCondition($sourceModel)
    {
        if (empty($this->trigger_condition)) {
            return true; // No condition means always apply
        }

        foreach ($this->trigger_condition as $field => $expectedValue) {
            $actualValue = data_get($sourceModel, $field);
            if ($actualValue != $expectedValue) {
                return false;
            }
        }

        return true;
    }
}
