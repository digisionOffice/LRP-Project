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
        Schema::create('pengiriman_driver', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_do');
            $table->float('totalisator_awal')->nullable();
            $table->float('totalisator_tiba')->nullable();
            $table->datetime('waktu_mulai')->nullable();
            $table->datetime('waktu_tiba')->nullable();
            $table->text('foto_pengiriman')->nullable();
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
        Schema::dropIfExists('pengiriman_driver');
    }
};
