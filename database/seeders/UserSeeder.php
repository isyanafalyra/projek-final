<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::updateOrCreate(
            ['email' => 'admin@globalchain.com'],
            [
                'name' => 'Admin Global Chain',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ]
        );

        // User Biasa
        User::updateOrCreate(
            ['email' => 'user@globalchain.com'],
            [
                'name' => 'John Doe User',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ]
        );
    }
}
