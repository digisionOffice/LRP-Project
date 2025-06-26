<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('event_name')->index(); // Event pemicu notifikasi
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('channel')->default('whatsapp'); // Channel notifikasi
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
