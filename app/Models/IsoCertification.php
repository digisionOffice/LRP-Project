<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class IsoCertification extends Model
{
    use HasFactory;

    /**
     * Define the valid certificate names.
     * This acts as the single source of truth for the application.
     */
    public const CERTIFICATE_NAMES = [
        'ISO 9001:2015' => 'ISO 9001:2015',
        'ISO 45001:2018' => 'ISO 45001:2018',
        // Add any other standard certificate names here
    ];

    protected $table = 'iso_certifications';

    protected $fillable = [
        'name',
        'certificate_number',
        'logo_path',
        'active_year',
        'end_year',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo_path) {
            return Storage::disk('public')->url($this->logo_path);
        }
        return 'https://placehold.co/100x100?text=No+Image';
    }

    /**
     * The "booted" method of the model.
     * This is the ideal place to register model event listeners.
     */
    protected static function booted(): void
    {
        /**
         * Listen for the 'created' event. This will fire automatically
         * after a new IsoCertification record is saved to the database.
         */
        static::created(function (IsoCertification $certification) {
            // Only run this logic if the newly created certificate is set to 'active'.
            if ($certification->is_active) {
                // Find all OTHER certificates with the SAME name and deactivate them.
                static::where('name', $certification->name)
                      ->where('id', '!=', $certification->id)
                      ->update(['is_active' => false]);
            }
        });

        /**
         * Listen for the 'updating' event. This fires just before a record is updated.
         * This ensures that if an old record is re-activated, it becomes the only active one.
         */
        static::updating(function (IsoCertification $certification) {
            // Check if the 'is_active' field is being changed to true.
            if ($certification->isDirty('is_active') && $certification->is_active) {
                // If so, deactivate all other certificates with the same name.
                static::where('name', $certification->name)
                      ->where('id', '!=', $certification->id)
                      ->update(['is_active' => false]);
            }
        });
    }
}
