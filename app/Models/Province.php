<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Province extends Model
{
    use SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function regencies()
    {
        return $this->hasMany(Regency::class, 'province_id');
    }

    public function districts()
    {
        return $this->hasManyThrough(District::class, Regency::class, 'province_id', 'regency_id');
    }

    public function subdistricts()
    {
        return $this->hasManyThrough(
            Subdistrict::class,
            District::class,
            'regency_id',
            'district_id',
            'id',
            'id'
        )->join('regencies', 'districts.regency_id', '=', 'regencies.id')
            ->where('regencies.province_id', $this->id);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get total count of regencies
     */
    public function getRegenciesCountAttribute(): int
    {
        return $this->regencies()->count();
    }

    /**
     * Get total count of districts
     */
    public function getDistrictsCountAttribute(): int
    {
        return $this->districts()->count();
    }

    /**
     * Get total count of subdistricts
     */
    public function getSubdistrictsCountAttribute(): int
    {
        return Subdistrict::whereHas('district.regency', function ($query) {
            $query->where('province_id', $this->id);
        })->count();
    }
}
