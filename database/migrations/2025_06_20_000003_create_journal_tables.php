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
        // Tabel Header Jurnal
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->string('journal_number')->unique(); // Auto-generated journal number
            $table->date('transaction_date');
            $table->string('reference_number')->nullable(); // Reference ke dokumen sumber
            $table->string('source_type')->nullable(); // Sale, Purchase, Payment, Receipt, dll
            $table->unsignedBigInteger('source_id')->nullable(); // ID dari tabel sumber (sales_transactions, dll)
            $table->text('description');
            $table->enum('status', ['Draft', 'Posted', 'Cancelled', 'Error'])->default('Draft');
            $table->unsignedBigInteger('posting_rule_id')->nullable(); // Reference ke aturan posting yang digunakan
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Tabel Detail Entri Jurnal
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journal_id');
            $table->unsignedBigInteger('account_id');
            $table->text('description');
            $table->decimal('debit', 12, 2)->default(0);
            $table->decimal('credit', 12, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('journal_id')->references('id')->on('journals')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('akun')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('journals');
    }
};
