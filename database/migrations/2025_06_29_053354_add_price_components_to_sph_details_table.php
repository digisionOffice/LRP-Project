<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This adds columns to store the price breakdown (dasar, ppn, oat).
     */
    public function up(): void
    {
        Schema::table('sph_details', function (Blueprint $table) {
            // Adding the new columns after the 'quantity' column for logical grouping.
            $table->decimal('harga_dasar', 15, 2)->default(0)->after('quantity');
            $table->decimal('ppn', 15, 2)->default(0)->after('harga_dasar');
            $table->decimal('oat', 15, 2)->default(0)->after('ppn');
        });
    }

    /**
     * Reverse the migrations.
     * This removes the new columns if you need to rollback.
     */
    public function down(): void
    {
        Schema::table('sph_details', function (Blueprint $table) {
            $table->dropColumn(['harga_dasar', 'ppn', 'oat']);
        });
    }
};
