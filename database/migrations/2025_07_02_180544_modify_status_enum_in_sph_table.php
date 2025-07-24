<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This changes the 'accepted' status to 'approved' for consistency and adds 'needs_revision'.
     */
    public function up(): void
    {
        // --- FIX: Temporarily change the column to a string to allow any value ---
        Schema::table('sph', function (Blueprint $table) {
            $table->string('status', 50)->change();
        });

        // Now that the column can hold any string, we can safely update the data.
        DB::table('sph')->where('status', 'accepted')->update(['status' => 'approved']);

        // Finally, change the column back to the new, correct enum definition.
        Schema::table('sph', function (Blueprint $table) {
            $newStatusOptions = [
                'draft',
                'pending_approval',
                'needs_revision',
                'sent',
                'approved',
                'rejected',
                'expired'
            ];
            $table->enum('status', $newStatusOptions)
                  ->default('draft')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     * This reverts the 'status' column back to its original state.
     */
    public function down(): void
    {
        // To reverse, we do the same process in the opposite order.
        Schema::table('sph', function (Blueprint $table) {
            $table->string('status', 50)->change();
        });

        // Revert any 'approved' records back to 'accepted' before changing the column
        DB::table('sph')->where('status', 'approved')->update(['status' => 'accepted']);

        Schema::table('sph', function (Blueprint $table) {
            // Define the original set of allowed values
            $originalStatusOptions = [
                'draft',
                'pending_approval',
                'sent',
                'accepted', // Reverted back
                'rejected',
                'expired'
            ];

            // Revert the column to its original definition
            $table->enum('status', $originalStatusOptions)
                  ->default('draft')
                  ->change();
        });
    }
};
