<?php

namespace Tests\Feature\Api\V1;

use App\Domains\Category\Models\Category;
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

    public function test_it_requires_authentication_for_store(): void
    {
        $response = $this->postJson('/api/v1/categories', []);
        $response->assertStatus(401);
    }

    public function test_it_creates_a_category(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/categories', [
                'name' => 'Test Category',
                'type' => 'EXPENSES',
                'color' => 'red',
                'icon' => 'wallet',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'category' => [
                    'id',
                    'name',
                    'type',
                    'color',
                    'icon',
                    'transactions_count',
                ],
            ])
            ->assertJsonPath('category.name', 'Test Category')
            ->assertJsonPath('category.type', 'EXPENSES')
            ->assertJsonPath('category.color', 'red')
            ->assertJsonPath('category.icon', 'wallet');

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
            'type' => 'EXPENSES',
            'color' => 'red',
            'icon' => 'wallet',
        ]);
    }

    public function test_it_validates_required_fields_for_store(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/categories', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'type', 'color', 'icon']);
    }

    public function test_it_validates_type_is_valid(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/categories', [
                'name' => 'Test Category',
                'type' => 'INVALID_TYPE',
                'color' => 'red',
                'icon' => 'wallet',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_it_accepts_all_valid_category_types(): void
    {
        $types = ['INCOME', 'EXPENSES', 'SAVINGS', 'INVESTMENT'];

        foreach ($types as $type) {
            $response = $this->actingAs($this->user)
                ->postJson('/api/v1/categories', [
                    'name' => "Test Category {$type}",
                    'type' => $type,
                    'color' => 'blue',
                    'icon' => 'wallet',
                ]);

            $response->assertStatus(201);
        }

        $this->assertDatabaseCount('categories', 4);
    }

    public function test_it_requires_authentication_for_update(): void
    {
        $category = Category::factory()->create();

        $response = $this->putJson("/api/v1/categories/{$category->id}", []);
        $response->assertStatus(401);
    }

    public function test_it_updates_a_category(): void
    {
        $category = Category::factory()->create([
            'name' => 'Old Name',
            'type' => 'EXPENSES',
            'color' => 'red',
            'icon' => 'wallet',
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/categories/{$category->id}", [
                'name' => 'New Name',
                'type' => 'INCOME',
                'color' => 'blue',
                'icon' => 'money',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'category' => [
                    'id',
                    'name',
                    'type',
                    'color',
                    'icon',
                    'transactions_count',
                ],
            ])
            ->assertJsonPath('category.name', 'New Name')
            ->assertJsonPath('category.type', 'INCOME')
            ->assertJsonPath('category.color', 'blue')
            ->assertJsonPath('category.icon', 'money');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'New Name',
            'type' => 'INCOME',
            'color' => 'blue',
            'icon' => 'money',
        ]);
    }

    public function test_it_validates_required_fields_for_update(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/categories/{$category->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'type', 'color', 'icon']);
    }

    public function test_it_validates_type_is_valid_for_update(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/categories/{$category->id}", [
                'name' => 'Test Category',
                'type' => 'INVALID_TYPE',
                'color' => 'red',
                'icon' => 'wallet',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_it_returns_404_for_non_existent_category(): void
    {
        $response = $this->actingAs($this->user)
            ->putJson('/api/v1/categories/999', [
                'name' => 'Test Category',
                'type' => 'EXPENSES',
                'color' => 'red',
                'icon' => 'wallet',
            ]);

        $response->assertStatus(404);
    }
}
