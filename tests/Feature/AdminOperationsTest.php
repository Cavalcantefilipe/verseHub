<?php

namespace Tests\Feature;

use App\Models\BibleVerse;
use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class AdminOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_device_push_registration_is_idempotent_and_encrypted(): void
    {
        $user = User::factory()->create();
        $headers = $this->headers($user);
        $payload = [
            'token' => 'ExponentPushToken[test-device-1234567890]',
            'platform' => 'android',
            'device_name' => 'Pixel',
            'app_version' => '1.3.2',
        ];

        $this->putJson('/api/me/push-devices', $payload, $headers)->assertOk();
        $this->putJson('/api/me/push-devices', $payload, $headers)->assertOk();

        $this->assertDatabaseCount('push_devices', 1);
        $this->assertDatabaseHas('push_devices', [
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $payload['token']),
            'enabled' => true,
        ]);
        $this->assertDatabaseMissing('push_devices', ['token' => $payload['token']]);
    }

    public function test_admin_can_schedule_multiple_daily_verses_and_home_keeps_compatibility(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $headers = $this->headers($admin);
        foreach ([1 => 'João 3:16', 2 => 'Salmos 23:1'] as $position => $reference) {
            $this->postJson('/api/admin/daily-verses', [
                'publish_date' => now()->toDateString(),
                'position' => $position,
                'reference' => $reference,
                'version' => 'NVI',
                'text' => 'Texto autorizado '.$position,
                'is_active' => true,
            ], $headers)->assertCreated();
        }

        $this->getJson('/api/home')
            ->assertOk()
            ->assertJsonCount(2, 'data.daily_verses')
            ->assertJsonPath('data.daily_verse.reference', 'João 3:16');
    }

    public function test_admin_can_adjust_points_identity_ban_and_moderate_comment(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['name' => 'Nome antigo']);
        $headers = $this->headers($admin);

        $this->patchJson("/api/admin/users/{$user->id}/identity", [
            'name' => 'Nome revisado', 'avatar' => null,
        ], $headers)->assertOk()->assertJsonPath('data.name', 'Nome revisado');
        $this->postJson("/api/admin/users/{$user->id}/points", [
            'points' => 120, 'reason' => 'Reconhecimento de participação',
        ], $headers)->assertOk()->assertJsonPath('data.total_points', 120);

        $verse = BibleVerse::create(['reference' => 'João 3:16', 'version' => 'NVI', 'text' => 'Texto']);
        $post = CommunityPost::create(['bible_verse_id' => $verse->id, 'comments_count' => 1]);
        $comment = CommunityComment::create(['community_post_id' => $post->id, 'user_id' => $user->id, 'body' => 'Comentário', 'status' => 'published']);
        $this->patchJson("/api/admin/moderation/comments/{$comment->id}", ['is_featured' => true], $headers)
            ->assertOk()->assertJsonPath('data.is_featured', true);

        $this->postJson("/api/admin/users/{$user->id}/ban", ['banned' => true, 'reason' => 'Teste'], $headers)->assertOk();
        $this->getJson('/api/me/public-profile', $this->headers($user))->assertForbidden();
        $this->assertDatabaseCount('admin_audit_logs', 4);
    }

    public function test_mobile_admin_page_is_publicly_reachable_but_api_remains_protected(): void
    {
        $this->get('/admin')->assertOk()->assertSee('VerseHub Admin')->assertSee('viewport-fit=cover', false);
        $this->getJson('/api/admin/dashboard')->assertUnauthorized();
    }

    private function headers(User $user): array
    {
        return ['Authorization' => 'Bearer '.JWTAuth::fromUser($user), 'Accept' => 'application/json'];
    }
}
