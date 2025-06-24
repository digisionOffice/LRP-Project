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
        Schema::table('expense_requests', function (Blueprint $table) {
            // Add accounting integration fields
            $table->unsignedBigInteger('account_id')->nullable()->after('budget_code');
            $table->unsignedBigInteger('journal_id')->nullable()->after('account_id');
            
            // Add foreign key constraints
            $table->foreign('account_id')->references('id')->on('akun')->onDelete('set null');
            $table->foreign('journal_id')->references('id')->on('journals')->onDelete('set null');
            
            // Add indexes for better performance
            $table->index(['account_id', 'status']);
            $table->index(['journal_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_requests', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['account_id']);
            $table->dropForeign(['journal_id']);
            
            // Drop indexes
            $table->dropIndex(['account_id', 'status']);
            $table->dropIndex(['journal_id', 'status']);
            
            // Drop columns
            $table->dropColumn(['account_id', 'journal_id']);
        });
    }
};
