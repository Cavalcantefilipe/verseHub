<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BibleVersion;

class BibleVersionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $versions = [
            [
                'slug' => 'acf',
                'name' => 'Almeida Corrigida Fiel',
                'language' => 'pt'
            ],
            [
                'slug' => 'apee',
                'name' => 'A Palavra de Deus para Todos',
                'language' => 'pt'
            ],
            [
                'slug' => 'bbe',
                'name' => 'Bible in Basic English',
                'language' => 'en'
            ],
            [
                'slug' => 'kjv',
                'name' => 'King James Version',
                'language' => 'en'
            ],
            [
                'slug' => 'nvi',
                'name' => 'Nova Versão Internacional',
                'language' => 'pt'
            ],
            [
                'slug' => 'ra',
                'name' => 'Almeida Revista e Atualizada',
                'language' => 'pt'
            ],
            [
                'slug' => 'rvr',
                'name' => 'Reina Valera Revisada',
                'language' => 'es'
            ]
        ];

        foreach ($versions as $version) {
            BibleVersion::updateOrCreate(
                ['slug' => $version['slug']],
                $version
            );
        }
    }
}
