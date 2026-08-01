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
