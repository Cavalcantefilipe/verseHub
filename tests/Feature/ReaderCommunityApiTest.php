<?php

namespace Tests\Feature;

use App\Models\BiblePassage;
use App\Models\BibleVerse;
use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\User;
use App\Models\UserVerseCategory;
use App\Services\CommunityFeedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class ReaderCommunityApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_search_is_paginated_and_returns_navigation_fields(): void
    {
        BiblePassage::create([
            'version' => 'nvi', 'book_abbrev' => 'jo', 'book_name' => 'João',
            'chapter' => 3, 'verse_number' => 16, 'text' => 'Porque Deus amou o mundo.',
        ]);

        $this->getJson('/api/bible/nvi/search?q=amou')
            ->assertOk()
            ->assertJsonPath('data.0.reference', 'João 3:16')
            ->assertJsonPath('data.0.book_abbrev', 'jo')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_community_feed_uses_cursor_contract_and_batched_category_aggregate(): void
    {
        [$user, $category, $verse] = $this->classifiedVerse();
        app(CommunityFeedService::class)->refreshVerse($verse->id);

        $this->getJson('/api/community-feed?per_page=10')
            ->assertOk()
            ->assertJsonPath('data.0.reference', 'João 3:16')
            ->assertJsonPath('data.0.total_people', 1)
            ->assertJsonPath('data.0.top_categories.0.id', $category->id)
            ->assertJsonStructure(['meta' => ['next_cursor', 'has_more', 'per_page', 'sort']]);
    }

    public function test_like_is_idempotent_and_comment_counter_is_materialized(): void
    {
        [$user, , $verse] = $this->classifiedVerse();
        $post = app(CommunityFeedService::class)->refreshVerse($verse->id);
        $headers = $this->authHeaders($user);

        $this->postJson("/api/community/posts/{$post->id}/like", [], $headers)->assertCreated()->assertJsonPath('data.likes_count', 1);
        $this->postJson("/api/community/posts/{$post->id}/like", [], $headers)->assertOk()->assertJsonPath('data.likes_count', 1);
        $this->postJson("/api/community/posts/{$post->id}/comments", ['body' => 'Uma palavra de esperança.'], $headers)->assertCreated();
        $this->assertDatabaseHas('community_posts', ['id' => $post->id, 'likes_count' => 1, 'comments_count' => 1]);
        $this->assertDatabaseHas('user_stats', ['user_id' => $user->id, 'total_points' => 3]);
    }

    public function test_feed_accepts_each_indexed_sort_mode(): void
    {
        [, , $verse] = $this->classifiedVerse();
        app(CommunityFeedService::class)->refreshVerse($verse->id);

        foreach (['popular', 'recent', 'oldest', 'liked', 'commented'] as $sort) {
            $this->getJson('/api/community-feed?sort='.$sort)
                ->assertOk()
                ->assertJsonPath('meta.sort', $sort);
        }
    }

    public function test_profile_photo_and_public_impact_are_available(): void
    {
        Storage::fake('public');
        [$user, , $verse] = $this->classifiedVerse();
        app(CommunityFeedService::class)->refreshVerse($verse->id);
        $headers = $this->authHeaders($user);

        $this->putJson('/api/me/public-profile', [
            'display_name' => 'Leitor Teste',
            'bio' => 'Caminhando um capítulo por dia.',
            'is_public' => true,
            'show_ranking' => true,
        ], $headers)->assertOk();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Leitor Teste']);

        $avatarUrl = $this->postJson('/api/me/profile-avatar', [
            'avatar' => UploadedFile::fake()->image('avatar.jpg', 300, 300),
        ], $headers)->assertOk()->assertJsonStructure(['data' => ['avatar_url']])->json('data.avatar_url');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'avatar' => $avatarUrl]);

        $post = app(CommunityFeedService::class)->refreshVerse($verse->id);
        $this->postJson("/api/community/posts/{$post->id}/like", [], $headers)->assertCreated();
        $this->getJson('/api/me/public-profile', $headers)
            ->assertOk()
            ->assertJsonPath('data.stats.likes_received', 1);

        $this->getJson("/api/community/users/{$user->id}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Leitor Teste')
            ->assertJsonStructure(['data' => ['stats' => [
                'points', 'level', 'streak_days', 'classifications', 'likes_received', 'comments_received',
            ], 'recent_contributions']]);
    }

    public function test_daily_activity_is_idempotent_and_recovers_historical_points(): void
    {
        $user = User::factory()->create();
        $headers = $this->authHeaders($user);

        foreach ([11, 12] as $verseId) {
            DB::table('user_activity_events')->insert([
                'user_id' => $user->id,
                'event_type' => 'verse_classified',
                'event_data' => json_encode(['bible_verse_id' => $verseId, 'is_new' => true]),
                'created_at' => now(),
            ]);
        }

        $this->postJson('/api/me/activity/daily', [], $headers)
            ->assertCreated()
            ->assertJsonPath('data.recorded', true)
            ->assertJsonPath('data.points', 22)
            ->assertJsonPath('data.streak_days', 1)
            ->assertJsonPath('data.classifications', 2);

        $this->postJson('/api/me/activity/daily', [], $headers)
            ->assertOk()
            ->assertJsonPath('data.recorded', false)
            ->assertJsonPath('data.points', 22);

        $this->assertDatabaseCount('user_activity_days', 1);
        $this->assertDatabaseHas('user_stats', [
            'user_id' => $user->id,
            'total_points' => 22,
            'current_streak_days' => 1,
            'classifications_count' => 2,
        ]);
    }

    public function test_reading_state_and_saved_verse_are_upserts(): void
    {
        $user = User::factory()->create();
        $headers = $this->authHeaders($user);
        $state = ['version' => 'nvi', 'book_abbrev' => 'jo', 'book_name' => 'João', 'chapter' => 3];
        $this->putJson('/api/me/reading-state', $state, $headers)->assertOk()->assertJsonPath('data.chapter', 3);

        $verse = ['reference' => 'João 3:16', 'version' => 'NVI', 'text' => 'Porque Deus amou o mundo.', 'is_favorite' => true];
        $this->putJson('/api/me/saved-verses', $verse, $headers)->assertCreated();
        $this->putJson('/api/me/saved-verses', [...$verse, 'note' => 'Esperança'], $headers)->assertOk();
        $this->assertDatabaseCount('user_saved_verses', 1);
    }

    public function test_classification_limits_emotions_to_three(): void
    {
        $user = User::factory()->create();
        $headers = $this->authHeaders($user);
        $group = CategoryGroup::create([
            'name' => 'Sentimentos',
            'slug' => 'sentimentos-teste',
            'classification_kind' => 'emotion',
            'selection_limit' => 3,
            'status' => 'approved',
        ]);
        $categoryIds = collect(['Em paz', 'Com esperança', 'Com gratidão', 'Com coragem'])
            ->map(fn (string $name, int $index) => Category::create([
                'name' => $name,
                'slug' => "sentimento-teste-{$index}",
                'category_group_id' => $group->id,
                'status' => 'approved',
            ])->id)
            ->all();

        $payload = [
            'reference' => 'Salmos 23:1',
            'version' => 'NVI',
            'text' => 'O Senhor é o meu pastor.',
            'category_ids' => $categoryIds,
        ];

        $this->postJson('/api/classify-auth', $payload, $headers)
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Escolha no máximo três sentimentos.');

        $validPayload = [...$payload, 'category_ids' => array_slice($categoryIds, 0, 3)];
        $this->postJson('/api/classify-auth', $validPayload, $headers)
            ->assertCreated()
            ->assertJsonCount(3, 'data.categories');
        $this->postJson('/api/classify-auth', $validPayload, $headers)->assertOk();

        $this->assertDatabaseHas('user_stats', [
            'user_id' => $user->id,
            'total_points' => 10,
            'classifications_count' => 1,
        ]);
    }

    private function classifiedVerse(): array
    {
        $user = User::factory()->create();
        $group = CategoryGroup::create(['name' => 'Emoções', 'slug' => 'emocoes', 'status' => 'approved']);
        $category = Category::create([
            'name' => '…quando preciso de esperança', 'slug' => 'esperanca',
            'category_group_id' => $group->id, 'status' => 'approved',
        ]);
        $verse = BibleVerse::create(['reference' => 'João 3:16', 'version' => 'NVI', 'text' => 'Porque Deus amou o mundo.']);
        UserVerseCategory::create(['user_id' => $user->id, 'bible_verse_id' => $verse->id, 'category_id' => $category->id]);

        return [$user, $category, $verse];
    }

    private function authHeaders(User $user): array
    {
        return ['Authorization' => 'Bearer '.JWTAuth::fromUser($user), 'Accept' => 'application/json'];
    }
}
