<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_assets', function (Blueprint $table) {
            $table->unsignedSmallInteger('compressor_manufacturing_year')->nullable()->after('compressor_model');
        });
    }

    public function down(): void
    {
        Schema::table('customer_assets', function (Blueprint $table) {
            $table->dropColumn('compressor_manufacturing_year');
        });
    }
};
