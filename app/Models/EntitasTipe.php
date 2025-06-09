<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EntitasTipe extends Model
{
    use SoftDeletes;

    protected $table = 'entitas_tipe';

    protected $fillable = [
        'nama',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function entitas()
    {
        return $this->hasMany(Entitas::class, 'id_entitas_tipe');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
