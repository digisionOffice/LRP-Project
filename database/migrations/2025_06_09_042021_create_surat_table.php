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
        Schema::create('surat', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat', 100)->unique();
            $table->enum('jenis_surat', ['penawaran', 'kontrak', 'invoice', 'lainnya']);
            $table->datetime('tanggal_surat');
            $table->unsignedBigInteger('id_pelanggan')->nullable();
            $table->unsignedBigInteger('id_supplier')->nullable();
            $table->text('isi_surat')->nullable();
            $table->enum('status', ['draft', 'approved', 'rejected'])->default('draft');
            $table->enum('status_pembayaran', ['belum bayar', 'sudah bayar', 'terlambat'])->default('belum bayar');
            $table->datetime('tanggal_pembayaran')->nullable();
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
        Schema::dropIfExists('surat');
    }
};
