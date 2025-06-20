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
        // Add approval fields to uang_jalan table
        Schema::table('uang_jalan', function (Blueprint $table) {
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('status_terima');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_notes')->nullable()->after('approved_at');

            // Add indexes for performance
            $table->index(['approval_status']);
            $table->index(['approved_by']);
            $table->index(['approved_at']);
        });

        // Add approval fields to pengiriman_driver table
        Schema::table('pengiriman_driver', function (Blueprint $table) {
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('waktu_pool_arrival');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_notes')->nullable()->after('approved_at');

            // Add indexes for performance
            $table->index(['approval_status']);
            $table->index(['approved_by']);
            $table->index(['approved_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uang_jalan', function (Blueprint $table) {
            $table->dropIndex(['approval_status']);
            $table->dropIndex(['approved_by']);
            $table->dropIndex(['approved_at']);

            $table->dropColumn([
                'approval_status',
                'approved_by',
                'approved_at',
                'approval_notes',
            ]);
        });

        Schema::table('pengiriman_driver', function (Blueprint $table) {
            $table->dropIndex(['approval_status']);
            $table->dropIndex(['approved_by']);
            $table->dropIndex(['approved_at']);

            $table->dropColumn([
                'approval_status',
                'approved_by',
                'approved_at',
                'approval_notes',
            ]);
        });
    }
};
