<?php

namespace Tests\Feature\Api\V1;

use App\Domains\Transaction\Models\Transaction;
use App\Models\Brand;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
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
        $response = $this->getJson('/api/v1/transactions');

        $response->assertStatus(401);
    }

    public function test_it_returns_transactions(): void
    {
        Transaction::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/transactions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'amount',
                        'created_at',
                        'note',
                        'brand' => [
                            'id',
                            'name',
                            'category' => [
                                'id',
                                'name',
                                'type',
                                'color',
                                'icon'
                            ]
                        ]
                    ]
                ],
                'paginatorInfo' => [
                    'hasMorePages',
                    'currentPage',
                    'lastPage',
                    'perPage',
                    'total'
                ]
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_it_returns_transactions_sorted_by_id_descending(): void
    {
        $transaction1 = Transaction::factory()->create();
        $transaction2 = Transaction::factory()->create();
        $transaction3 = Transaction::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/transactions');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertEquals($transaction3->id, $data[0]['id']);
        $this->assertEquals($transaction2->id, $data[1]['id']);
        $this->assertEquals($transaction1->id, $data[2]['id']);
    }

    public function test_it_includes_brand_and_category_relations(): void
    {
        $category = Category::factory()->create(['name' => 'Test Category']);
        $brand = Brand::factory()->create([
            'name' => 'Test Brand',
            'category_id' => $category->id
        ]);
        $transaction = Transaction::factory()->create([
            'brand_id' => $brand->id,
            'amount' => 100.50
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/transactions');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $transaction->id)
            ->assertJsonPath('data.0.amount', 100.50)
            ->assertJsonPath('data.0.brand.name', 'Test Brand')
            ->assertJsonPath('data.0.brand.category.name', 'Test Category');
    }

    public function test_it_paginates_results(): void
    {
        Transaction::factory()->count(15)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/transactions?perPage=10');

        $response->assertStatus(200)
            ->assertJsonPath('paginatorInfo.perPage', 10)
            ->assertJsonPath('paginatorInfo.currentPage', 1)
            ->assertJsonPath('paginatorInfo.hasMorePages', true)
            ->assertJsonPath('paginatorInfo.total', 15);

        $this->assertCount(10, $response->json('data'));
    }

    public function test_it_handles_page_parameter(): void
    {
        Transaction::factory()->count(15)->create();

        $page1Response = $this->actingAs($this->user)
            ->getJson('/api/v1/transactions?perPage=10&page=1');

        $page2Response = $this->actingAs($this->user)
            ->getJson('/api/v1/transactions?perPage=10&page=2');

        $page1Response->assertStatus(200)
            ->assertJsonPath('paginatorInfo.currentPage', 1);
        $this->assertCount(10, $page1Response->json('data'));

        $page2Response->assertStatus(200)
            ->assertJsonPath('paginatorInfo.currentPage', 2)
            ->assertJsonPath('paginatorInfo.hasMorePages', false);
        $this->assertCount(5, $page2Response->json('data'));
    }

    public function test_it_searches_by_amount(): void
    {
        Transaction::factory()->create(['amount' => 100.50]);
        Transaction::factory()->create(['amount' => 200.75]);
        $matchingTransaction = Transaction::factory()->create(['amount' => 150.25]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/transactions?filter[search]=150');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($matchingTransaction->id, $data[0]['id']);
    }

    public function test_it_searches_by_note(): void
    {
        Transaction::factory()->create(['note' => 'Grocery shopping']);
        Transaction::factory()->create(['note' => 'Fuel for car']);
        $matchingTransaction = Transaction::factory()->create(['note' => 'Coffee with friends']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/transactions?filter[search]=coffee');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($matchingTransaction->id, $data[0]['id']);
    }

    public function test_it_searches_by_brand_name(): void
    {
        $category = Category::factory()->create();
        $starbucks = Brand::factory()->create(['name' => 'Starbucks', 'category_id' => $category->id]);
        $mcdonalds = Brand::factory()->create(['name' => 'McDonalds', 'category_id' => $category->id]);
        
        Transaction::factory()->create(['brand_id' => $mcdonalds->id]);
        $matchingTransaction = Transaction::factory()->create(['brand_id' => $starbucks->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/transactions?filter[search]=starbucks');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($matchingTransaction->id, $data[0]['id']);
        $this->assertEquals('Starbucks', $data[0]['brand']['name']);
    }

    public function test_it_searches_across_multiple_fields(): void
    {
        $category = Category::factory()->create();
        $coffee = Brand::factory()->create(['name' => 'Coffee Shop', 'category_id' => $category->id]);
        
        $t1 = Transaction::factory()->create([
            'brand_id' => $coffee->id,
            'amount' => 100,
            'note' => 'Regular purchase'
        ]);
        
        $t2 = Transaction::factory()->create([
            'brand_id' => Brand::factory()->create(['category_id' => $category->id]),
            'amount' => 200,
            'note' => 'Coffee beans'
        ]);
        
        $t3 = Transaction::factory()->create([
            'brand_id' => Brand::factory()->create(['category_id' => $category->id]),
            'amount' => 300,
            'note' => 'Other purchase'
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/transactions?filter[search]=coffee');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(2, $data);
        
        $ids = collect($data)->pluck('id')->toArray();
        $this->assertContains($t1->id, $ids);
        $this->assertContains($t2->id, $ids);
        $this->assertNotContains($t3->id, $ids);
    }

    public function test_it_returns_empty_result_when_search_has_no_matches(): void
    {
        Transaction::factory()->count(5)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/transactions?filter[search]=nonexistent');

        $response->assertStatus(200)
            ->assertJsonPath('paginatorInfo.total', 0);
        
        $this->assertEmpty($response->json('data'));
    }

    public function test_it_returns_empty_array_when_no_transactions_exist(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/transactions');

        $response->assertStatus(200)
            ->assertJsonPath('paginatorInfo.total', 0)
            ->assertJsonPath('paginatorInfo.hasMorePages', false);
        
        $this->assertEmpty($response->json('data'));
    }

    public function test_it_handles_per_page_parameter(): void
    {
        Transaction::factory()->count(50)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/transactions?perPage=25');

        $response->assertStatus(200)
            ->assertJsonPath('paginatorInfo.perPage', 25);
        
        $this->assertCount(25, $response->json('data'));
    }

    public function test_it_uses_default_per_page_when_not_specified(): void
    {
        Transaction::factory()->count(60)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/transactions');

        $response->assertStatus(200)
            ->assertJsonPath('paginatorInfo.perPage', 50);
        
        $this->assertCount(50, $response->json('data'));
    }

    public function test_it_combines_search_and_pagination(): void
    {
        $category = Category::factory()->create();
        
        // Create 15 transactions with "test" in the note
        for ($i = 0; $i < 15; $i++) {
            Transaction::factory()->create(['note' => "Test transaction $i"]);
        }
        
        // Create 5 other transactions
        Transaction::factory()->count(5)->create(['note' => 'Other transaction']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/transactions?filter[search]=test&perPage=10&page=1');

        $response->assertStatus(200)
            ->assertJsonPath('paginatorInfo.perPage', 10)
            ->assertJsonPath('paginatorInfo.currentPage', 1)
            ->assertJsonPath('paginatorInfo.total', 15)
            ->assertJsonPath('paginatorInfo.hasMorePages', true);
        
        $this->assertCount(10, $response->json('data'));
    }

    public function test_it_returns_correct_json_structure_for_pagination(): void
    {
        Transaction::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/transactions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'paginatorInfo' => [
                    'hasMorePages',
                    'currentPage',
                    'lastPage',
                    'perPage',
                    'total'
                ]
            ]);

        $paginatorInfo = $response->json('paginatorInfo');
        $this->assertIsBool($paginatorInfo['hasMorePages']);
        $this->assertIsInt($paginatorInfo['currentPage']);
        $this->assertIsInt($paginatorInfo['lastPage']);
        $this->assertIsInt($paginatorInfo['perPage']);
        $this->assertIsInt($paginatorInfo['total']);
    }

    public function test_it_handles_search_with_special_characters(): void
    {
        Transaction::factory()->create(['note' => 'Test with discount']);
        $matchingTransaction = Transaction::factory()->create(['note' => 'Purchase at 50% off']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/transactions?filter[search]=50');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($matchingTransaction->id, $data[0]['id']);
    }

    public function test_it_handles_case_insensitive_search(): void
    {
        $matchingTransaction = Transaction::factory()->create(['note' => 'Coffee Shop Purchase']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/transactions?filter[search]=COFFEE');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($matchingTransaction->id, $data[0]['id']);
    }

    public function test_create_requires_authentication(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create(['category_id' => $category->id]);

        $response = $this->postJson('/api/v1/transactions', [
            'amount' => 100.50,
            'brand_id' => $brand->id,
            'created_at' => now()->format('Y-m-d'),
            'note' => 'Test transaction'
        ]);

        $response->assertStatus(401);
    }

    public function test_it_creates_a_transaction(): void
    {
        $category = Category::factory()->create(['name' => 'Test Category']);
        $brand = Brand::factory()->create([
            'name' => 'Test Brand',
            'category_id' => $category->id
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/transactions', [
                'amount' => 100.50,
                'brand_id' => $brand->id,
                'created_at' => now()->format('Y-m-d'),
                'note' => 'Test transaction'
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'transaction' => [
                    'id',
                    'amount',
                    'created_at',
                    'note',
                    'brand' => [
                        'id',
                        'name',
                        'category' => [
                            'id',
                            'name',
                            'type'
                        ]
                    ]
                ]
            ])
            ->assertJsonPath('transaction.amount', 100.50)
            ->assertJsonPath('transaction.brand.name', 'Test Brand')
            ->assertJsonPath('transaction.brand.category.name', 'Test Category')
            ->assertJsonPath('transaction.note', 'Test transaction');

        $this->assertDatabaseHas('transactions', [
            'amount' => 100.50,
            'brand_id' => $brand->id,
            'note' => 'Test transaction'
        ]);
    }

    public function test_create_validates_required_amount(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/transactions', [
                'brand_id' => $brand->id,
                'created_at' => now()->format('Y-m-d')
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_create_validates_required_brand_id(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/transactions', [
                'amount' => 100.50,
                'created_at' => now()->format('Y-m-d')
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['brand_id']);
    }

    public function test_create_validates_required_created_at(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/transactions', [
                'amount' => 100.50,
                'brand_id' => $brand->id
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['created_at']);
    }

    public function test_create_validates_brand_exists(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/transactions', [
                'amount' => 100.50,
                'brand_id' => 99999,
                'created_at' => now()->format('Y-m-d')
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['brand_id']);
    }

    public function test_create_validates_amount_is_numeric(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/transactions', [
                'amount' => 'not-a-number',
                'brand_id' => $brand->id,
                'created_at' => now()->format('Y-m-d')
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_create_validates_amount_is_positive(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/transactions', [
                'amount' => -100,
                'brand_id' => $brand->id,
                'created_at' => now()->format('Y-m-d')
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_create_accepts_transaction_without_note(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/transactions', [
                'amount' => 100.50,
                'brand_id' => $brand->id,
                'created_at' => now()->format('Y-m-d')
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('transactions', [
            'amount' => 100.50,
            'brand_id' => $brand->id,
            'note' => null
        ]);
    }

    public function test_create_validates_note_max_length(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/transactions', [
                'amount' => 100.50,
                'brand_id' => $brand->id,
                'created_at' => now()->format('Y-m-d'),
                'note' => str_repeat('a', 1001)
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['note']);
    }

    public function test_create_validates_created_at_is_valid_date(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/transactions', [
                'amount' => 100.50,
                'brand_id' => $brand->id,
                'created_at' => 'not-a-date'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['created_at']);
    }
}

