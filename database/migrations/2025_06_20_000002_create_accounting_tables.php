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
        // Tabel Inventaris (Stok Item)
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->integer('quantity')->default(0);
            $table->decimal('unit_cost', 12, 2)->nullable(); // Harga pokok per unit saat ini
            $table->decimal('total_value', 12, 2)->nullable(); // Total nilai inventaris
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('item_id')->references('id')->on('item')->onDelete('cascade');
            $table->unique('item_id'); // Satu item hanya punya satu record inventaris
        });

        // Tabel Transaksi Penjualan
        Schema::create('sales_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->unique(); // Auto-generated transaction code
            $table->date('transaction_date');
            $table->string('customer_name')->nullable();
            $table->enum('payment_method', ['Cash', 'Transfer', 'Credit'])->default('Cash');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Tabel Detail Item Penjualan
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_transaction_id');
            $table->unsignedBigInteger('item_id');
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('unit_cost', 12, 2); // Untuk perhitungan HPP
            $table->decimal('total_price', 12, 2);
            $table->decimal('total_cost', 12, 2); // Untuk perhitungan HPP
            $table->timestamps();

            $table->foreign('sales_transaction_id')->references('id')->on('sales_transactions')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('item')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales_transactions');
        Schema::dropIfExists('inventories');
    }
};
