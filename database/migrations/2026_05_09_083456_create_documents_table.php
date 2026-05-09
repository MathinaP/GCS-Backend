<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['invoice', 'proforma_invoice', 'purchase_order', 'quotation']);
            $table->string('doc_number');
            $table->date('date');
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft');

            // Party
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('consignee_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();

            // Right-column meta
            $table->string('reference_no')->nullable();
            $table->date('reference_date')->nullable();
            $table->string('other_reference')->nullable();
            $table->string('delivery_note')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('buyers_order_no')->nullable();
            $table->date('buyers_order_date')->nullable();
            $table->string('dispatch_doc_no')->nullable();
            $table->date('delivery_note_date')->nullable();
            $table->string('dispatched_through')->nullable();
            $table->string('destination')->nullable();
            $table->string('terms_of_delivery')->nullable();
            $table->string('quotation_no')->nullable();
            $table->date('quotation_date')->nullable();
            $table->decimal('packing_charges', 10, 2)->nullable();
            $table->string('pr_no')->nullable();
            $table->string('quotation_validity')->nullable();

            // Totals
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('cgst_amount', 12, 2)->default(0);
            $table->decimal('sgst_amount', 12, 2)->default(0);
            $table->decimal('igst_amount', 12, 2)->default(0);
            $table->decimal('round_off', 6, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
