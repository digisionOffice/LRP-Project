<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            // Add photo metadata fields if they don't exist
            if (!Schema::hasColumn('absensi', 'metadata_foto_masuk')) {
                $table->json('metadata_foto_masuk')->nullable()->after('foto_masuk');
            }
            if (!Schema::hasColumn('absensi', 'metadata_foto_keluar')) {
                $table->json('metadata_foto_keluar')->nullable()->after('foto_keluar');
            }
        });
    }

    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropColumn(['metadata_foto_masuk', 'metadata_foto_keluar']);
        });
    }
};
