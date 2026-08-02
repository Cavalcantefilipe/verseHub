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
                'slug' => 'sentimentos',
                'name' => 'Sentimentos',
                'classification_kind' => 'emotion',
                'selection_prompt' => 'O que você sentiu ao ler este versículo?',
                'selection_limit' => 3,
                'icon' => 'heart-outline',
                'color' => '#db2777',
                'display_order' => 0,
                'categories' => [
                    ['slug' => 'sentindo-paz', 'name' => 'Em paz', 'icon' => 'leaf-outline', 'color' => '#059669'],
                    ['slug' => 'sentindo-esperanca', 'name' => 'Com esperança', 'icon' => 'sunny-outline', 'color' => '#ca8a04'],
                    ['slug' => 'sentindo-gratidao', 'name' => 'Com gratidão', 'icon' => 'heart-outline', 'color' => '#db2777'],
                    ['slug' => 'sentindo-coragem', 'name' => 'Com coragem', 'icon' => 'shield-checkmark-outline', 'color' => '#2563eb'],
                    ['slug' => 'sentindo-ansiedade', 'name' => 'Com ansiedade', 'icon' => 'pulse-outline', 'color' => '#ea580c'],
                    ['slug' => 'sentindo-tristeza', 'name' => 'Com tristeza', 'icon' => 'sad-outline', 'color' => '#475569'],
                    ['slug' => 'sentindo-medo', 'name' => 'Com medo', 'icon' => 'alert-circle-outline', 'color' => '#64748b'],
                    ['slug' => 'sentindo-solidao', 'name' => 'Só', 'icon' => 'person-outline', 'color' => '#7c3aed'],
                    ['slug' => 'sentindo-cansaco', 'name' => 'Cansado', 'icon' => 'bed-outline', 'color' => '#a16207'],
                    ['slug' => 'sentindo-culpa', 'name' => 'Com culpa', 'icon' => 'rainy-outline', 'color' => '#991b1b'],
                    ['slug' => 'sentindo-raiva', 'name' => 'Com raiva', 'icon' => 'flame-outline', 'color' => '#dc2626'],
                    ['slug' => 'sentindo-confusao', 'name' => 'Confuso', 'icon' => 'help-circle-outline', 'color' => '#4f46e5'],
                    ['slug' => 'sentindo-alegria', 'name' => 'Com alegria', 'icon' => 'happy-outline', 'color' => '#d97706'],
                    ['slug' => 'sentindo-alivio', 'name' => 'Aliviado', 'icon' => 'water-outline', 'color' => '#0891b2'],
                    ['slug' => 'sentindo-amor', 'name' => 'Amado', 'icon' => 'heart-circle-outline', 'color' => '#e11d48'],
                    ['slug' => 'sentindo-confianca', 'name' => 'Confiante', 'icon' => 'ribbon-outline', 'color' => '#0284c7'],
                    ['slug' => 'sentindo-consolo', 'name' => 'Consolado', 'icon' => 'hand-left-outline', 'color' => '#0d9488'],
                    ['slug' => 'sentindo-duvida', 'name' => 'Com dúvida', 'icon' => 'help-buoy-outline', 'color' => '#7c3aed'],
                    ['slug' => 'sentindo-vergonha', 'name' => 'Com vergonha', 'icon' => 'eye-off-outline', 'color' => '#9f1239'],
                    ['slug' => 'sentindo-frustracao', 'name' => 'Frustrado', 'icon' => 'thunderstorm-outline', 'color' => '#c2410c'],
                ],
            ],
            [
                'slug' => 'vida-emocional',
                'name' => 'Vida Emocional',
                'icon' => 'heart-outline',
                'color' => '#ec4899',
                'display_order' => 1,
                'classification_kind' => 'context',
                'selection_prompt' => 'O que você está vivendo?',
                'selection_limit' => null,
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
                'classification_kind' => 'context',
                'selection_prompt' => 'O que você está vivendo?',
                'selection_limit' => null,
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
                'classification_kind' => 'context',
                'selection_prompt' => 'O que você está vivendo?',
                'selection_limit' => null,
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
                'classification_kind' => 'context',
                'selection_prompt' => 'O que você está vivendo?',
                'selection_limit' => null,
                'categories' => [
                    ['slug' => 'quando-nao-sei-o-que-fazer', 'name' => '…não sei o que fazer', 'icon' => 'help-circle-outline', 'color' => '#6366f1'],
                    ['slug' => 'quando-penso-no-futuro', 'name' => '…penso no futuro', 'icon' => 'calendar-outline', 'color' => '#3b82f6'],
                    ['slug' => 'quando-necessidades-materiais', 'name' => '…passo por necessidades materiais ou financeiras', 'icon' => 'wallet-outline', 'color' => '#059669'],
                    ['slug' => 'quando-doenca-dor-fisica', 'name' => '…enfrento doenças ou dor física', 'icon' => 'medkit-outline', 'color' => '#f43f5e'],
                    ['slug' => 'quando-vivo-luto', 'name' => '…vivo o luto', 'icon' => 'flower-outline', 'color' => '#475569'],
                ],
            ],
            [
                'slug' => 'financas',
                'name' => 'Finanças',
                'icon' => 'wallet-outline',
                'color' => '#15803d',
                'display_order' => 5,
                'classification_kind' => 'context',
                'selection_prompt' => 'O que você está vivendo?',
                'selection_limit' => null,
                'categories' => [
                    ['slug' => 'quando-enfrento-dividas', 'name' => '…enfrento dívidas', 'icon' => 'receipt-outline', 'color' => '#b91c1c'],
                    ['slug' => 'quando-falta-provisao', 'name' => '…me preocupo com a provisão', 'icon' => 'basket-outline', 'color' => '#c2410c'],
                    ['slug' => 'quando-decido-sobre-dinheiro', 'name' => '…preciso tomar uma decisão financeira', 'icon' => 'git-branch-outline', 'color' => '#2563eb'],
                    ['slug' => 'quando-preciso-administrar-melhor', 'name' => '…quero administrar melhor o que tenho', 'icon' => 'calculator-outline', 'color' => '#0369a1'],
                    ['slug' => 'quando-quero-ser-generoso', 'name' => '…quero praticar a generosidade', 'icon' => 'gift-outline', 'color' => '#15803d'],
                    ['slug' => 'quando-trabalho-renda', 'name' => '…busco direção para trabalho e renda', 'icon' => 'briefcase-outline', 'color' => '#7c3aed'],
                ],
            ],
            [
                'slug' => 'evangelismo',
                'name' => 'Evangelismo',
                'icon' => 'megaphone-outline',
                'color' => '#b45309',
                'display_order' => 6,
                'classification_kind' => 'context',
                'selection_prompt' => 'O que você está vivendo?',
                'selection_limit' => null,
                'categories' => [
                    ['slug' => 'quando-quero-compartilhar-fe', 'name' => '…quero compartilhar minha fé', 'icon' => 'megaphone-outline', 'color' => '#b45309'],
                    ['slug' => 'quando-tenho-medo-de-falar', 'name' => '…tenho medo de falar sobre Jesus', 'icon' => 'mic-off-outline', 'color' => '#dc2626'],
                    ['slug' => 'quando-intercedo-por-alguem', 'name' => '…estou intercedendo por alguém', 'icon' => 'people-outline', 'color' => '#7c3aed'],
                    ['slug' => 'quando-acompanho-novo-convertido', 'name' => '…acompanho alguém no início da fé', 'icon' => 'leaf-outline', 'color' => '#15803d'],
                    ['slug' => 'quando-preciso-responder-fe', 'name' => '…preciso responder perguntas sobre a fé', 'icon' => 'chatbubbles-outline', 'color' => '#2563eb'],
                    ['slug' => 'quando-quero-viver-testemunho', 'name' => '…quero viver um testemunho coerente', 'icon' => 'footsteps-outline', 'color' => '#0f766e'],
                ],
            ],
            [
                'slug' => 'resposta-ao-texto',
                'name' => 'O que este texto despertou?',
                'icon' => 'bulb-outline',
                'color' => '#7c3aed',
                'display_order' => 7,
                'classification_kind' => 'context',
                'selection_prompt' => 'O que este versículo despertou em você?',
                'selection_limit' => null,
                'categories' => [
                    ['slug' => 'vejo-uma-promessa', 'name' => 'Vejo uma promessa', 'icon' => 'sparkles-outline', 'color' => '#ca8a04'],
                    ['slug' => 'recebi-um-alerta', 'name' => 'Recebi um alerta', 'icon' => 'warning-outline', 'color' => '#dc2626'],
                    ['slug' => 'aprendi-algo-novo', 'name' => 'Aprendi algo novo', 'icon' => 'bulb-outline', 'color' => '#7c3aed'],
                    ['slug' => 'senti-encorajamento', 'name' => 'Senti encorajamento', 'icon' => 'trending-up-outline', 'color' => '#2563eb'],
                    ['slug' => 'convite-a-mudanca', 'name' => 'É um convite à mudança', 'icon' => 'refresh-outline', 'color' => '#0d9488'],
                    ['slug' => 'confirmou-algo', 'name' => 'Confirmou algo que eu buscava', 'icon' => 'checkmark-done-outline', 'color' => '#15803d'],
                    ['slug' => 'quero-praticar', 'name' => 'Quero colocar em prática', 'icon' => 'footsteps-outline', 'color' => '#b45309'],
                    ['slug' => 'ainda-tenho-duvidas', 'name' => 'Ainda tenho dúvidas', 'icon' => 'help-circle-outline', 'color' => '#64748b'],
                ],
            ],
        ];

        foreach ($groups as $groupData) {
            $group = CategoryGroup::updateOrCreate(
                ['slug' => $groupData['slug']],
                [
                    'name' => $groupData['name'],
                    'classification_kind' => $groupData['classification_kind'],
                    'selection_prompt' => $groupData['selection_prompt'],
                    'selection_limit' => $groupData['selection_limit'],
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
