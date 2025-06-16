<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('alamat_pelanggan', function (Blueprint $table) {
            // Add location column for Leaflet Map Picker
            $table->text('location')->nullable()->after('alamat');
        });

       
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alamat_pelanggan', function (Blueprint $table) {
            $table->dropColumn('location');
        });
    }
};
