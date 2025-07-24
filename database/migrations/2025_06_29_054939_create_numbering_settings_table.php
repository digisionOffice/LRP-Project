<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('numbering_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique(); // e.g., 'sph', 'expense_request'
            $table->string('prefix'); // e.g., 'SPH'
            $table->string('suffix')->nullable(); // e.g., 'LRP'
            $table->unsignedTinyInteger('sequence_digits')->default(4);
            $table->string('format'); // e.g., '{PREFIX}/{YEAR}/{MONTH_ROMAN}/{SEQUENCE}'
            $table->enum('reset_frequency', ['daily', 'monthly', 'yearly', 'never'])->default('monthly');
            $table->unsignedInteger('last_sequence')->default(0);
            $table->date('last_reset_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('numbering_settings');
    }
};
