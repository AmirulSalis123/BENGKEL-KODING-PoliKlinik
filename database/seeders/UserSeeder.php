<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User; // PENTING: Import model User
use Illuminate\Support\Facades\Hash; // PENTING: Import Hash facade

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('admin'), // Menggunakan Hash::make()
                'role' => 'admin',
            ],
            [
                'name' => 'Dokter',
                'email' => 'dokter@gmail.com',
                'password' => Hash::make('dokter'), // Menggunakan Hash::make()
                'role' => 'dokter',
            ],
            
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}