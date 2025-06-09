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
            // Administration fields
            $table->string('do_signatory_name')->nullable()->after('no_segel');
            $table->boolean('do_print_status')->default(false)->after('do_signatory_name');
            $table->text('fuel_usage_notes')->nullable()->after('do_print_status');
            
            // Driver allowance fields
            $table->decimal('driver_allowance_amount', 10, 2)->nullable()->after('fuel_usage_notes');
            $table->boolean('allowance_receipt_status')->default(false)->after('driver_allowance_amount');
            $table->timestamp('allowance_receipt_time')->nullable()->after('allowance_receipt_status');
            
            // Driver delivery fields
            $table->boolean('do_handover_status')->default(false)->after('allowance_receipt_time');
            $table->timestamp('do_handover_time')->nullable()->after('do_handover_status');
            
            // Finance fields
            $table->string('invoice_number')->nullable()->after('do_handover_time');
            $table->string('tax_invoice_number')->nullable()->after('invoice_number');
            $table->boolean('invoice_delivery_status')->default(false)->after('tax_invoice_number');
            $table->boolean('invoice_archive_status')->default(false)->after('invoice_delivery_status');
            $table->boolean('invoice_confirmation_status')->default(false)->after('invoice_archive_status');
            $table->timestamp('invoice_confirmation_time')->nullable()->after('invoice_confirmation_status');
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'overdue'])->default('pending')->after('invoice_confirmation_time');
            
            // Add indexes for performance
            $table->index(['payment_status']);
            $table->index(['do_print_status']);
            $table->index(['invoice_delivery_status']);
            $table->index(['allowance_receipt_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_order', function (Blueprint $table) {
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['do_print_status']);
            $table->dropIndex(['invoice_delivery_status']);
            $table->dropIndex(['allowance_receipt_status']);
            
            $table->dropColumn([
                'do_signatory_name',
                'do_print_status',
                'fuel_usage_notes',
                'driver_allowance_amount',
                'allowance_receipt_status',
                'allowance_receipt_time',
                'do_handover_status',
                'do_handover_time',
                'invoice_number',
                'tax_invoice_number',
                'invoice_delivery_status',
                'invoice_archive_status',
                'invoice_confirmation_status',
                'invoice_confirmation_time',
                'payment_status',
            ]);
        });
    }
};
