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
        // Add performance indexes to existing tables
        Schema::table('transaksi_penjualan', function (Blueprint $table) {
            $table->index(['kode']);
            $table->index(['nomor_po']);
            $table->index(['tanggal']);
            $table->index(['id_pelanggan']);
            $table->index(['id_tbbm']);
        });

        Schema::table('delivery_order', function (Blueprint $table) {
            $table->index(['kode']);
            $table->index(['status_muat']);
            $table->index(['tanggal_delivery']);
            $table->index(['id_transaksi']);
            $table->index(['id_user']);
            $table->index(['id_kendaraan']);
        });

        Schema::table('uang_jalan', function (Blueprint $table) {
            $table->index(['status_kirim']);
            $table->index(['status_terima']);
            $table->index(['id_do']);
            $table->index(['id_user']);
        });

        Schema::table('pengiriman_driver', function (Blueprint $table) {
            $table->index(['id_do']);
        });

        Schema::table('faktur_pajak', function (Blueprint $table) {
            $table->index(['nomor_faktur']);
            $table->index(['status']);
            $table->index(['id_transaksi_penjualan']);
        });

        Schema::table('pelanggan', function (Blueprint $table) {
            $table->index(['kode']);
            $table->index(['nama']);
            $table->index(['type']);
        });

        Schema::table('item', function (Blueprint $table) {
            $table->index(['kode']);
            $table->index(['name']);
            $table->index(['id_item_jenis']);
        });

        // Karyawan table removed - employee data now in users table
        Schema::table('users', function (Blueprint $table) {
            $table->index(['no_induk']);
            $table->index(['name']);
            $table->index(['role']);
            $table->index(['id_jabatan']);
            $table->index(['id_divisi']);
            $table->index(['is_active']);
        });

        Schema::table('kendaraans', function (Blueprint $table) {
            $table->index(['no_pol_kendaraan']);
            $table->index(['merk']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_penjualan', function (Blueprint $table) {
            $table->dropIndex(['kode']);
            $table->dropIndex(['nomor_po']);
            $table->dropIndex(['tanggal']);
            $table->dropIndex(['id_pelanggan']);
            $table->dropIndex(['id_tbbm']);
        });

        Schema::table('delivery_order', function (Blueprint $table) {
            $table->dropIndex(['kode']);
            $table->dropIndex(['status_muat']);
            $table->dropIndex(['tanggal_delivery']);
            $table->dropIndex(['id_transaksi']);
            $table->dropIndex(['id_user']);
            $table->dropIndex(['id_kendaraan']);
        });

        Schema::table('uang_jalan', function (Blueprint $table) {
            $table->dropIndex(['status_kirim']);
            $table->dropIndex(['status_terima']);
            $table->dropIndex(['id_do']);
            $table->dropIndex(['id_user']);
        });

        Schema::table('pengiriman_driver', function (Blueprint $table) {
            $table->dropIndex(['id_do']);
        });

        Schema::table('faktur_pajak', function (Blueprint $table) {
            $table->dropIndex(['nomor_faktur']);
            $table->dropIndex(['status']);
            $table->dropIndex(['id_transaksi_penjualan']);
        });

        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropIndex(['kode']);
            $table->dropIndex(['nama']);
            $table->dropIndex(['type']);
        });

        Schema::table('item', function (Blueprint $table) {
            $table->dropIndex(['kode']);
            $table->dropIndex(['name']);
            $table->dropIndex(['id_item_jenis']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['no_induk']);
            $table->dropIndex(['name']);
            $table->dropIndex(['role']);
            $table->dropIndex(['id_jabatan']);
            $table->dropIndex(['id_divisi']);
            $table->dropIndex(['is_active']);
        });

        Schema::table('kendaraans', function (Blueprint $table) {
            $table->dropIndex(['no_pol_kendaraan']);
            $table->dropIndex(['merk']);
        });
    }
};
