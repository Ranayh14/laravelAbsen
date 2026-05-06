<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@absen.com'],
            [
                'role' => 'admin',
                'nama' => 'Administrator Admin',
                'password' => \Illuminate\Support\Facades\Hash::make('@dminabsen123'),
            ]
        );
    }
}
