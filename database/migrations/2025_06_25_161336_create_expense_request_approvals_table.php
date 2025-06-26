<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This creates the new table to store approval history.
     */
    public function up(): void
    {
        Schema::create('expense_request_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_request_id')->constrained('expense_requests')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // The user who performed the action
            
            $table->string('status'); // e.g., 'approved', 'rejected', 'needs_revision'
            $table->text('note')->nullable(); // The reason/note for this approval step
            $table->integer('step_sequence')->default(1); // For multi-level approval workflows
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * This drops the new approvals table.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_request_approvals');
    }
};
