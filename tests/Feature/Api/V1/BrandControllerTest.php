<?php

namespace Tests\Feature\Api\V1;

use App\Domains\Brand\Models\Brand;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandControllerTest extends TestCase
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
        $response = $this->getJson('/api/v1/brands');
        $response->assertStatus(401);
    }

    public function test_it_returns_paginated_brands(): void
    {
        $category = Category::factory()->create();
        $brands = Brand::factory()->count(10)->create([
            'category_id' => $category->id
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/brands');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'category' => [
                            'id',
                            'name',
                            'color',
                            'icon',
                        ],
                        'transactionsCount',
                    ],
                ],
                'paginatorInfo' => [
                    'hasMorePages',
                    'currentPage',
                    'lastPage',
                    'perPage',
                    'total',
                ],
            ]);

        $this->assertEquals(10, $response->json('paginatorInfo.total'));
    }

    public function test_it_filters_brands_by_search(): void
    {
        $category = Category::factory()->create(['name' => 'Food']);
        $brand1 = Brand::factory()->create([
            'name' => 'McDonald',
            'category_id' => $category->id
        ]);
        $brand2 = Brand::factory()->create([
            'name' => 'Starbucks',
            'category_id' => $category->id
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/brands?filter[search]=McDonald');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('paginatorInfo.total'));
        $this->assertEquals('McDonald', $response->json('data.0.name'));
    }

    public function test_it_filters_brands_by_category(): void
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        $brand1 = Brand::factory()->create(['category_id' => $category1->id]);
        $brand2 = Brand::factory()->create(['category_id' => $category2->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/brands?filter[category_id]={$category1->id}");

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('paginatorInfo.total'));
        $this->assertEquals($brand1->id, $response->json('data.0.id'));
    }

    public function test_it_includes_transactions_count(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/brands');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.transactionsCount', 0);
    }

    public function test_it_respects_per_page_parameter(): void
    {
        $category = Category::factory()->create();
        Brand::factory()->count(30)->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/brands?perPage=10');

        $response->assertStatus(200);
        $this->assertEquals(10, $response->json('paginatorInfo.perPage'));
        $this->assertEquals(10, count($response->json('data')));
    }

    public function test_it_requires_authentication_for_store(): void
    {
        $response = $this->postJson('/api/v1/brands', []);
        $response->assertStatus(401);
    }

    public function test_it_creates_a_brand(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/brands', [
                'name' => 'Test Brand',
                'category_id' => $category->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'brand' => [
                    'id',
                    'name',
                    'category' => [
                        'id',
                        'name',
                    ],
                    'transactions_count',
                ],
            ])
            ->assertJsonPath('brand.name', 'Test Brand')
            ->assertJsonPath('brand.category.id', $category->id);

        $this->assertDatabaseHas('brands', [
            'name' => 'Test Brand',
            'category_id' => $category->id,
        ]);
    }

    public function test_it_validates_required_fields_for_store(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/brands', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'category_id']);
    }

    public function test_it_validates_category_exists_for_store(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/brands', [
                'name' => 'Test Brand',
                'category_id' => 999,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    }

    public function test_it_requires_authentication_for_update(): void
    {
        $brand = Brand::factory()->create();

        $response = $this->putJson("/api/v1/brands/{$brand->id}", []);
        $response->assertStatus(401);
    }

    public function test_it_updates_a_brand(): void
    {
        $category = Category::factory()->create();
        $newCategory = Category::factory()->create();
        $brand = Brand::factory()->create([
            'name' => 'Old Name',
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/brands/{$brand->id}", [
                'name' => 'New Name',
                'category_id' => $newCategory->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'brand' => [
                    'id',
                    'name',
                    'category' => [
                        'id',
                        'name',
                    ],
                    'transactions_count',
                ],
            ])
            ->assertJsonPath('brand.name', 'New Name')
            ->assertJsonPath('brand.category.id', $newCategory->id);

        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'name' => 'New Name',
            'category_id' => $newCategory->id,
        ]);
    }

    public function test_it_validates_required_fields_for_update(): void
    {
        $brand = Brand::factory()->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/brands/{$brand->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'category_id']);
    }

    public function test_it_validates_category_exists_for_update(): void
    {
        $brand = Brand::factory()->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/brands/{$brand->id}", [
                'name' => 'Test Brand',
                'category_id' => 999,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    }

    public function test_it_returns_404_for_non_existent_brand(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)
            ->putJson('/api/v1/brands/999', [
                'name' => 'Test Brand',
                'category_id' => $category->id,
            ]);

        $response->assertStatus(404);
    }
}
