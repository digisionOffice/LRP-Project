<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This adds the 'used_in_transaction' status to the enum list.
     */
    public function up(): void
    {
        // No data update is needed because we are only adding a new option.
        
        Schema::table('sph', function (Blueprint $table) {
            // Define the new, expanded set of allowed values for the enum
            $newStatusOptions = [
                'draft',
                'pending_approval',
                'needs_revision',
                'sent',
                'approved',
                'rejected',
                'expired',
                'used_in_transaction' // <-- ADDED
            ];

            // Use the ->change() method to modify the existing column
            $table->enum('status', $newStatusOptions)
                  ->default('draft')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     * This reverts the 'status' column back to its original state (without the new status).
     */
    public function down(): void
    {
        Schema::table('sph', function (Blueprint $table) {
            // Define the previous set of allowed values to allow a clean rollback
            $previousStatusOptions = [
                'draft',
                'pending_approval',
                'needs_revision',
                'sent',
                'approved',
                'rejected',
                'expired'
            ];

            // Revert the column to its original definition
            $table->enum('status', $previousStatusOptions)
                  ->default('draft')
                  ->change();
        });
    }
};
