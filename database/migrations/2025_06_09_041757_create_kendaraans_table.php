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
        Schema::create('kendaraans', function (Blueprint $table) {
            $table->id();
            $table->string('no_pol_kendaraan', 50)->unique();
            $table->string('merk')->nullable();
            $table->string('tipe', 100)->nullable();
            $table->float('kapasitas')->nullable();
            $table->integer('kapasitas_satuan')->nullable();
            $table->datetime('tanggal_awal_valid')->nullable();
            $table->datetime('tanggal_akhir_valid')->nullable();
            $table->text('deskripsi')->nullable();
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
        Schema::dropIfExists('kendaraans');
    }
};
