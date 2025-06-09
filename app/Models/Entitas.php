<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entitas extends Model
{
    use SoftDeletes;

    protected $table = 'entitas';

    protected $fillable = [
        'nama',
        'id_entitas_tipe',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function entitasTipe()
    {
        return $this->belongsTo(EntitasTipe::class, 'id_entitas_tipe');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'id_entitas');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
