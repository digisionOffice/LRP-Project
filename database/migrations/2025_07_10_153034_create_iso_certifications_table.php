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
        Schema::create('iso_certifications', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('certificate_number')->nullable();
            $table->string('logo_path')->nullable();
            $table->year('active_year');
            $table->year('end_year');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iso_certifications');
    }
};
