<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('fabrication_number')->unique();
            $table->string('compressor_model');
            $table->string('service_dealer')->default('Go Care Solutions');
            $table->string('product');
            $table->string('compressor_make');
            $table->string('service_engineer')->nullable();
            $table->string('contact_person_name')->nullable();
            $table->string('contact_person_mail')->nullable();
            $table->string('contact_person_number')->nullable();
            $table->string('alternate_person_name')->nullable();
            $table->string('alternate_person_mail')->nullable();
            $table->string('alternate_person_number')->nullable();
            $table->decimal('hours_meter_reading', 10, 2)->nullable();
            $table->date('hmr_date')->nullable();
            $table->boolean('amc')->default(false);
            $table->date('amc_start_date')->nullable();
            $table->date('amc_end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_assets');
    }
};
