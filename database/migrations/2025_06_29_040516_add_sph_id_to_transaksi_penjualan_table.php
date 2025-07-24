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
        Schema::table('transaksi_penjualan', function (Blueprint $table) {
            $table->foreignId('sph_id')
                  ->nullable()        // Nullable because not all sales come from an SPH
                  ->after('id')         // Places the column logically at the beginning
                  ->constrained('sph')  // Creates a foreign key to the 'sph' table
                  ->nullOnDelete();    // If the SPH is deleted, this link becomes NULL
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_penjualan', function (Blueprint $table) {
            // It's important to drop the foreign key before the column
            $table->dropForeign(['sph_id']);
            $table->dropColumn('sph_id');
        });
    }
};
