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
        Schema::table('pengiriman_driver', function (Blueprint $table) {
            // Add missing driver delivery fields
            $table->float('totalisator_pool_return')->nullable()->after('foto_pengiriman');
            $table->datetime('waktu_pool_arrival')->nullable()->after('totalisator_pool_return');
            
            // Add indexes for performance
            $table->index(['waktu_mulai']);
            $table->index(['waktu_tiba']);
            $table->index(['waktu_pool_arrival']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengiriman_driver', function (Blueprint $table) {
            $table->dropIndex(['waktu_mulai']);
            $table->dropIndex(['waktu_tiba']);
            $table->dropIndex(['waktu_pool_arrival']);
            
            $table->dropColumn([
                'totalisator_pool_return',
                'waktu_pool_arrival',
            ]);
        });
    }
};
