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
}
