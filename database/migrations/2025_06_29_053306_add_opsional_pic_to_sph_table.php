<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This adds an optional PIC field to override the customer's default PIC if needed.
     */
    public function up(): void
    {
        Schema::table('sph', function (Blueprint $table) {
            // Renamed from 'contact_person' to 'opsional_pic' as per the new logic.
            $table->string('opsional_pic')->nullable()->after('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     * This removes the column if you need to rollback.
     */
    public function down(): void
    {
        Schema::table('sph', function (Blueprint $table) {
            $table->dropColumn('opsional_pic');
        });
    }
};
