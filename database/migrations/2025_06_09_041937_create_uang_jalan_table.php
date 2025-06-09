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
        Schema::create('uang_jalan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_do');
            $table->float('nominal');
            $table->enum('status_kirim', ['pending', 'kirim', 'ditolak'])->default('pending');
            $table->string('bukti_kirim')->nullable();
            $table->enum('status_terima', ['pending', 'terima', 'ditolak'])->default('pending');
            $table->string('bukti_terima')->nullable();
            $table->unsignedBigInteger('id_karyawan')->nullable();
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
        Schema::dropIfExists('uang_jalan');
    }
};
