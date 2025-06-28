<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_name',
        'user_id',
        'channel',
        'is_active',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mencari dan mengembalikan semua pengaturan notifikasi yang aktif untuk sebuah event.
     *
     * @param string $eventName Nama event yang akan dicari.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function findActiveRecipientsForEvent(string $eventName)
    {
        return static::with('user')
            ->where('event_name', $eventName)
            ->where('is_active', true)
            ->get();
    }
}
