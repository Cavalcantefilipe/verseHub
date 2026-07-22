<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class CategoryV2ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_categories_only_expose_approved_content(): void
    {
        $approvedGroup = $this->createGroup('approved');
        $pendingGroup = $this->createGroup('pending');
        $approved = $this->createCategory($approvedGroup, 'approved');
        $pending = $this->createCategory($pendingGroup, 'pending');

        $this->getJson('/api/categories')
            ->assertOk()
            ->assertJsonPath('data.0.group.id', $approvedGroup->id)
            ->assertJsonPath('data.0.categories.0.id', $approved->id)
            ->assertJsonMissingPath('data.0.categories.0.created_by_user_id')
            ->assertJsonMissing(['id' => $pending->id]);

        $this->getJson("/api/categories/{$pending->id}")->assertNotFound();
        $this->getJson("/api/categories/{$approved->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $approved->id);
    }

    public function test_authenticated_user_can_list_and_create_own_custom_categories(): void
    {
        $user = User::factory()->create();
        $approvedGroup = $this->createGroup('approved');
        $pendingGroup = $this->createGroup('pending');
        $headers = $this->authHeaders($user);

        $this->postJson('/api/categories/custom', [
            'name' => '…quando preciso recomeçar',
            'category_group_id' => $pendingGroup->id,
        ], $headers)->assertUnprocessable();

        $created = $this->postJson('/api/categories/custom', [
            'name' => '…quando preciso recomeçar',
            'category_group_id' => $approvedGroup->id,
        ], $headers)
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->json('data');

        $this->getJson('/api/categories/mine', $headers)
            ->assertOk()
            ->assertJsonPath('data.0.id', $created['id']);
    }

    public function test_deleting_custom_category_also_removes_its_empty_pending_group(): void
    {
        $user = User::factory()->create();
        $headers = $this->authHeaders($user);

        $created = $this->postJson('/api/categories/custom', [
            'name' => '…quando preciso de coragem',
            'new_group_name' => 'Novos começos',
        ], $headers)
            ->assertCreated()
            ->json('data');

        $groupId = $created['category_group_id'];

        $this->deleteJson("/api/categories/custom/{$created['id']}", [], $headers)
            ->assertOk();

        $this->assertDatabaseMissing('categories', ['id' => $created['id']]);
        $this->assertDatabaseMissing('category_groups', ['id' => $groupId]);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'custom_categories_count' => 0,
        ]);
    }

    public function test_authenticated_user_response_matches_mobile_and_admin_contracts(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'can_create_categories' => false,
            'custom_categories_count' => 3,
        ]);

        $this->getJson('/api/auth/user', $this->authHeaders($admin))
            ->assertOk()
            ->assertJsonPath('data.user.id', $admin->id)
            ->assertJsonPath('data.user.is_admin', true)
            ->assertJsonPath('data.user.can_create_categories', false)
            ->assertJsonPath('data.user.custom_categories_count', 3);
    }

    public function test_admin_routes_reject_non_admin_users(): void
    {
        $user = User::factory()->create();

        $this->getJson('/api/admin/dashboard', $this->authHeaders($user))
            ->assertForbidden();
    }

    public function test_admin_routes_allow_admin_users(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->getJson('/api/admin/dashboard', $this->authHeaders($admin))
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'pending_categories',
                'pending_groups',
                'total_users',
                'classifications_total',
            ]]);
    }

    private function authHeaders(User $user): array
    {
        return [
            'Authorization' => 'Bearer '.JWTAuth::fromUser($user),
            'Accept' => 'application/json',
        ];
    }

    private function createGroup(string $status): CategoryGroup
    {
        return CategoryGroup::create([
            'name' => ucfirst($status).' Group '.uniqid(),
            'slug' => $status.'-group-'.uniqid(),
            'status' => $status,
        ]);
    }

    private function createCategory(CategoryGroup $group, string $status): Category
    {
        return Category::create([
            'name' => ucfirst($status).' Category '.uniqid(),
            'slug' => $status.'-category-'.uniqid(),
            'category_group_id' => $group->id,
            'status' => $status,
        ]);
    }
}
