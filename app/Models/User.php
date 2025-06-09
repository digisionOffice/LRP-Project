<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
// FilamentUser
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'no_induk',
        'hp',
        'id_jabatan',
        'id_divisi',
        'id_entitas',
        'created_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'hp' => 'string',
        ];
    }

    // filament isAdmin
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    // Employee-related relationships (merged from Karyawan)
    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'id_jabatan');
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'id_divisi');
    }

    public function entitas()
    {
        return $this->belongsTo(Entitas::class, 'id_entitas');
    }

    // Operational relationships (using User instead of Karyawan)
    public function deliveryOrders()
    {
        return $this->hasMany(DeliveryOrder::class, 'id_user');
    }

    public function uangJalan()
    {
        return $this->hasMany(UangJalan::class, 'id_user');
    }

    // User management relationships
    public function userRoles()
    {
        return $this->hasMany(UserRole::class, 'id_user');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }
}
