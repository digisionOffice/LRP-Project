<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('karyawan', function (Blueprint $table) {
            $table->id();
            $table->string('no_induk')->unique();
            $table->string('nama');
            $table->string('hp')->nullable();
            $table->string('email')->nullable();
            $table->unsignedBigInteger('id_jabatan')->nullable();
            $table->unsignedBigInteger('id_divisi')->nullable();
            $table->unsignedBigInteger('id_entitas')->nullable();
            $table->unsignedBigInteger('id_user')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            // $table->foreign('id_jabatan')->references('id')->on('jabatan')->onDelete('set null');
            // $table->foreign('id_divisi')->references('id')->on('divisi')->onDelete('set null');
            // $table->foreign('id_entitas')->references('id')->on('entitas')->onDelete('set null');
            // $table->foreign('id_user')->references('id')->on('users')->onDelete('set null');
            // $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index(['id_jabatan']);
            $table->index(['id_divisi']);
            $table->index(['id_entitas']);
            $table->index(['id_user']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('karyawan');
    }
};
