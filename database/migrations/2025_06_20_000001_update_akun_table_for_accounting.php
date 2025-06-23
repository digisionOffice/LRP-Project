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
        Schema::table('akun', function (Blueprint $table) {
            // Add new columns for accounting module
            $table->enum('kategori_akun', ['Aset', 'Kewajiban', 'Ekuitas', 'Pendapatan', 'Beban'])->after('nama_akun');
            $table->decimal('saldo_awal', 15, 2)->nullable()->after('kategori_akun');
            
            // Update tipe_akun enum to match accounting standards
            $table->dropColumn('tipe_akun');
        });

        Schema::table('akun', function (Blueprint $table) {
            $table->enum('tipe_akun', ['Debit', 'Kredit'])->after('kategori_akun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('akun', function (Blueprint $table) {
            $table->dropColumn(['kategori_akun', 'saldo_awal']);
            $table->dropColumn('tipe_akun');
        });

        Schema::table('akun', function (Blueprint $table) {
            $table->enum('tipe_akun', ['Aktiva', 'Kewajiban', 'Modal', 'Pendapatan', 'Beban'])->after('nama_akun');
        });
    }
};
