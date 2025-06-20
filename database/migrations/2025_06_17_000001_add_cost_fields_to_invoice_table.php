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
        Schema::table('invoice', function (Blueprint $table) {
            // Add cost fields
            $table->decimal('biaya_ongkos_angkut', 15, 2)->nullable()->after('subtotal');
            $table->decimal('biaya_pbbkb', 15, 2)->nullable()->after('biaya_ongkos_angkut');
            $table->decimal('biaya_operasional_kerja', 15, 2)->nullable()->after('biaya_pbbkb');

            // Add boolean flags for including/excluding components
            $table->boolean('include_ppn')->default(true)->after('biaya_operasional_kerja');
            $table->boolean('include_pbbkb')->default(false)->after('include_ppn');
            $table->boolean('include_operasional_kerja')->default(false)->after('include_pbbkb');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice', function (Blueprint $table) {
            $table->dropColumn([
                'biaya_ongkos_angkut',
                'biaya_pbbkb',
                'biaya_operasional_kerja',
                'include_ppn',
                'include_pbbkb',
                'include_operasional_kerja'
            ]);
        });
    }
};
