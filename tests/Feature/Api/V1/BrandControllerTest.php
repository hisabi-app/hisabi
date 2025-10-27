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
}
