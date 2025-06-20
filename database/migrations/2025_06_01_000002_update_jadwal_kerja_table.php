<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_kerja', function (Blueprint $table) {
            // Check if columns exist before adding them
            if (!Schema::hasColumn('jadwal_kerja', 'shift_id')) {
                $table->unsignedBigInteger('shift_id')->nullable()->after('karyawan_id');
                $table->foreign('shift_id')->references('id')->on('shift')->onDelete('set null');
            }

            if (!Schema::hasColumn('jadwal_kerja', 'supervisor_id')) {
                $table->unsignedBigInteger('supervisor_id')->nullable()->after('shift_id');
                $table->foreign('supervisor_id')->references('id')->on('users')->onDelete('set null');
            }

            if (!Schema::hasColumn('jadwal_kerja', 'is_approved')) {
                $table->boolean('is_approved')->default(false)->after('keterangan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_kerja', function (Blueprint $table) {
            // Check if columns exist before dropping them
            if (Schema::hasColumn('jadwal_kerja', 'shift_id')) {
                $table->dropForeign(['shift_id']);
                $table->dropColumn('shift_id');
            }

            if (Schema::hasColumn('jadwal_kerja', 'supervisor_id')) {
                $table->dropForeign(['supervisor_id']);
                $table->dropColumn('supervisor_id');
            }

            if (Schema::hasColumn('jadwal_kerja', 'is_approved')) {
                $table->dropColumn('is_approved');
            }
        });
    }
};
