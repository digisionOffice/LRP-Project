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
        Schema::create('invoice', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_invoice', 100)->unique();
            $table->unsignedBigInteger('id_do');
            $table->unsignedBigInteger('id_transaksi');
            $table->datetime('tanggal_invoice');
            $table->datetime('tanggal_jatuh_tempo');
            $table->string('nama_pelanggan');
            $table->text('alamat_pelanggan')->nullable();
            $table->string('npwp_pelanggan', 50)->nullable();
            $table->decimal('subtotal', 15, 2);
            $table->decimal('total_pajak', 15, 2)->default(0);
            $table->decimal('total_invoice', 15, 2);
            $table->decimal('total_terbayar', 15, 2)->default(0);
            $table->decimal('sisa_tagihan', 15, 2);
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['nomor_invoice']);
            $table->index(['status']);
            $table->index(['tanggal_invoice']);
            $table->index(['tanggal_jatuh_tempo']);
            $table->index(['id_do']);
            $table->index(['id_transaksi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice');
    }
};
