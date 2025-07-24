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
        // This table stores the main header information for a Quotation (SPH)
        Schema::create('sph', function (Blueprint $table) {
            $table->id();
            $table->string('sph_number')->unique();
            $table->foreignId('customer_id')->constrained('pelanggan')->restrictOnDelete();
            $table->date('sph_date');
            $table->date('valid_until_date')->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'sent', 'accepted', 'rejected', 'expired'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->text('terms_and_conditions')->nullable();
            $table->text('notes_internal')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // This table stores the line items (products/services) for each SPH
        Schema::create('sph_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sph_id')->constrained('sph')->onDelete('cascade');
            $table->foreignId('item_id')->nullable()->constrained('item')->nullOnDelete();
            $table->string('description'); // To allow for custom service descriptions
            $table->decimal('quantity', 15, 2);
            $table->decimal('price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop in reverse order of creation to respect foreign key constraints
        Schema::dropIfExists('sph_details');
        Schema::dropIfExists('sph');
    }
};
