<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This removes the old, single-level approval fields.
     */
    public function up(): void
    {
        Schema::table('expense_requests', function (Blueprint $table) {
            // It's important to drop the foreign key before dropping the column
            $table->dropForeign(['approved_by']);

            $table->dropColumn([
                'approved_by',
                'approved_at',
                'approval_notes',
                'rejection_reason'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     * This adds the old columns back if we need to rollback.
     */
    public function down(): void
    {
        Schema::table('expense_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('approved_by')->nullable()->after('requested_by');
            $table->timestamp('approved_at')->nullable()->after('reviewed_at');
            $table->text('approval_notes')->nullable()->after('paid_at');
            $table->text('rejection_reason')->nullable()->after('approval_notes');

            // Re-add the foreign key constraint
            $table->foreign('approved_by')->references('id')->on('users');
        });
    }
};
