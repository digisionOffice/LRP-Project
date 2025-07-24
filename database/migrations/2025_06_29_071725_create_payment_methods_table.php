<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This creates a centralized table to store company payment methods.
     */
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('method_name')->unique(); // e.g., 'Bank BNI Utama', 'Kas Kecil'
            $table->string('bank_name')->nullable(); // e.g., 'Bank BNI'
            $table->string('account_number')->nullable(); // e.g., '217736160'
            $table->string('account_name')->nullable(); // e.g., 'An. PT. Lintas Riau Prima'
            $table->text('notes')->nullable(); // For other details
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * This safely drops the table if you need to rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
