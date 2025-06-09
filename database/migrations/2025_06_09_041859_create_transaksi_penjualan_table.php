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
        Schema::create('transaksi_penjualan', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->unique();
            $table->enum('tipe', ['dagang', 'jasa']);
            $table->datetime('tanggal');
            $table->unsignedBigInteger('id_pelanggan');
            $table->string('id_subdistrict', 10)->nullable();
            $table->text('alamat')->nullable();
            $table->string('nomor_po', 50)->nullable();
            $table->integer('top_pembayaran')->nullable(); // Dalam hari
            $table->unsignedBigInteger('id_tbbm')->nullable();
            $table->unsignedBigInteger('id_akun_pendapatan')->nullable();
            $table->unsignedBigInteger('id_akun_piutang')->nullable();
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
        Schema::dropIfExists('transaksi_penjualan');
    }
};
