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
        Schema::create('alamat_pelanggan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pelanggan');
            $table->text('alamat');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('id_pelanggan')->references('id')->on('pelanggan')->onDelete('cascade');

            // Index for performance
            $table->index(['id_pelanggan']);
            $table->index(['is_primary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alamat_pelanggan');
    }
};
