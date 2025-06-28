<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This creates the activity_logs table to store a history of model updates.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // This will store the User ID of who made the change.
            // It's nullable in case a change is made by the system/seeder.
            // onDelete('set null') means if the user is deleted, this log entry remains but user_id becomes NULL.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            // This creates two columns: 'loggable_id' (unsignedBigInteger) and 'loggable_type' (string).
            // This is the polymorphic relationship that allows this table to connect to ANY other model.
            $table->morphs('loggable');
            
            // This column stores the actual changes in a structured JSON format.
            $table->json('changes');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * This safely drops the table if you need to rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
