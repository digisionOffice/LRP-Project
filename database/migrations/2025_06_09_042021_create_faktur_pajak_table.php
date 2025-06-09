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
        Schema::create('faktur_pajak', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_faktur', 100)->unique();
            $table->unsignedBigInteger('id_transaksi_penjualan');
            $table->datetime('tanggal_faktur');
            $table->string('npwp_pelanggan', 50)->nullable();
            $table->string('nama_pelanggan');
            $table->float('total_dpp');
            $table->float('total_ppn');
            $table->enum('status', ['draft', 'submitted', 'approved'])->default('draft');
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
        Schema::dropIfExists('faktur_pajak');
    }
};
