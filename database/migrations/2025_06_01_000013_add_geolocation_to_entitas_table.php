<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('entitas', function (Blueprint $table) {
            // Only add columns if they don't exist
            if (!Schema::hasColumn('entitas', 'radius')) {
                $table->integer('radius')->default(100)->after('longitude')
                    ->comment('Radius dalam meter yang diperbolehkan untuk absensi');
            }

            if (!Schema::hasColumn('entitas', 'enable_geofencing')) {
                $table->boolean('enable_geofencing')->default(true)->after('radius')
                    ->comment('Aktifkan/nonaktifkan geofencing untuk entitas ini');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entitas', function (Blueprint $table) {
            $table->dropColumn([
                'latitude',
                'longitude',
                'radius',
                'enable_geofencing'
            ]);
        });
    }
};
