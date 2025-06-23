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
        // Tabel Aturan Posting (Posting Rules)
        Schema::create('posting_rules', function (Blueprint $table) {
            $table->id();
            $table->string('rule_name'); // Nama aturan posting
            $table->string('source_type'); // Sale, Purchase, Payment, Receipt, ManualAdjust, dll
            $table->json('trigger_condition')->nullable(); // Kondisi pemicu dalam format JSON
            $table->text('description')->nullable(); // Deskripsi aturan
            $table->boolean('is_active')->default(true); // Status aktif/non-aktif
            $table->integer('priority')->default(0); // Prioritas eksekusi (semakin kecil semakin tinggi)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Tabel Detail Entri Aturan Posting
        Schema::create('posting_rule_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('posting_rule_id');
            $table->unsignedBigInteger('account_id'); // Reference ke tabel akun
            $table->enum('dc_type', ['Debit', 'Credit']); // Tipe debit atau kredit
            $table->enum('amount_type', ['Fixed', 'SourceValue', 'Calculated']); // Tipe perhitungan jumlah
            $table->decimal('fixed_amount', 12, 2)->nullable(); // Jumlah tetap (jika amount_type = Fixed)
            $table->string('source_property')->nullable(); // Property dari source model (jika amount_type = SourceValue)
            $table->text('calculation_expression')->nullable(); // Ekspresi perhitungan (jika amount_type = Calculated)
            $table->text('description_template')->nullable(); // Template deskripsi dengan placeholder
            $table->integer('sort_order')->default(0); // Urutan tampilan
            $table->timestamps();

            $table->foreign('posting_rule_id')->references('id')->on('posting_rules')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('akun')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posting_rule_entries');
        Schema::dropIfExists('posting_rules');
    }
};
