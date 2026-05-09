<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('material_id')->nullable()->constrained('materials')->nullOnDelete();
            $table->integer('sl_no');
            $table->text('description');
            $table->string('hsn_sac')->nullable();
            $table->decimal('quantity', 10, 3)->default(1);
            $table->string('unit')->nullable();
            $table->decimal('rate', 10, 2)->default(0);
            $table->string('per')->nullable();
            $table->decimal('discount_pct', 5, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('gst_rate', 5, 2)->default(0);
            $table->decimal('gst_amount', 12, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_items');
    }
};
