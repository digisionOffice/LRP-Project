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
        Schema::create('sph_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sph_id')->constrained('sph')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status'); // e.g., 'approved', 'rejected', 'needs_revision'
            $table->text('note')->nullable();
            $table->integer('step_sequence')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sph_approvals');
    }
};
