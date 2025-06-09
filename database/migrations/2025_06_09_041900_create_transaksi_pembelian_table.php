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
        Schema::create('transaksi_pembelian', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->unique();
            $table->unsignedBigInteger('id_supplier');
            $table->unsignedBigInteger('id_item');
            $table->float('volume_item');
            $table->float('harga_beli');
            $table->unsignedBigInteger('id_TBBM')->nullable();
            $table->unsignedBigInteger('id_akun_biaya')->nullable();
            $table->unsignedBigInteger('id_akun_utang')->nullable();
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
        Schema::dropIfExists('transaksi_pembelian');
    }
};
