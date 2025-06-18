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
        Schema::table('delivery_order', function (Blueprint $table) {
            $table->float('volume_do')->nullable()->after('waktu_selesai_muat')->comment('Volume yang akan dikirim dalam DO ini');
            $table->float('sisa_volume_do')->nullable()->after('volume_do')->comment('Sisa volume dari SO yang belum dikirim');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_order', function (Blueprint $table) {
            $table->dropColumn(['volume_do', 'sisa_volume_do']);
        });
    }
};
