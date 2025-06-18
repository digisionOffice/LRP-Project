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
        Schema::create('tax_invoice', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_tax_invoice', 100)->unique();
            $table->unsignedBigInteger('id_invoice');
            $table->unsignedBigInteger('id_do');
            $table->unsignedBigInteger('id_transaksi');
            $table->datetime('tanggal_tax_invoice');
            $table->string('nama_pelanggan');
            $table->text('alamat_pelanggan')->nullable();
            $table->string('npwp_pelanggan', 50)->nullable();
            $table->string('nama_perusahaan');
            $table->text('alamat_perusahaan');
            $table->string('npwp_perusahaan', 50);
            $table->decimal('dasar_pengenaan_pajak', 15, 2); // DPP
            $table->decimal('tarif_pajak', 5, 2)->default(11.00); // PPN rate (usually 11%)
            $table->decimal('pajak_pertambahan_nilai', 15, 2); // PPN amount
            $table->decimal('total_tax_invoice', 15, 2);
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['nomor_tax_invoice']);
            $table->index(['status']);
            $table->index(['tanggal_tax_invoice']);
            $table->index(['id_invoice']);
            $table->index(['id_do']);
            $table->index(['id_transaksi']);
            $table->index(['npwp_pelanggan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_invoice');
    }
};
