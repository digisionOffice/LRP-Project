<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            if (!Schema::hasColumn('karyawan', 'supervisor_id')) {
                $table->unsignedBigInteger('supervisor_id')->nullable()->after('id_user');
                $table->foreign('supervisor_id')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            if (Schema::hasColumn('karyawan', 'supervisor_id')) {
                $table->dropForeign(['supervisor_id']);
                $table->dropColumn('supervisor_id');
            }
        });
    }
};
