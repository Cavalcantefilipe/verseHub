<?php

namespace Tests\Feature;

use App\Models\BibleVerse;
use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\User;
use App\Models\UserVerseCategory;
use App\Services\AppleAuthService;
use App\Services\CommunityFeedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class AuthStoreComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_apple_identity_creates_an_account_and_returns_app_token(): void
    {
        $this->mock(AppleAuthService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('verifyIdentityToken')
                ->once()
                ->with('valid-apple-token')
                ->andReturn([
                    'sub' => 'apple-user-123',
                    'email' => 'private@privaterelay.appleid.com',
                    'email_verified' => true,
                ]);
        });

        $this->postJson('/api/auth/apple', [
            'identity_token' => 'valid-apple-token',
            'given_name' => 'Maria',
            'family_name' => 'Silva',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.name', 'Maria Silva')
            ->assertJsonPath('data.user.email', 'private@privaterelay.appleid.com')
            ->assertJsonStructure(['data' => ['token']]);

        $this->assertDatabaseHas('users', [
            'email' => 'private@privaterelay.appleid.com',
            'provider' => 'apple',
            'provider_id' => 'apple-user-123',
        ]);
    }

    public function test_account_can_be_deleted_inside_the_app(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $group = CategoryGroup::create(['name' => 'Sentimentos', 'slug' => 'delete-test', 'status' => 'approved']);
        $category = Category::create([
            'name' => 'Esperança', 'slug' => 'delete-hope',
            'category_group_id' => $group->id, 'status' => 'approved',
        ]);
        $verse = BibleVerse::create(['reference' => 'João 3:16', 'version' => 'NVI', 'text' => 'Porque Deus amou o mundo.']);
        UserVerseCategory::create(['user_id' => $user->id, 'bible_verse_id' => $verse->id, 'category_id' => $category->id]);
        UserVerseCategory::create(['user_id' => $otherUser->id, 'bible_verse_id' => $verse->id, 'category_id' => $category->id]);
        $post = app(CommunityFeedService::class)->refreshVerse($verse->id);
        $headers = [
            'Authorization' => 'Bearer '.JWTAuth::fromUser($user),
            'Accept' => 'application/json',
        ];
        $this->postJson("/api/community/posts/{$post->id}/like", [], $headers)->assertCreated();
        $this->postJson("/api/community/posts/{$post->id}/comments", ['body' => 'Comentário temporário'], $headers)->assertCreated();

        DB::table('user_public_profiles')->insert([
            'user_id' => $user->id,
            'display_name' => 'Perfil para excluir',
            'is_public' => true,
            'show_ranking' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->deleteJson('/api/auth/account', [], $headers)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('user_public_profiles', ['user_id' => $user->id]);
        $this->assertDatabaseHas('community_posts', [
            'id' => $post->id,
            'classifiers_count' => 1,
            'classifications_count' => 1,
            'likes_count' => 0,
            'comments_count' => 0,
        ]);
    }

    public function test_account_deletion_requires_authentication(): void
    {
        $this->deleteJson('/api/auth/account')->assertUnauthorized();
    }
}
