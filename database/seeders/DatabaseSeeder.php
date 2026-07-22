<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // As versões e os versículos legados foram desativados e suas tabelas
        // recebem o sufixo `_off`. A leitura bíblica atual vem da API/cache.
        $this->call([
            CategorySeeder::class,
        ]);
    }
}
