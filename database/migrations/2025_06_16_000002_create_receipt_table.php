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
        Schema::create('receipt', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_receipt', 100)->unique();
            $table->unsignedBigInteger('id_invoice');
            $table->unsignedBigInteger('id_do');
            $table->unsignedBigInteger('id_transaksi');
            $table->datetime('tanggal_receipt');
            $table->datetime('tanggal_pembayaran');
            $table->string('metode_pembayaran', 50); // cash, transfer, check, etc.
            $table->string('referensi_pembayaran', 100)->nullable(); // bank reference, check number, etc.
            $table->decimal('jumlah_pembayaran', 15, 2);
            $table->decimal('biaya_admin', 15, 2)->default(0);
            $table->decimal('total_diterima', 15, 2);
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->text('catatan')->nullable();
            $table->string('bank_pengirim', 100)->nullable();
            $table->string('bank_penerima', 100)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['nomor_receipt']);
            $table->index(['status']);
            $table->index(['tanggal_receipt']);
            $table->index(['tanggal_pembayaran']);
            $table->index(['id_invoice']);
            $table->index(['id_do']);
            $table->index(['id_transaksi']);
            $table->index(['metode_pembayaran']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipt');
    }
};
