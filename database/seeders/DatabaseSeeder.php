<?php

namespace Database\Seeders;

// use App\Models\User; // Tidak diperlukan karena tidak memanggil factory User
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Panggil semua seeder yang dibutuhkan, termasuk UserSeeder
        $this->call([
            UserSeeder::class, // <-- Baris ini memastikan UserSeeder dijalankan
        ]);

        // Kode bawaan (seperti User::factory()->create...) dihapus/diabaikan
    }
}