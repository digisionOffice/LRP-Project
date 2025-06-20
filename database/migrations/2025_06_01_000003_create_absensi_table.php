<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('karyawan_id');
            $table->unsignedBigInteger('jadwal_id')->nullable(); // Link to jadwal_kerja
            $table->date('tanggal_absensi');
            $table->time('waktu_masuk')->nullable();
            $table->time('waktu_keluar')->nullable();
            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'cuti', 'alpha'])->default('hadir');
            $table->text('keterangan')->nullable();
            $table->string('lokasi_masuk')->nullable(); // For GPS tracking
            $table->decimal('latitude_masuk', 10, 8)->nullable(); // GPS latitude for check-in
            $table->decimal('longitude_masuk', 11, 8)->nullable(); // GPS longitude for check-in
            $table->string('lokasi_keluar')->nullable(); // For GPS tracking
            $table->decimal('latitude_keluar', 10, 8)->nullable(); // GPS latitude for check-out
            $table->decimal('longitude_keluar', 11, 8)->nullable(); // GPS longitude for check-out
            $table->string('foto_masuk')->nullable(); // For selfie verification
            $table->string('foto_keluar')->nullable(); // For selfie verification
            $table->json('metadata_foto_masuk')->nullable(); // Photo metadata for check-in
            $table->json('metadata_foto_keluar')->nullable(); // Photo metadata for check-out
            $table->unsignedBigInteger('approved_by')->nullable(); // Supervisor who approved
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('karyawan_id')->references('id')->on('karyawan')->onDelete('cascade');
            $table->foreign('jadwal_id')->references('id')->on('jadwal_kerja')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');

            // Indexes for better performance
            $table->index(['karyawan_id', 'tanggal_absensi']);
            $table->index(['tanggal_absensi']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
