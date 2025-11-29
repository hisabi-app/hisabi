<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_it_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/categories/all');
        $response->assertStatus(401);
    }

    public function test_it_returns_all_categories(): void
    {
        $categories = Category::factory()->count(10)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/categories/all');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'type',
                        'color',
                        'icon',
                        'transactionsCount',
                    ],
                ],
            ]);

        $this->assertCount(10, $response->json('data'));
    }

    public function test_it_includes_transactions_count(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/categories/all');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.transactionsCount', 0);
    }
}
