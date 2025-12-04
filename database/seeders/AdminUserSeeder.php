<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@bank.local'], // غيّر هذا الإيميل لما تريد
            [
                'name' => 'System Admin',
                'phone' => '0000000000',
                'password' => Hash::make('Admin@12345'), // غيّر الباسورد لو حاب
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
