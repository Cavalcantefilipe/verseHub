<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $now = now();
            $emotionGroupId = DB::table('category_groups')->where('slug', 'sentimentos')->value('id');
            $emotions = [
                ['sentindo-alegria', 'Com alegria', 'happy-outline', '#d97706'],
                ['sentindo-alivio', 'Aliviado', 'water-outline', '#0891b2'],
                ['sentindo-amor', 'Amado', 'heart-circle-outline', '#e11d48'],
                ['sentindo-confianca', 'Confiante', 'ribbon-outline', '#0284c7'],
                ['sentindo-consolo', 'Consolado', 'hand-left-outline', '#0d9488'],
                ['sentindo-duvida', 'Com dúvida', 'help-buoy-outline', '#7c3aed'],
                ['sentindo-vergonha', 'Com vergonha', 'eye-off-outline', '#9f1239'],
                ['sentindo-frustracao', 'Frustrado', 'thunderstorm-outline', '#c2410c'],
            ];

            foreach ($emotions as $offset => [$slug, $name, $icon, $color]) {
                $this->upsertCategory($slug, $name, $icon, $color, $emotionGroupId, 13 + $offset, $now);
            }

            $groups = [
                [
                    'slug' => 'financas', 'name' => 'Finanças', 'icon' => 'wallet-outline',
                    'color' => '#15803d', 'display_order' => 5,
                    'categories' => [
                        ['quando-enfrento-dividas', '…enfrento dívidas', 'receipt-outline', '#b91c1c'],
                        ['quando-falta-provisao', '…me preocupo com a provisão', 'basket-outline', '#c2410c'],
                        ['quando-decido-sobre-dinheiro', '…preciso tomar uma decisão financeira', 'git-branch-outline', '#2563eb'],
                        ['quando-preciso-administrar-melhor', '…quero administrar melhor o que tenho', 'calculator-outline', '#0369a1'],
                        ['quando-quero-ser-generoso', '…quero praticar a generosidade', 'gift-outline', '#15803d'],
                        ['quando-trabalho-renda', '…busco direção para trabalho e renda', 'briefcase-outline', '#7c3aed'],
                    ],
                ],
                [
                    'slug' => 'evangelismo', 'name' => 'Evangelismo', 'icon' => 'megaphone-outline',
                    'color' => '#b45309', 'display_order' => 6,
                    'categories' => [
                        ['quando-quero-compartilhar-fe', '…quero compartilhar minha fé', 'megaphone-outline', '#b45309'],
                        ['quando-tenho-medo-de-falar', '…tenho medo de falar sobre Jesus', 'mic-off-outline', '#dc2626'],
                        ['quando-intercedo-por-alguem', '…estou intercedendo por alguém', 'people-outline', '#7c3aed'],
                        ['quando-acompanho-novo-convertido', '…acompanho alguém no início da fé', 'leaf-outline', '#15803d'],
                        ['quando-preciso-responder-fe', '…preciso responder perguntas sobre a fé', 'chatbubbles-outline', '#2563eb'],
                        ['quando-quero-viver-testemunho', '…quero viver um testemunho coerente', 'footsteps-outline', '#0f766e'],
                    ],
                ],
                [
                    'slug' => 'resposta-ao-texto', 'name' => 'O que este texto despertou?', 'icon' => 'bulb-outline',
                    'color' => '#7c3aed', 'display_order' => 7,
                    'categories' => [
                        ['vejo-uma-promessa', 'Vejo uma promessa', 'sparkles-outline', '#ca8a04'],
                        ['recebi-um-alerta', 'Recebi um alerta', 'warning-outline', '#dc2626'],
                        ['aprendi-algo-novo', 'Aprendi algo novo', 'bulb-outline', '#7c3aed'],
                        ['senti-encorajamento', 'Senti encorajamento', 'trending-up-outline', '#2563eb'],
                        ['convite-a-mudanca', 'É um convite à mudança', 'refresh-outline', '#0d9488'],
                        ['confirmou-algo', 'Confirmou algo que eu buscava', 'checkmark-done-outline', '#15803d'],
                        ['quero-praticar', 'Quero colocar em prática', 'footsteps-outline', '#b45309'],
                        ['ainda-tenho-duvidas', 'Ainda tenho dúvidas', 'help-circle-outline', '#64748b'],
                    ],
                ],
            ];

            foreach ($groups as $group) {
                DB::table('category_groups')->updateOrInsert(
                    ['slug' => $group['slug']],
                    [
                        'name' => $group['name'],
                        'classification_kind' => 'context',
                        'selection_prompt' => 'O que você está vivendo?',
                        'selection_limit' => null,
                        'icon' => $group['icon'],
                        'color' => $group['color'],
                        'display_order' => $group['display_order'],
                        'created_by_user_id' => null,
                        'status' => 'approved',
                        'approved_by_user_id' => null,
                        'approved_at' => $now,
                        'rejected_reason' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );

                $groupId = DB::table('category_groups')->where('slug', $group['slug'])->value('id');
                foreach ($group['categories'] as $order => [$slug, $name, $icon, $color]) {
                    $this->upsertCategory($slug, $name, $icon, $color, $groupId, $order + 1, $now);
                }
            }
        });
    }

    public function down(): void
    {
        $categorySlugs = [
            'sentindo-alegria', 'sentindo-alivio', 'sentindo-amor', 'sentindo-confianca',
            'sentindo-consolo', 'sentindo-duvida', 'sentindo-vergonha', 'sentindo-frustracao',
            'quando-enfrento-dividas', 'quando-falta-provisao', 'quando-decido-sobre-dinheiro',
            'quando-preciso-administrar-melhor', 'quando-quero-ser-generoso', 'quando-trabalho-renda',
            'quando-quero-compartilhar-fe', 'quando-tenho-medo-de-falar', 'quando-intercedo-por-alguem',
            'quando-acompanho-novo-convertido', 'quando-preciso-responder-fe', 'quando-quero-viver-testemunho',
            'vejo-uma-promessa', 'recebi-um-alerta', 'aprendi-algo-novo', 'senti-encorajamento',
            'convite-a-mudanca', 'confirmou-algo', 'quero-praticar', 'ainda-tenho-duvidas',
        ];

        DB::transaction(function () use ($categorySlugs): void {
            DB::table('categories')->whereIn('slug', $categorySlugs)->delete();
            DB::table('category_groups')->whereIn('slug', ['financas', 'evangelismo', 'resposta-ao-texto'])->delete();
        });
    }

    private function upsertCategory(
        string $slug,
        string $name,
        string $icon,
        string $color,
        int $groupId,
        int $displayOrder,
        mixed $now
    ): void {
        DB::table('categories')->updateOrInsert(
            ['slug' => $slug],
            [
                'category_group_id' => $groupId,
                'name' => $name,
                'description' => null,
                'icon' => $icon,
                'color' => $color,
                'created_by_user_id' => null,
                'status' => 'approved',
                'approved_by_user_id' => null,
                'approved_at' => $now,
                'rejected_reason' => null,
                'display_order' => $displayOrder,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
};
