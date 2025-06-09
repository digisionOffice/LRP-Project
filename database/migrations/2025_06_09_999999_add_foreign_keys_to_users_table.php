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
        // Add foreign key constraints to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('id_jabatan')->references('id')->on('jabatan')->onDelete('set null');
            $table->foreign('id_divisi')->references('id')->on('divisi')->onDelete('set null');
            $table->foreign('id_entitas')->references('id')->on('entitas')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        // Add foreign key constraints to delivery_order table
        Schema::table('delivery_order', function (Blueprint $table) {
            $table->foreign('id_user')->references('id')->on('users')->onDelete('set null');
        });

        // Add foreign key constraints to uang_jalan table
        Schema::table('uang_jalan', function (Blueprint $table) {
            $table->foreign('id_user')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_jabatan']);
            $table->dropForeign(['id_divisi']);
            $table->dropForeign(['id_entitas']);
            $table->dropForeign(['created_by']);
        });

        Schema::table('delivery_order', function (Blueprint $table) {
            $table->dropForeign(['id_user']);
        });

        Schema::table('uang_jalan', function (Blueprint $table) {
            $table->dropForeign(['id_user']);
        });
    }
};
