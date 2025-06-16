<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlamatPelanggan extends Model
{
    protected $table = 'alamat_pelanggan';

    protected $fillable = [
        'id_pelanggan',
        'alamat',
        'location',
        'is_primary',
    ];



    protected $casts = [
        'is_primary' => 'boolean',
        'location' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the pelanggan that owns this address
     */
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }

    /**
     * Boot method to add model events
     */
    protected static function boot()
    {
        parent::boot();

        // Ensure only one primary address per customer
        static::saving(function ($alamat) {
            if ($alamat->is_primary) {
                // Set all other addresses for this customer to non-primary
                static::where('id_pelanggan', $alamat->id_pelanggan)
                    ->where('id', '!=', $alamat->id)
                    ->update(['is_primary' => false]);
            }
        });
    }


    /**
     * Check if address has coordinates
     */
    public function hasCoordinates(): bool
    {
        return !is_null($this->location);
    }

    /**
     * Get the location attribute for Leaflet Map Picker integration
     * Returns coordinates in [lat, lng] format as expected by the plugin
     */
    public function getLocationAttribute(): ?array
    {
        // First check if we have data in the location column (new format)
        if (!empty($this->attributes['location'])) {
            $decoded = json_decode($this->attributes['location'], true);
            if (is_array($decoded) && count($decoded) >= 2) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * Set the location attribute for Leaflet Map Picker integration
     * Accepts coordinates in [lat, lng] format from the plugin
     */
    public function setLocationAttribute(?array $value): void
    {
        if (is_array($value) && count($value) >= 2) {
            // Store in the location column as JSON (plugin format)
            $this->attributes['location'] = json_encode($value);

        } elseif (is_null($value)) {
            // Clear all location data
            $this->attributes['location'] = null;
        }
    }
}
