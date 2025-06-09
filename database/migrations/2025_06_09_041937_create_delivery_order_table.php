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
        Schema::create('delivery_order', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->unique();
            $table->unsignedBigInteger('id_transaksi');
            $table->unsignedBigInteger('id_karyawan')->nullable();
            $table->unsignedBigInteger('id_kendaraan')->nullable();
            $table->datetime('tanggal_delivery')->nullable();
            $table->string('no_segel', 50)->nullable();
            $table->enum('status_muat', ['pending', 'muat', 'selesai'])->default('pending');
            $table->datetime('waktu_muat')->nullable();
            $table->datetime('waktu_selesai_muat')->nullable();
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
        Schema::dropIfExists('delivery_order');
    }
};
