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
        Schema::create('transaksi_penjualan_approvals', function (Blueprint $table) {
            $table->id();
            // This creates the foreign key and an index on this column.
            $table->foreignId('id_transaksi_penjualan')->constrained('transaksi_penjualan')->cascadeOnDelete();
            $table->enum('status', ['approved', 'rejected', 'reject_with_perbaikan']);
            $table->text('note')->nullable();
            // This also creates a foreign key and an index.
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_penjualan_approvals');
    }
};
