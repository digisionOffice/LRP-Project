<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This adds the 'paid_by' column to track who made the payment.
     */
    public function up(): void
    {
        Schema::table('expense_requests', function (Blueprint $table) {
            // Add the new column after the 'paid_at' column for logical grouping.
            // It's nullable because it only has a value when the status is 'paid'.
            $table->foreignId('paid_by')
                  ->nullable()
                  ->after('paid_at')
                  ->constrained('users') // Creates a foreign key to the 'id' on the 'users' table.
                  ->nullOnDelete();   // If the user is deleted, set this field to NULL.
        });
    }

    /**
     * Reverse the migrations.
     * This safely removes the column and its foreign key if you need to rollback.
     */
    public function down(): void
    {
        Schema::table('expense_requests', function (Blueprint $table) {
            // It's important to drop the foreign key constraint before dropping the column.
            $table->dropForeign(['paid_by']);
            $table->dropColumn('paid_by');
        });
    }
};
