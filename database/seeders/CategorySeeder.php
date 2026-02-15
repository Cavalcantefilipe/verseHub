<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // TEOLOGIA PRÓPRIA (Deus)
            ['name' => 'Deus', 'slug' => 'deus', 'icon' => 'infinite-outline', 'color' => '#7c3aed', 'group' => 'Teologia'],
            ['name' => 'Atributos de Deus', 'slug' => 'atributos-de-deus', 'icon' => 'star-outline', 'color' => '#8b5cf6', 'group' => 'Teologia'],
            ['name' => 'Trindade', 'slug' => 'trindade', 'icon' => 'triangle-outline', 'color' => '#a78bfa', 'group' => 'Teologia'],
            ['name' => 'Soberania de Deus', 'slug' => 'soberania-de-deus', 'icon' => 'globe-outline', 'color' => '#6366f1', 'group' => 'Teologia'],
            ['name' => 'Santidade', 'slug' => 'santidade', 'icon' => 'sparkles-outline', 'color' => '#818cf8', 'group' => 'Teologia'],
            ['name' => 'Glória de Deus', 'slug' => 'gloria-de-deus', 'icon' => 'sunny-outline', 'color' => '#fbbf24', 'group' => 'Teologia'],
            ['name' => 'Reino de Deus', 'slug' => 'reino-de-deus', 'icon' => 'home-outline', 'color' => '#f59e0b', 'group' => 'Teologia'],
            ['name' => 'Criação', 'slug' => 'criacao', 'icon' => 'planet-outline', 'color' => '#10b981', 'group' => 'Teologia'],
            ['name' => 'Providência', 'slug' => 'providencia', 'icon' => 'hand-right-outline', 'color' => '#14b8a6', 'group' => 'Teologia'],
            ['name' => 'Eleição', 'slug' => 'eleicao', 'icon' => 'checkmark-circle-outline', 'color' => '#0d9488', 'group' => 'Teologia'],
            ['name' => 'Predestinação', 'slug' => 'predestinacao', 'icon' => 'git-branch-outline', 'color' => '#0891b2', 'group' => 'Teologia'],
            ['name' => 'Livre-Arbítrio', 'slug' => 'livre-arbitrio', 'icon' => 'options-outline', 'color' => '#06b6d4', 'group' => 'Teologia'],
            ['name' => 'Revelação', 'slug' => 'revelacao', 'icon' => 'eye-outline', 'color' => '#22d3ee', 'group' => 'Teologia'],
            ['name' => 'Inspiração das Escrituras', 'slug' => 'inspiracao-das-escrituras', 'icon' => 'book-outline', 'color' => '#67e8f9', 'group' => 'Teologia'],

            // CRISTOLOGIA
            ['name' => 'Jesus Cristo', 'slug' => 'jesus-cristo', 'icon' => 'person-outline', 'color' => '#dc2626', 'group' => 'Cristologia'],
            ['name' => 'Jesus Filho de Deus', 'slug' => 'jesus-filho-de-deus', 'icon' => 'heart-circle-outline', 'color' => '#ef4444', 'group' => 'Cristologia'],
            ['name' => 'Divindade de Cristo', 'slug' => 'divindade-de-cristo', 'icon' => 'flash-outline', 'color' => '#f87171', 'group' => 'Cristologia'],
            ['name' => 'Humanidade de Cristo', 'slug' => 'humanidade-de-cristo', 'icon' => 'body-outline', 'color' => '#fb923c', 'group' => 'Cristologia'],
            ['name' => 'Encarnação', 'slug' => 'encarnacao', 'icon' => 'gift-outline', 'color' => '#f97316', 'group' => 'Cristologia'],
            ['name' => 'Sacrifício', 'slug' => 'sacrificio', 'icon' => 'add-outline', 'color' => '#ea580c', 'group' => 'Cristologia'],
            ['name' => 'Redenção', 'slug' => 'redencao', 'icon' => 'shield-checkmark-outline', 'color' => '#c2410c', 'group' => 'Cristologia'],
            ['name' => 'Ressurreição', 'slug' => 'ressurreicao', 'icon' => 'arrow-up-circle-outline', 'color' => '#22c55e', 'group' => 'Cristologia'],
            ['name' => 'Segunda Vinda', 'slug' => 'segunda-vinda', 'icon' => 'cloud-download-outline', 'color' => '#16a34a', 'group' => 'Cristologia'],
            ['name' => 'Senhorio de Cristo', 'slug' => 'senhorio-de-cristo', 'icon' => 'ribbon-outline', 'color' => '#15803d', 'group' => 'Cristologia'],
            ['name' => 'Ministério de Jesus', 'slug' => 'ministerio-de-jesus', 'icon' => 'walk-outline', 'color' => '#166534', 'group' => 'Cristologia'],
            ['name' => 'Parábolas', 'slug' => 'parabolas', 'icon' => 'chatbubbles-outline', 'color' => '#84cc16', 'group' => 'Cristologia'],
            ['name' => 'Milagres', 'slug' => 'milagres', 'icon' => 'sparkles-outline', 'color' => '#a3e635', 'group' => 'Cristologia'],

            // ESPÍRITO SANTO
            ['name' => 'Espírito Santo', 'slug' => 'espirito-santo', 'icon' => 'flame-outline', 'color' => '#0ea5e9', 'group' => 'Espírito Santo'],
            ['name' => 'Batismo no Espírito', 'slug' => 'batismo-no-espirito', 'icon' => 'water-outline', 'color' => '#38bdf8', 'group' => 'Espírito Santo'],
            ['name' => 'Dons Espirituais', 'slug' => 'dons-espirituais', 'icon' => 'gift-outline', 'color' => '#7dd3fc', 'group' => 'Espírito Santo'],
            ['name' => 'Fruto do Espírito', 'slug' => 'fruto-do-espirito', 'icon' => 'leaf-outline', 'color' => '#22c55e', 'group' => 'Espírito Santo'],
            ['name' => 'Consolador', 'slug' => 'consolador', 'icon' => 'heart-outline', 'color' => '#4ade80', 'group' => 'Espírito Santo'],
            ['name' => 'Direção Espiritual', 'slug' => 'direcao-espiritual', 'icon' => 'compass-outline', 'color' => '#86efac', 'group' => 'Espírito Santo'],
            ['name' => 'Poder Espiritual', 'slug' => 'poder-espiritual', 'icon' => 'flash-outline', 'color' => '#fcd34d', 'group' => 'Espírito Santo'],

            // SALVAÇÃO
            ['name' => 'Salvação', 'slug' => 'salvacao', 'icon' => 'shield-checkmark-outline', 'color' => '#dc2626', 'group' => 'Salvação'],
            ['name' => 'Graça', 'slug' => 'graca', 'icon' => 'gift-outline', 'color' => '#ec4899', 'group' => 'Salvação'],
            ['name' => 'Fé', 'slug' => 'fe', 'icon' => 'flame-outline', 'color' => '#f43f5e', 'group' => 'Salvação'],
            ['name' => 'Arrependimento', 'slug' => 'arrependimento', 'icon' => 'refresh-outline', 'color' => '#fb7185', 'group' => 'Salvação'],
            ['name' => 'Justificação', 'slug' => 'justificacao', 'icon' => 'checkmark-done-outline', 'color' => '#fda4af', 'group' => 'Salvação'],
            ['name' => 'Santificação', 'slug' => 'santificacao', 'icon' => 'sparkles-outline', 'color' => '#a855f7', 'group' => 'Salvação'],
            ['name' => 'Regeneração', 'slug' => 'regeneracao', 'icon' => 'sync-outline', 'color' => '#c084fc', 'group' => 'Salvação'],
            ['name' => 'Perdão', 'slug' => 'perdao', 'icon' => 'hand-left-outline', 'color' => '#14b8a6', 'group' => 'Salvação'],
            ['name' => 'Vida Eterna', 'slug' => 'vida-eterna', 'icon' => 'infinite-outline', 'color' => '#2dd4bf', 'group' => 'Salvação'],
            ['name' => 'Novo Nascimento', 'slug' => 'novo-nascimento', 'icon' => 'flower-outline', 'color' => '#5eead4', 'group' => 'Salvação'],
            ['name' => 'Conversão', 'slug' => 'conversao', 'icon' => 'swap-horizontal-outline', 'color' => '#99f6e4', 'group' => 'Salvação'],

            // IGREJA
            ['name' => 'Igreja', 'slug' => 'igreja', 'icon' => 'business-outline', 'color' => '#3b82f6', 'group' => 'Igreja'],
            ['name' => 'Corpo de Cristo', 'slug' => 'corpo-de-cristo', 'icon' => 'people-outline', 'color' => '#60a5fa', 'group' => 'Igreja'],
            ['name' => 'Comunhão', 'slug' => 'comunhao', 'icon' => 'people-circle-outline', 'color' => '#93c5fd', 'group' => 'Igreja'],
            ['name' => 'Unidade', 'slug' => 'unidade', 'icon' => 'link-outline', 'color' => '#2563eb', 'group' => 'Igreja'],
            ['name' => 'Liderança', 'slug' => 'lideranca', 'icon' => 'person-add-outline', 'color' => '#1d4ed8', 'group' => 'Igreja'],
            ['name' => 'Disciplina', 'slug' => 'disciplina', 'icon' => 'school-outline', 'color' => '#1e40af', 'group' => 'Igreja'],
            ['name' => 'Missão', 'slug' => 'missao', 'icon' => 'send-outline', 'color' => '#1e3a8a', 'group' => 'Igreja'],
            ['name' => 'Evangelismo', 'slug' => 'evangelismo', 'icon' => 'megaphone-outline', 'color' => '#f97316', 'group' => 'Igreja'],
            ['name' => 'Batismo', 'slug' => 'batismo', 'icon' => 'water-outline', 'color' => '#0ea5e9', 'group' => 'Igreja'],
            ['name' => 'Ceia do Senhor', 'slug' => 'ceia-do-senhor', 'icon' => 'wine-outline', 'color' => '#7c2d12', 'group' => 'Igreja'],
            ['name' => 'Ministério', 'slug' => 'ministerio', 'icon' => 'hand-right-outline', 'color' => '#0284c7', 'group' => 'Igreja'],

            // ESCATOLOGIA
            ['name' => 'Escatologia', 'slug' => 'escatologia', 'icon' => 'hourglass-outline', 'color' => '#4c1d95', 'group' => 'Escatologia'],
            ['name' => 'Juízo Final', 'slug' => 'juizo-final', 'icon' => 'scale-outline', 'color' => '#5b21b6', 'group' => 'Escatologia'],
            ['name' => 'Céu', 'slug' => 'ceu', 'icon' => 'cloudy-outline', 'color' => '#7dd3fc', 'group' => 'Escatologia'],
            ['name' => 'Inferno', 'slug' => 'inferno', 'icon' => 'flame-outline', 'color' => '#b91c1c', 'group' => 'Escatologia'],
            ['name' => 'Ressurreição dos Mortos', 'slug' => 'ressurreicao-dos-mortos', 'icon' => 'arrow-up-outline', 'color' => '#059669', 'group' => 'Escatologia'],
            ['name' => 'Vida Após a Morte', 'slug' => 'vida-apos-a-morte', 'icon' => 'moon-outline', 'color' => '#6366f1', 'group' => 'Escatologia'],
            ['name' => 'Nova Jerusalém', 'slug' => 'nova-jerusalem', 'icon' => 'diamond-outline', 'color' => '#fcd34d', 'group' => 'Escatologia'],
            ['name' => 'Tribulação', 'slug' => 'tribulacao', 'icon' => 'thunderstorm-outline', 'color' => '#64748b', 'group' => 'Escatologia'],
            ['name' => 'Anticristo', 'slug' => 'anticristo', 'icon' => 'skull-outline', 'color' => '#1e293b', 'group' => 'Escatologia'],
            ['name' => 'Eternidade', 'slug' => 'eternidade', 'icon' => 'infinite-outline', 'color' => '#a855f7', 'group' => 'Escatologia'],

            // LEI / PECADO
            ['name' => 'Lei de Deus', 'slug' => 'lei-de-deus', 'icon' => 'document-text-outline', 'color' => '#78716c', 'group' => 'Lei e Pecado'],
            ['name' => 'Mandamentos', 'slug' => 'mandamentos', 'icon' => 'list-outline', 'color' => '#57534e', 'group' => 'Lei e Pecado'],
            ['name' => 'Obediência', 'slug' => 'obediencia', 'icon' => 'checkmark-circle-outline', 'color' => '#059669', 'group' => 'Lei e Pecado'],
            ['name' => 'Desobediência', 'slug' => 'desobediencia', 'icon' => 'close-circle-outline', 'color' => '#dc2626', 'group' => 'Lei e Pecado'],
            ['name' => 'Justiça', 'slug' => 'justica', 'icon' => 'scale-outline', 'color' => '#0284c7', 'group' => 'Lei e Pecado'],
            ['name' => 'Julgamento', 'slug' => 'julgamento', 'icon' => 'hammer-outline', 'color' => '#475569', 'group' => 'Lei e Pecado'],
            ['name' => 'Pecado', 'slug' => 'pecado', 'icon' => 'warning-outline', 'color' => '#991b1b', 'group' => 'Lei e Pecado'],
            ['name' => 'Consequências do Pecado', 'slug' => 'consequencias-do-pecado', 'icon' => 'alert-circle-outline', 'color' => '#7f1d1d', 'group' => 'Lei e Pecado'],
            ['name' => 'Santidade Prática', 'slug' => 'santidade-pratica', 'icon' => 'sparkles-outline', 'color' => '#d946ef', 'group' => 'Lei e Pecado'],

            // VIRTUDES
            ['name' => 'Amor', 'slug' => 'amor', 'icon' => 'heart-outline', 'color' => '#ec4899', 'group' => 'Virtudes'],
            ['name' => 'Esperança', 'slug' => 'esperanca', 'icon' => 'sunny-outline', 'color' => '#22c55e', 'group' => 'Virtudes'],
            ['name' => 'Humildade', 'slug' => 'humildade', 'icon' => 'ribbon-outline', 'color' => '#78716c', 'group' => 'Virtudes'],
            ['name' => 'Mansidão', 'slug' => 'mansidao', 'icon' => 'flower-outline', 'color' => '#a3e635', 'group' => 'Virtudes'],
            ['name' => 'Paciência', 'slug' => 'paciencia', 'icon' => 'time-outline', 'color' => '#0891b2', 'group' => 'Virtudes'],
            ['name' => 'Perseverança', 'slug' => 'perseveranca', 'icon' => 'fitness-outline', 'color' => '#f97316', 'group' => 'Virtudes'],
            ['name' => 'Bondade', 'slug' => 'bondade', 'icon' => 'happy-outline', 'color' => '#4ade80', 'group' => 'Virtudes'],
            ['name' => 'Misericórdia', 'slug' => 'misericordia', 'icon' => 'hand-right-outline', 'color' => '#2dd4bf', 'group' => 'Virtudes'],
            ['name' => 'Fidelidade', 'slug' => 'fidelidade', 'icon' => 'shield-outline', 'color' => '#3b82f6', 'group' => 'Virtudes'],
            ['name' => 'Generosidade', 'slug' => 'generosidade', 'icon' => 'gift-outline', 'color' => '#f472b6', 'group' => 'Virtudes'],

            // ADVERTÊNCIAS
            ['name' => 'Orgulho', 'slug' => 'orgulho', 'icon' => 'trending-up-outline', 'color' => '#dc2626', 'group' => 'Advertências'],
            ['name' => 'Inveja', 'slug' => 'inveja', 'icon' => 'eye-off-outline', 'color' => '#16a34a', 'group' => 'Advertências'],
            ['name' => 'Ira', 'slug' => 'ira', 'icon' => 'flame-outline', 'color' => '#ef4444', 'group' => 'Advertências'],
            ['name' => 'Ganância', 'slug' => 'ganancia', 'icon' => 'cash-outline', 'color' => '#eab308', 'group' => 'Advertências'],
            ['name' => 'Idolatria', 'slug' => 'idolatria', 'icon' => 'image-outline', 'color' => '#78716c', 'group' => 'Advertências'],
            ['name' => 'Imoralidade', 'slug' => 'imoralidade', 'icon' => 'ban-outline', 'color' => '#be123c', 'group' => 'Advertências'],
            ['name' => 'Mentira', 'slug' => 'mentira', 'icon' => 'close-outline', 'color' => '#1e293b', 'group' => 'Advertências'],
            ['name' => 'Hipocrisia', 'slug' => 'hipocrisia', 'icon' => 'person-outline', 'color' => '#64748b', 'group' => 'Advertências'],
            ['name' => 'Advertência', 'slug' => 'advertencia', 'icon' => 'warning-outline', 'color' => '#f59e0b', 'group' => 'Advertências'],
            ['name' => 'Castigo', 'slug' => 'castigo', 'icon' => 'hammer-outline', 'color' => '#92400e', 'group' => 'Advertências'],
            ['name' => 'Correção', 'slug' => 'correcao', 'icon' => 'construct-outline', 'color' => '#0369a1', 'group' => 'Advertências'],

            // FINANÇAS
            ['name' => 'Finanças', 'slug' => 'financas', 'icon' => 'wallet-outline', 'color' => '#059669', 'group' => 'Finanças'],
            ['name' => 'Riqueza', 'slug' => 'riqueza', 'icon' => 'diamond-outline', 'color' => '#fbbf24', 'group' => 'Finanças'],
            ['name' => 'Pobreza', 'slug' => 'pobreza', 'icon' => 'sad-outline', 'color' => '#78716c', 'group' => 'Finanças'],
            ['name' => 'Trabalho', 'slug' => 'trabalho', 'icon' => 'briefcase-outline', 'color' => '#64748b', 'group' => 'Finanças'],
            ['name' => 'Diligência', 'slug' => 'diligencia', 'icon' => 'speedometer-outline', 'color' => '#16a34a', 'group' => 'Finanças'],
            ['name' => 'Preguiça', 'slug' => 'preguica', 'icon' => 'bed-outline', 'color' => '#a16207', 'group' => 'Finanças'],
            ['name' => 'Mordomia', 'slug' => 'mordomia', 'icon' => 'home-outline', 'color' => '#0284c7', 'group' => 'Finanças'],
            ['name' => 'Dízimo', 'slug' => 'dizimo', 'icon' => 'pie-chart-outline', 'color' => '#7c3aed', 'group' => 'Finanças'],
            ['name' => 'Oferta', 'slug' => 'oferta', 'icon' => 'gift-outline', 'color' => '#db2777', 'group' => 'Finanças'],
            ['name' => 'Prosperidade', 'slug' => 'prosperidade', 'icon' => 'trending-up-outline', 'color' => '#22c55e', 'group' => 'Finanças'],
            ['name' => 'Contentamento', 'slug' => 'contentamento', 'icon' => 'happy-outline', 'color' => '#14b8a6', 'group' => 'Finanças'],
            ['name' => 'Justiça Social', 'slug' => 'justica-social', 'icon' => 'scale-outline', 'color' => '#0891b2', 'group' => 'Finanças'],
            ['name' => 'Dívida', 'slug' => 'divida', 'icon' => 'card-outline', 'color' => '#dc2626', 'group' => 'Finanças'],
            ['name' => 'Honestidade', 'slug' => 'honestidade', 'icon' => 'checkmark-outline', 'color' => '#059669', 'group' => 'Finanças'],
            ['name' => 'Prioridades', 'slug' => 'prioridades', 'icon' => 'list-outline', 'color' => '#6366f1', 'group' => 'Finanças'],
            ['name' => 'Herança', 'slug' => 'heranca', 'icon' => 'document-outline', 'color' => '#a855f7', 'group' => 'Finanças'],

            // SABEDORIA
            ['name' => 'Sabedoria', 'slug' => 'sabedoria', 'icon' => 'bulb-outline', 'color' => '#f59e0b', 'group' => 'Sabedoria'],
            ['name' => 'Conhecimento', 'slug' => 'conhecimento', 'icon' => 'library-outline', 'color' => '#0ea5e9', 'group' => 'Sabedoria'],
            ['name' => 'Discernimento', 'slug' => 'discernimento', 'icon' => 'eye-outline', 'color' => '#8b5cf6', 'group' => 'Sabedoria'],
            ['name' => 'Prudência', 'slug' => 'prudencia', 'icon' => 'shield-outline', 'color' => '#64748b', 'group' => 'Sabedoria'],
            ['name' => 'Conselho', 'slug' => 'conselho', 'icon' => 'chatbubble-outline', 'color' => '#06b6d4', 'group' => 'Sabedoria'],
            ['name' => 'Ensino', 'slug' => 'ensino', 'icon' => 'school-outline', 'color' => '#3b82f6', 'group' => 'Sabedoria'],
            ['name' => 'Temor do Senhor', 'slug' => 'temor-do-senhor', 'icon' => 'heart-circle-outline', 'color' => '#7c3aed', 'group' => 'Sabedoria'],
            ['name' => 'Insensatez', 'slug' => 'insensatez', 'icon' => 'help-circle-outline', 'color' => '#78716c', 'group' => 'Sabedoria'],

            // SOFRIMENTO
            ['name' => 'Sofrimento', 'slug' => 'sofrimento', 'icon' => 'sad-outline', 'color' => '#475569', 'group' => 'Sofrimento'],
            ['name' => 'Dor', 'slug' => 'dor', 'icon' => 'bandage-outline', 'color' => '#64748b', 'group' => 'Sofrimento'],
            ['name' => 'Aflição', 'slug' => 'aflicao', 'icon' => 'thunderstorm-outline', 'color' => '#334155', 'group' => 'Sofrimento'],
            ['name' => 'Perseguição', 'slug' => 'perseguicao', 'icon' => 'walk-outline', 'color' => '#1e293b', 'group' => 'Sofrimento'],
            ['name' => 'Provação', 'slug' => 'provacao', 'icon' => 'flask-outline', 'color' => '#f97316', 'group' => 'Sofrimento'],
            ['name' => 'Consolação', 'slug' => 'consolacao', 'icon' => 'heart-outline', 'color' => '#ec4899', 'group' => 'Sofrimento'],

            // ORAÇÃO
            ['name' => 'Oração', 'slug' => 'oracao', 'icon' => 'chatbubble-ellipses-outline', 'color' => '#8b5cf6', 'group' => 'Oração'],
            ['name' => 'Louvor', 'slug' => 'louvor', 'icon' => 'musical-notes-outline', 'color' => '#a855f7', 'group' => 'Oração'],
            ['name' => 'Adoração', 'slug' => 'adoracao', 'icon' => 'star-outline', 'color' => '#fbbf24', 'group' => 'Oração'],
            ['name' => 'Jejum', 'slug' => 'jejum', 'icon' => 'restaurant-outline', 'color' => '#78716c', 'group' => 'Oração'],
            ['name' => 'Intercessão', 'slug' => 'intercessao', 'icon' => 'people-outline', 'color' => '#06b6d4', 'group' => 'Oração'],
            ['name' => 'Clamor', 'slug' => 'clamor', 'icon' => 'megaphone-outline', 'color' => '#f43f5e', 'group' => 'Oração'],
            ['name' => 'Gratidão', 'slug' => 'gratidao', 'icon' => 'gift-outline', 'color' => '#84cc16', 'group' => 'Oração'],

            // DIREÇÃO
            ['name' => 'Vontade de Deus', 'slug' => 'vontade-de-deus', 'icon' => 'compass-outline', 'color' => '#7c3aed', 'group' => 'Direção'],
            ['name' => 'Propósito', 'slug' => 'proposito', 'icon' => 'flag-outline', 'color' => '#f97316', 'group' => 'Direção'],
            ['name' => 'Chamado', 'slug' => 'chamado', 'icon' => 'call-outline', 'color' => '#0ea5e9', 'group' => 'Direção'],
            ['name' => 'Decisão', 'slug' => 'decisao', 'icon' => 'git-branch-outline', 'color' => '#64748b', 'group' => 'Direção'],
            ['name' => 'Caminhos do Homem', 'slug' => 'caminhos-do-homem', 'icon' => 'map-outline', 'color' => '#78716c', 'group' => 'Direção'],
            ['name' => 'Confiança em Deus', 'slug' => 'confianca-em-deus', 'icon' => 'shield-checkmark-outline', 'color' => '#22c55e', 'group' => 'Direção'],
            ['name' => 'Planejamento', 'slug' => 'planejamento', 'icon' => 'calendar-outline', 'color' => '#3b82f6', 'group' => 'Direção'],

            // RELACIONAMENTOS
            ['name' => 'Família', 'slug' => 'familia', 'icon' => 'people-outline', 'color' => '#3b82f6', 'group' => 'Relacionamentos'],
            ['name' => 'Casamento', 'slug' => 'casamento', 'icon' => 'heart-outline', 'color' => '#ec4899', 'group' => 'Relacionamentos'],
            ['name' => 'Pais e Filhos', 'slug' => 'pais-e-filhos', 'icon' => 'people-circle-outline', 'color' => '#8b5cf6', 'group' => 'Relacionamentos'],
            ['name' => 'Amizade', 'slug' => 'amizade', 'icon' => 'person-add-outline', 'color' => '#06b6d4', 'group' => 'Relacionamentos'],
            ['name' => 'Amor ao Próximo', 'slug' => 'amor-ao-proximo', 'icon' => 'heart-circle-outline', 'color' => '#f43f5e', 'group' => 'Relacionamentos'],
            ['name' => 'Reconciliação', 'slug' => 'reconciliacao', 'icon' => 'git-merge-outline', 'color' => '#22c55e', 'group' => 'Relacionamentos'],
            ['name' => 'Autoridade', 'slug' => 'autoridade', 'icon' => 'person-outline', 'color' => '#475569', 'group' => 'Relacionamentos'],

            // GUERRA ESPIRITUAL
            ['name' => 'Batalha Espiritual', 'slug' => 'batalha-espiritual', 'icon' => 'shield-outline', 'color' => '#dc2626', 'group' => 'Guerra Espiritual'],
            ['name' => 'Armadura de Deus', 'slug' => 'armadura-de-deus', 'icon' => 'body-outline', 'color' => '#f59e0b', 'group' => 'Guerra Espiritual'],
            ['name' => 'Tentação', 'slug' => 'tentacao', 'icon' => 'alert-outline', 'color' => '#ef4444', 'group' => 'Guerra Espiritual'],
            ['name' => 'Resistência ao Mal', 'slug' => 'resistencia-ao-mal', 'icon' => 'hand-left-outline', 'color' => '#16a34a', 'group' => 'Guerra Espiritual'],
            ['name' => 'Vitória Espiritual', 'slug' => 'vitoria-espiritual', 'icon' => 'trophy-outline', 'color' => '#fbbf24', 'group' => 'Guerra Espiritual'],

            // SOCIEDADE
            ['name' => 'Governo', 'slug' => 'governo', 'icon' => 'business-outline', 'color' => '#475569', 'group' => 'Sociedade'],
            ['name' => 'Autoridades', 'slug' => 'autoridades', 'icon' => 'people-outline', 'color' => '#64748b', 'group' => 'Sociedade'],
            ['name' => 'Paz', 'slug' => 'paz', 'icon' => 'leaf-outline', 'color' => '#06b6d4', 'group' => 'Sociedade'],
            ['name' => 'Guerra', 'slug' => 'guerra', 'icon' => 'flash-outline', 'color' => '#dc2626', 'group' => 'Sociedade'],
            ['name' => 'Nação', 'slug' => 'nacao', 'icon' => 'flag-outline', 'color' => '#16a34a', 'group' => 'Sociedade'],
            ['name' => 'Responsabilidade Social', 'slug' => 'responsabilidade-social', 'icon' => 'hand-right-outline', 'color' => '#0891b2', 'group' => 'Sociedade'],
            ['name' => 'Ética', 'slug' => 'etica', 'icon' => 'checkmark-done-outline', 'color' => '#7c3aed', 'group' => 'Sociedade'],
            ['name' => 'Corrupção', 'slug' => 'corrupcao', 'icon' => 'close-circle-outline', 'color' => '#991b1b', 'group' => 'Sociedade'],

            // HISTÓRIA BÍBLICA
            ['name' => 'Patriarcas', 'slug' => 'patriarcas', 'icon' => 'people-outline', 'color' => '#92400e', 'group' => 'História Bíblica'],
            ['name' => 'Reis', 'slug' => 'reis', 'icon' => 'ribbon-outline', 'color' => '#fbbf24', 'group' => 'História Bíblica'],
            ['name' => 'Profetas', 'slug' => 'profetas', 'icon' => 'megaphone-outline', 'color' => '#7c3aed', 'group' => 'História Bíblica'],
            ['name' => 'Israel', 'slug' => 'israel', 'icon' => 'star-outline', 'color' => '#0ea5e9', 'group' => 'História Bíblica'],
            ['name' => 'Exílio', 'slug' => 'exilio', 'icon' => 'walk-outline', 'color' => '#64748b', 'group' => 'História Bíblica'],
            ['name' => 'Aliança', 'slug' => 'alianca', 'icon' => 'document-text-outline', 'color' => '#b45309', 'group' => 'História Bíblica'],
            ['name' => 'Promessas', 'slug' => 'promessas', 'icon' => 'bookmark-outline', 'color' => '#0ea5e9', 'group' => 'História Bíblica'],
            ['name' => 'Narrativas Históricas', 'slug' => 'narrativas-historicas', 'icon' => 'book-outline', 'color' => '#78716c', 'group' => 'História Bíblica'],

            // VIDA CRISTÃ
            ['name' => 'Vida Cristã', 'slug' => 'vida-crista', 'icon' => 'walk-outline', 'color' => '#059669', 'group' => 'Vida Cristã'],
            ['name' => 'Santidade Diária', 'slug' => 'santidade-diaria', 'icon' => 'today-outline', 'color' => '#8b5cf6', 'group' => 'Vida Cristã'],
            ['name' => 'Testemunho', 'slug' => 'testemunho', 'icon' => 'chatbubble-outline', 'color' => '#f97316', 'group' => 'Vida Cristã'],
            ['name' => 'Comportamento', 'slug' => 'comportamento', 'icon' => 'person-outline', 'color' => '#3b82f6', 'group' => 'Vida Cristã'],
            ['name' => 'Ética Cristã', 'slug' => 'etica-crista', 'icon' => 'checkmark-circle-outline', 'color' => '#22c55e', 'group' => 'Vida Cristã'],
            ['name' => 'Maturidade Espiritual', 'slug' => 'maturidade-espiritual', 'icon' => 'trending-up-outline', 'color' => '#14b8a6', 'group' => 'Vida Cristã'],
            ['name' => 'Crescimento Espiritual', 'slug' => 'crescimento-espiritual', 'icon' => 'leaf-outline', 'color' => '#84cc16', 'group' => 'Vida Cristã'],

            // PROMESSAS
            ['name' => 'Promessas de Deus', 'slug' => 'promessas-de-deus', 'icon' => 'bookmark-outline', 'color' => '#0ea5e9', 'group' => 'Promessas'],
            ['name' => 'Proteção', 'slug' => 'protecao', 'icon' => 'shield-checkmark-outline', 'color' => '#6366f1', 'group' => 'Promessas'],
            ['name' => 'Provisão', 'slug' => 'provisao', 'icon' => 'cash-outline', 'color' => '#10b981', 'group' => 'Promessas'],
            ['name' => 'Cura', 'slug' => 'cura', 'icon' => 'medkit-outline', 'color' => '#f43f5e', 'group' => 'Promessas'],
            ['name' => 'Libertação', 'slug' => 'libertacao', 'icon' => 'key-outline', 'color' => '#fbbf24', 'group' => 'Promessas'],
            ['name' => 'Segurança', 'slug' => 'seguranca', 'icon' => 'lock-closed-outline', 'color' => '#22c55e', 'group' => 'Promessas'],

            // JUSTIÇA
            ['name' => 'Justiça e Misericórdia', 'slug' => 'justica-e-misericordia', 'icon' => 'scale-outline', 'color' => '#7c3aed', 'group' => 'Justiça'],
            ['name' => 'Equidade', 'slug' => 'equidade', 'icon' => 'git-compare-outline', 'color' => '#3b82f6', 'group' => 'Justiça'],
            ['name' => 'Retidão', 'slug' => 'retidao', 'icon' => 'arrow-forward-outline', 'color' => '#059669', 'group' => 'Justiça'],
            ['name' => 'Juízo Justo', 'slug' => 'juizo-justo', 'icon' => 'hammer-outline', 'color' => '#475569', 'group' => 'Justiça'],
            ['name' => 'Defesa do Oprimido', 'slug' => 'defesa-do-oprimido', 'icon' => 'hand-left-outline', 'color' => '#f97316', 'group' => 'Justiça'],

            // PROFECIA
            ['name' => 'Profecia', 'slug' => 'profecia', 'icon' => 'megaphone-outline', 'color' => '#7c3aed', 'group' => 'Profecia'],
            ['name' => 'Profecias Messiânicas', 'slug' => 'profecias-messianicas', 'icon' => 'star-outline', 'color' => '#fbbf24', 'group' => 'Profecia'],
            ['name' => 'Profecias Futuras', 'slug' => 'profecias-futuras', 'icon' => 'hourglass-outline', 'color' => '#6366f1', 'group' => 'Profecia'],
            ['name' => 'Advertências Proféticas', 'slug' => 'advertencias-profeticas', 'icon' => 'warning-outline', 'color' => '#f59e0b', 'group' => 'Profecia'],

            // EMOÇÕES
            ['name' => 'Medo', 'slug' => 'medo', 'icon' => 'alert-circle-outline', 'color' => '#64748b', 'group' => 'Emoções'],
            ['name' => 'Ansiedade', 'slug' => 'ansiedade', 'icon' => 'pulse-outline', 'color' => '#f97316', 'group' => 'Emoções'],
            ['name' => 'Alegria', 'slug' => 'alegria', 'icon' => 'happy-outline', 'color' => '#fbbf24', 'group' => 'Emoções'],
            ['name' => 'Tristeza', 'slug' => 'tristeza', 'icon' => 'sad-outline', 'color' => '#475569', 'group' => 'Emoções'],
            ['name' => 'Confiança', 'slug' => 'confianca', 'icon' => 'shield-outline', 'color' => '#22c55e', 'group' => 'Emoções'],

            // OUTROS (Provérbios)
            ['name' => 'Justo x Perverso', 'slug' => 'justo-x-perverso', 'icon' => 'git-compare-outline', 'color' => '#475569', 'group' => 'Outros'],
            ['name' => 'Morte x Vida', 'slug' => 'morte-x-vida', 'icon' => 'pulse-outline', 'color' => '#64748b', 'group' => 'Outros'],
            ['name' => 'Negligência x Diligência', 'slug' => 'negligencia-x-diligencia', 'icon' => 'swap-horizontal-outline', 'color' => '#78716c', 'group' => 'Outros'],
            ['name' => 'Armadilhas', 'slug' => 'armadilhas', 'icon' => 'warning-outline', 'color' => '#dc2626', 'group' => 'Outros'],
            ['name' => 'Língua', 'slug' => 'lingua', 'icon' => 'chatbubble-outline', 'color' => '#f43f5e', 'group' => 'Outros'],
            ['name' => 'Coração', 'slug' => 'coracao', 'icon' => 'heart-outline', 'color' => '#ec4899', 'group' => 'Outros'],
            ['name' => 'Legado', 'slug' => 'legado', 'icon' => 'document-outline', 'color' => '#a855f7', 'group' => 'Outros'],
            ['name' => 'Bebida', 'slug' => 'bebida', 'icon' => 'wine-outline', 'color' => '#7c2d12', 'group' => 'Outros'],
            ['name' => 'Ecologia', 'slug' => 'ecologia', 'icon' => 'leaf-outline', 'color' => '#22c55e', 'group' => 'Outros'],
            ['name' => 'Mulher', 'slug' => 'mulher', 'icon' => 'woman-outline', 'color' => '#ec4899', 'group' => 'Outros'],
            ['name' => 'Perverso', 'slug' => 'perverso', 'icon' => 'skull-outline', 'color' => '#1e293b', 'group' => 'Outros'],
            ['name' => 'Outros', 'slug' => 'outros', 'icon' => 'ellipsis-horizontal-outline', 'color' => '#9ca3af', 'group' => 'Outros'],
        ];

        foreach ($categories as $categoryData) {
            Category::updateOrCreate(
                ['slug' => $categoryData['slug']],
                [
                    'name' => $categoryData['name'],
                    'slug' => $categoryData['slug'],
                    'icon' => $categoryData['icon'],
                    'color' => $categoryData['color'],
                    'description' => $categoryData['group'] ?? null,
                ]
            );
        }

        // Remover categorias antigas que não estão na lista
        $slugs = array_column($categories, 'slug');
        Category::whereNotIn('slug', $slugs)->delete();
    }
}
