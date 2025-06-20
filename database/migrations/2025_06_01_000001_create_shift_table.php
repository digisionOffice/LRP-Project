<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift', function (Blueprint $table) {
            $table->id();
            $table->string('nama_shift');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->integer('toleransi_keterlambatan')->default(0); // Dalam menit

            // Split shift fields
            $table->boolean('is_split_shift')->default(false);
            $table->time('waktu_mulai_periode2')->nullable(); // Start time for second period
            $table->time('waktu_selesai_periode2')->nullable(); // End time for second period
            $table->integer('toleransi_keterlambatan_periode2')->nullable(); // Tolerance for second period

            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift');
    }
};
