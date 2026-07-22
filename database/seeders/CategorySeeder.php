<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryGroup;
use Illuminate\Database\Seeder;

/**
 * VerseHub v2 — "Bíblia para momentos, não para temas".
 *
 * 4 grupos × 5 momentos = 20 categorias oficiais. A pergunta-âncora exibida
 * pro usuário é "Esse versículo me ajuda quando…", então cada categoria
 * começa com "..." pra encaixar gramaticalmente na frase.
 */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            [
                'slug' => 'vida-emocional',
                'name' => 'Vida Emocional',
                'icon' => 'heart-outline',
                'color' => '#ec4899',
                'display_order' => 1,
                'categories' => [
                    ['slug' => 'quando-bate-ansiedade', 'name' => '…bate a ansiedade ou a preocupação', 'icon' => 'pulse-outline', 'color' => '#f97316'],
                    ['slug' => 'quando-sinto-medo', 'name' => '…sinto medo', 'icon' => 'alert-circle-outline', 'color' => '#64748b'],
                    ['slug' => 'quando-tristeza-aperta', 'name' => '…a tristeza aperta', 'icon' => 'sad-outline', 'color' => '#475569'],
                    ['slug' => 'quando-me-sinto-incapaz', 'name' => '…me sinto incapaz', 'icon' => 'remove-circle-outline', 'color' => '#78716c'],
                    ['slug' => 'quando-cansaco-me-alcanca', 'name' => '…o cansaço me alcança', 'icon' => 'bed-outline', 'color' => '#a16207'],
                ],
            ],
            [
                'slug' => 'lutas-relacionais',
                'name' => 'Lutas Relacionais',
                'icon' => 'people-outline',
                'color' => '#3b82f6',
                'display_order' => 2,
                'categories' => [
                    ['slug' => 'quando-solidao-me-toma', 'name' => '…a solidão me toma', 'icon' => 'person-outline', 'color' => '#64748b'],
                    ['slug' => 'quando-conflito-com-alguem', 'name' => '…estou em conflito com alguém', 'icon' => 'flash-outline', 'color' => '#dc2626'],
                    ['slug' => 'quando-falo-o-que-nao-deveria', 'name' => '…falo o que não deveria', 'icon' => 'chatbubble-outline', 'color' => '#f43f5e'],
                    ['slug' => 'quando-desafios-na-familia', 'name' => '…enfrento desafios na dinâmica familiar', 'icon' => 'people-circle-outline', 'color' => '#8b5cf6'],
                    ['slug' => 'quando-injustica-traicao-raiva', 'name' => '…sofro uma injustiça, traição ou sinto raiva', 'icon' => 'flame-outline', 'color' => '#ef4444'],
                ],
            ],
            [
                'slug' => 'caminhada-espiritual',
                'name' => 'Caminhada Espiritual',
                'icon' => 'walk-outline',
                'color' => '#7c3aed',
                'display_order' => 3,
                'categories' => [
                    ['slug' => 'quando-carrego-culpa', 'name' => '…carrego culpa', 'icon' => 'sad-outline', 'color' => '#7f1d1d'],
                    ['slug' => 'quando-preciso-perdoar', 'name' => '…preciso perdoar', 'icon' => 'hand-left-outline', 'color' => '#14b8a6'],
                    ['slug' => 'quando-luto-contra-pecado', 'name' => '…estou lutando contra o pecado', 'icon' => 'shield-outline', 'color' => '#991b1b'],
                    ['slug' => 'quando-distante-de-deus', 'name' => '…me sinto distante de Deus', 'icon' => 'cloud-outline', 'color' => '#475569'],
                    ['slug' => 'quando-agradeco-na-dor', 'name' => '…preciso agradecer em meio à dor', 'icon' => 'gift-outline', 'color' => '#84cc16'],
                ],
            ],
            [
                'slug' => 'circunstancias',
                'name' => 'Circunstâncias',
                'icon' => 'compass-outline',
                'color' => '#0891b2',
                'display_order' => 4,
                'categories' => [
                    ['slug' => 'quando-nao-sei-o-que-fazer', 'name' => '…não sei o que fazer', 'icon' => 'help-circle-outline', 'color' => '#6366f1'],
                    ['slug' => 'quando-penso-no-futuro', 'name' => '…penso no futuro', 'icon' => 'calendar-outline', 'color' => '#3b82f6'],
                    ['slug' => 'quando-necessidades-materiais', 'name' => '…passo por necessidades materiais ou financeiras', 'icon' => 'wallet-outline', 'color' => '#059669'],
                    ['slug' => 'quando-doenca-dor-fisica', 'name' => '…enfrento doenças ou dor física', 'icon' => 'medkit-outline', 'color' => '#f43f5e'],
                    ['slug' => 'quando-vivo-luto', 'name' => '…vivo o luto', 'icon' => 'flower-outline', 'color' => '#475569'],
                ],
            ],
        ];

        foreach ($groups as $groupData) {
            $group = CategoryGroup::updateOrCreate(
                ['slug' => $groupData['slug']],
                [
                    'name' => $groupData['name'],
                    'icon' => $groupData['icon'],
                    'color' => $groupData['color'],
                    'display_order' => $groupData['display_order'],
                    'status' => 'approved',
                    'created_by_user_id' => null,
                    'approved_at' => now(),
                ]
            );

            foreach ($groupData['categories'] as $index => $cat) {
                Category::updateOrCreate(
                    ['slug' => $cat['slug']],
                    [
                        'name' => $cat['name'],
                        'icon' => $cat['icon'],
                        'color' => $cat['color'],
                        'description' => null,
                        'category_group_id' => $group->id,
                        'created_by_user_id' => null,
                        'status' => 'approved',
                        'approved_at' => now(),
                        'display_order' => $index + 1,
                    ]
                );
            }
        }
    }
}
