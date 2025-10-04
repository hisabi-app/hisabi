<?php

namespace Tests\Unit\Domains\Transaction\Services;

use App\Domains\Transaction\Models\Transaction;
use App\Domains\Transaction\Services\TransactionService;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    private TransactionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TransactionService();
    }

    public function test_it_returns_paginated_transactions(): void
    {
        // Arrange
        Transaction::factory()->count(3)->create();

        // Act
        $result = $this->service->getPaginated(perPage: 10);

        // Assert
        $this->assertCount(3, $result->items());
        $this->assertEquals(3, $result->total());
        $this->assertEquals(1, $result->currentPage());
        $this->assertFalse($result->hasMorePages());
    }

    public function test_it_paginates_results_correctly(): void
    {
        // Arrange
        Transaction::factory()->count(15)->create();

        // Act
        $page1 = $this->service->getPaginated(perPage: 10);
        
        // Simulate page 2 request
        request()->merge(['page' => 2]);
        $page2 = $this->service->getPaginated(perPage: 10);

        // Assert
        $this->assertCount(10, $page1->items());
        $this->assertTrue($page1->hasMorePages());
        $this->assertEquals(1, $page1->currentPage());
        
        $this->assertCount(5, $page2->items());
        $this->assertFalse($page2->hasMorePages());
        $this->assertEquals(2, $page2->currentPage());
    }

    public function test_it_sorts_by_id_descending_by_default(): void
    {
        // Arrange
        $transaction1 = Transaction::factory()->create();
        $transaction2 = Transaction::factory()->create();
        $transaction3 = Transaction::factory()->create();

        // Act
        $result = $this->service->getPaginated(perPage: 10);

        // Assert
        $items = $result->items();
        $this->assertEquals($transaction3->id, $items[0]->id);
        $this->assertEquals($transaction2->id, $items[1]->id);
        $this->assertEquals($transaction1->id, $items[2]->id);
    }

    public function test_it_includes_brand_and_category_relations(): void
    {
        // Arrange
        $transaction = Transaction::factory()->create();

        // Act
        $result = $this->service->getPaginated(perPage: 10);

        // Assert
        $item = $result->items()[0];
        $this->assertTrue($item->relationLoaded('brand'));
        $this->assertTrue($item->brand->relationLoaded('category'));
        $this->assertEquals($transaction->brand->name, $item->brand->name);
        $this->assertEquals($transaction->brand->category->name, $item->brand->category->name);
    }

    public function test_it_searches_by_amount(): void
    {
        // Arrange
        Transaction::factory()->create(['amount' => 100.50]);
        Transaction::factory()->create(['amount' => 200.75]);
        $matchingTransaction = Transaction::factory()->create(['amount' => 150.25]);

        // Act
        request()->merge(['filter' => ['search' => '150']]);
        $result = $this->service->getPaginated(perPage: 10);

        // Assert
        $this->assertCount(1, $result->items());
        $this->assertEquals($matchingTransaction->id, $result->items()[0]->id);
    }

    public function test_it_searches_by_note(): void
    {
        // Arrange
        Transaction::factory()->create(['note' => 'Grocery shopping']);
        Transaction::factory()->create(['note' => 'Fuel for car']);
        $matchingTransaction = Transaction::factory()->create(['note' => 'Coffee with friends']);

        // Act
        request()->merge(['filter' => ['search' => 'coffee']]);
        $result = $this->service->getPaginated(perPage: 10);

        // Assert
        $this->assertCount(1, $result->items());
        $this->assertEquals($matchingTransaction->id, $result->items()[0]->id);
    }

    public function test_it_searches_by_brand_name(): void
    {
        // Arrange
        $category = Category::factory()->create();
        $starbucks = Brand::factory()->create(['name' => 'Starbucks', 'category_id' => $category->id]);
        $mcdonalds = Brand::factory()->create(['name' => 'McDonalds', 'category_id' => $category->id]);
        
        Transaction::factory()->create(['brand_id' => $mcdonalds->id]);
        $matchingTransaction = Transaction::factory()->create(['brand_id' => $starbucks->id]);

        // Act
        request()->merge(['filter' => ['search' => 'starbucks']]);
        $result = $this->service->getPaginated(perPage: 10);

        // Assert
        $this->assertCount(1, $result->items());
        $this->assertEquals($matchingTransaction->id, $result->items()[0]->id);
        $this->assertEquals('Starbucks', $result->items()[0]->brand->name);
    }

    public function test_it_searches_across_multiple_fields(): void
    {
        // Arrange
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

        // Act
        request()->merge(['filter' => ['search' => 'coffee']]);
        $result = $this->service->getPaginated(perPage: 10);

        // Assert
        $this->assertCount(2, $result->items());
        $ids = collect($result->items())->pluck('id')->toArray();
        $this->assertContains($t1->id, $ids);
        $this->assertContains($t2->id, $ids);
        $this->assertNotContains($t3->id, $ids);
    }

    public function test_it_returns_empty_result_when_search_has_no_matches(): void
    {
        // Arrange
        Transaction::factory()->count(5)->create();

        // Act
        request()->merge(['filter' => ['search' => 'nonexistent']]);
        $result = $this->service->getPaginated(perPage: 10);

        // Assert
        $this->assertCount(0, $result->items());
        $this->assertEquals(0, $result->total());
    }

    public function test_it_allows_sorting_by_amount_ascending(): void
    {
        // Arrange
        $t1 = Transaction::factory()->create(['amount' => 300]);
        $t2 = Transaction::factory()->create(['amount' => 100]);
        $t3 = Transaction::factory()->create(['amount' => 200]);

        // Act
        request()->merge(['sort' => 'amount']);
        $result = $this->service->getPaginated(perPage: 10);

        // Assert
        $items = $result->items();
        $this->assertEquals($t2->id, $items[0]->id);
        $this->assertEquals($t3->id, $items[1]->id);
        $this->assertEquals($t1->id, $items[2]->id);
    }

    public function test_it_allows_sorting_by_amount_descending(): void
    {
        // Arrange
        $t1 = Transaction::factory()->create(['amount' => 300]);
        $t2 = Transaction::factory()->create(['amount' => 100]);
        $t3 = Transaction::factory()->create(['amount' => 200]);

        // Act
        request()->merge(['sort' => '-amount']);
        $result = $this->service->getPaginated(perPage: 10);

        // Assert
        $items = $result->items();
        $this->assertEquals($t1->id, $items[0]->id);
        $this->assertEquals($t3->id, $items[1]->id);
        $this->assertEquals($t2->id, $items[2]->id);
    }

    public function test_it_allows_sorting_by_created_at(): void
    {
        // Arrange
        $t1 = Transaction::factory()->create(['created_at' => now()->subDays(2)]);
        $t2 = Transaction::factory()->create(['created_at' => now()->subDays(1)]);
        $t3 = Transaction::factory()->create(['created_at' => now()]);

        // Act
        request()->merge(['sort' => '-created_at']);
        $result = $this->service->getPaginated(perPage: 10);

        // Assert
        $items = $result->items();
        $this->assertEquals($t3->id, $items[0]->id);
        $this->assertEquals($t2->id, $items[1]->id);
        $this->assertEquals($t1->id, $items[2]->id);
    }

    public function test_it_respects_custom_per_page_parameter(): void
    {
        // Arrange
        Transaction::factory()->count(10)->create();

        // Act
        $result = $this->service->getPaginated(perPage: 3);

        // Assert
        $this->assertCount(3, $result->items());
        $this->assertEquals(3, $result->perPage());
        $this->assertTrue($result->hasMorePages());
    }
}

