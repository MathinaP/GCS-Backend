<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        // admin@gocare.com — regular admin, no expense access
        DB::table('users')
            ->where('email', 'admin@gocare.com')
            ->update(['role' => 'admin']);

        // Create owner@gocare.com as super_admin if not exists
        $exists = DB::table('users')->where('email', 'owner@gocare.com')->exists();
        if (!$exists) {
            DB::table('users')->insert([
                'name'       => 'Owner',
                'email'      => 'owner@gocare.com',
                'password'   => Hash::make('Superadmin@gcs'),
                'role'       => 'super_admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('users')
                ->where('email', 'owner@gocare.com')
                ->update(['role' => 'super_admin']);
        }
    }

    public function down(): void
    {
        DB::table('users')->where('email', 'owner@gocare.com')->delete();
        DB::table('users')->where('email', 'admin@gocare.com')->update(['role' => 'super_admin']);
    }
};
