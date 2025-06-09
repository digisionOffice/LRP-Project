<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Divisi extends Model
{
    use SoftDeletes;

    protected $table = 'divisi';

    protected $fillable = [
        'nama',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'id_divisi');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
