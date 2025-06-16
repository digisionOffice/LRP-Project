<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class User extends Authenticatable implements FilamentUser, HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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

    public function expenseRequests()
    {
        return $this->hasMany(ExpenseRequest::class, 'user_id');
    }


    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    /**
     * Register media collections for the User model
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png']);

        $this->addMediaCollection('standalone')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf']);
    }

    /**
     * Register media conversions for the User model
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->performOnCollections('avatar', 'standalone');

        $this->addMediaConversion('preview')
            ->width(300)
            ->height(300)
            ->performOnCollections('avatar', 'standalone');
    }

    // role
    public function isAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    // get role from  spatie
    public function getRoleNameAttribute()
    {
        return $this->roles->first()->name ?? '';
    }
}
