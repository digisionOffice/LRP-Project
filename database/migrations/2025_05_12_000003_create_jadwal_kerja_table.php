<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_kerja', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('karyawan_id');
            $table->date('tanggal_jadwal');
            $table->time('waktu_masuk')->nullable();
            $table->time('waktu_keluar')->nullable();
            $table->string('status')->nullable(); // Default 'Hadir', 'Libur', 'Cuti', 'Izin'
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('karyawan_id')->references('id')->on('karyawan')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_kerja');
    }
};
