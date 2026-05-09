<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@gocare.com'],
            ['name' => 'Admin', 'password' => bcrypt('Gocare@2025')]
        );
    }
}
