<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subdistrict extends Model
{
    use SoftDeletes;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'district_id',
        'name',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function regency()
    {
        return $this->hasOneThrough(Regency::class, District::class, 'id', 'id', 'district_id', 'regency_id');
    }

    public function province()
    {
        return $this->hasOneThrough(
            Province::class,
            Regency::class,
            'id',
            'id',
            'district_id',
            'province_id'
        )->join('districts', 'regencies.id', '=', 'districts.regency_id')
            ->where('districts.id', $this->district_id);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
