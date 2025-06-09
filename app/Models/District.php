<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class District extends Model
{
    use SoftDeletes;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'regency_id',
        'name',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function regency()
    {
        return $this->belongsTo(Regency::class, 'regency_id');
    }

    public function province()
    {
        return $this->hasOneThrough(Province::class, Regency::class, 'id', 'id', 'regency_id', 'province_id');
    }

    public function subdistricts()
    {
        return $this->hasMany(Subdistrict::class, 'district_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get total count of subdistricts
     */
    public function getSubdistrictsCountAttribute(): int
    {
        return $this->subdistricts()->count();
    }
}
