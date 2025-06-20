<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            // Add periode field to track which period this attendance is for
            if (!Schema::hasColumn('absensi', 'periode')) {
                $table->integer('periode')->default(1)->after('jadwal_id')
                    ->comment('1 for first period, 2 for second period in split shift');
            }

            // Add index for better performance
            $table->index(['karyawan_id', 'tanggal_absensi', 'periode'], 'idx_absensi_karyawan_tanggal_periode');
        });
    }

    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropIndex('idx_absensi_karyawan_tanggal_periode');

            if (Schema::hasColumn('absensi', 'periode')) {
                $table->dropColumn('periode');
            }
        });
    }
};
