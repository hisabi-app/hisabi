<?php

namespace Tests\Feature\Brands;

use App\Domains\Brand\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllBrandsTest extends TestCase
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
        $response = $this->getJson('/api/v1/brands/all');
        $response->assertStatus(401);
    }

    public function test_it_returns_correct_data(): void
    {
        $brand = Brand::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/brands/all');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'id' => $brand->id,
                        'name' => $brand->name,
                        'category' => [
                            'id' => $brand->category->id,
                            'name' => $brand->category->name,
                            'color' => $brand->category->color,
                            'icon' => $brand->category->icon,
                        ],
                    ]
                ],
            ]);
    }
}
