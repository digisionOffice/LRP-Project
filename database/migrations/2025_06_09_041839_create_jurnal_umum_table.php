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
        Schema::create('jurnal_umum', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_akun');
            $table->datetime('tanggal_transaksi');
            $table->text('deskripsi')->nullable();
            $table->float('debit')->default(0);
            $table->float('kredit')->default(0);
            $table->unsignedBigInteger('id_transaksi_penjualan')->nullable();
            $table->unsignedBigInteger('id_transaksi_pembelian')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal_umum');
    }
};
