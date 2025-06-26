<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // <-- ADDED: Import DB facade

// The filename for this migration would be something like:
// 2025_06_25_160000_modify_status_column_in_expense_requests_table.php

return new class extends Migration
{
    /**
     * Run the migrations.
     * This modifies the 'status' and 'submitted_at' columns.
     */
    public function up(): void
    {
        // --- ADDED STEP: Update existing data before changing the table structure ---
        // This prevents the "Data truncated" error by ensuring no 'draft' values exist.
        DB::table('expense_requests')
            ->where('status', 'draft')
            ->update(['status' => 'submitted', 'submitted_at' => now()]);

        Schema::table('expense_requests', function (Blueprint $table) {
            // --- 1. Modify the 'status' column ---
            $newStatusOptions = [
                'submitted',
                'under_review',
                'approved',
                'rejected',
                'paid'
            ];
            $table->enum('status', $newStatusOptions)
                  ->default('submitted')
                  ->comment('Default status is now submitted.')
                  ->change();

            // --- 2. ADDED: Modify the 'submitted_at' column ---
            // Set a default value to the current timestamp, as new records are now 'submitted' by default.
            $table->timestamp('submitted_at')
                  ->nullable(false) // Make it non-nullable since it will always have a value on creation
                  ->default(DB::raw('CURRENT_TIMESTAMP'))
                  ->comment('Timestamp is now set automatically on creation.')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     * This reverts the 'status' and 'submitted_at' columns back to their original state.
     */
    public function down(): void
    {
        Schema::table('expense_requests', function (Blueprint $table) {
            // --- 1. Revert the 'status' column ---
            $originalStatusOptions = [
                'draft',
                'submitted',
                'under_review',
                'approved',
                'rejected',
                'paid'
            ];
            $table->enum('status', $originalStatusOptions)
                  ->default('draft')
                  ->comment('') // Clear comment
                  ->change();

            // --- 2. ADDED: Revert the 'submitted_at' column ---
            // Revert back to a simple nullable timestamp with no default value.
            $table->timestamp('submitted_at')
                  ->nullable()
                  ->comment('') // Clear comment
                  ->change();
        });

        // It is generally not safe to automatically revert the data change,
        // so we don't change 'submitted' back to 'draft' here.
    }
};
