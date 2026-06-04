<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Change column default to 'admin' for any future users
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('admin')->change();
        });

        // All users except owner get 'admin' role
        DB::table('users')
            ->where('email', '!=', 'owner@gocare.com')
            ->update(['role' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('super_admin')->change();
        });
    }
};
