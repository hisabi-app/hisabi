<?php

namespace Tests\Unit\Http\Queries\Transaction;

use App\Domains\Transaction\Models\Transaction;
use App\Domains\Transaction\Services\TransactionService;
use App\Http\Queries\Transaction\GetTransactionsQuery\GetTransactionsQuery;
use App\Http\Queries\Transaction\GetTransactionsQuery\GetTransactionsQueryHandler;
use App\Http\Queries\Transaction\GetTransactionsQuery\GetTransactionsQueryResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetTransactionsQueryHandlerTest extends TestCase
{
    use RefreshDatabase;

    private GetTransactionsQueryHandler $handler;
    private TransactionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TransactionService();
        $this->handler = new GetTransactionsQueryHandler($this->service);
    }

    public function test_it_handles_query_and_returns_response(): void
    {
        // Arrange
        Transaction::factory()->count(3)->create();
        $query = new GetTransactionsQuery(perPage: 50);

        // Act
        $response = $this->handler->handle($query);

        // Assert
        $this->assertInstanceOf(GetTransactionsQueryResponse::class, $response);
    }

    public function test_it_passes_per_page_to_service(): void
    {
        // Arrange
        Transaction::factory()->count(10)->create();
        $query = new GetTransactionsQuery(perPage: 5);

        // Act
        $response = $this->handler->handle($query);
        $jsonResponse = $response->toResponse();
        $data = json_decode($jsonResponse->getContent(), true);

        // Assert
        $this->assertCount(5, $data['data']);
        $this->assertEquals(5, $data['paginatorInfo']['perPage']);
    }

    public function test_it_returns_correct_response_structure(): void
    {
        // Arrange
        $transaction = Transaction::factory()->create([
            'amount' => 100.50,
            'note' => 'Test note'
        ]);
        $query = new GetTransactionsQuery(perPage: 50);

        // Act
        $response = $this->handler->handle($query);
        $jsonResponse = $response->toResponse();
        $data = json_decode($jsonResponse->getContent(), true);

        // Assert
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('paginatorInfo', $data);
        
        // Check data structure
        $this->assertIsArray($data['data']);
        $this->assertCount(1, $data['data']);
        $this->assertEquals($transaction->id, $data['data'][0]['id']);
        $this->assertEquals(100.50, $data['data'][0]['amount']);
        $this->assertEquals('Test note', $data['data'][0]['note']);
        
        // Check paginator info structure
        $this->assertArrayHasKey('hasMorePages', $data['paginatorInfo']);
        $this->assertArrayHasKey('currentPage', $data['paginatorInfo']);
        $this->assertArrayHasKey('lastPage', $data['paginatorInfo']);
        $this->assertArrayHasKey('perPage', $data['paginatorInfo']);
        $this->assertArrayHasKey('total', $data['paginatorInfo']);
    }

    public function test_it_returns_pagination_info_correctly(): void
    {
        // Arrange
        Transaction::factory()->count(15)->create();
        $query = new GetTransactionsQuery(perPage: 10);

        // Act
        $response = $this->handler->handle($query);
        $jsonResponse = $response->toResponse();
        $data = json_decode($jsonResponse->getContent(), true);

        // Assert
        $this->assertTrue($data['paginatorInfo']['hasMorePages']);
        $this->assertEquals(1, $data['paginatorInfo']['currentPage']);
        $this->assertEquals(2, $data['paginatorInfo']['lastPage']);
        $this->assertEquals(10, $data['paginatorInfo']['perPage']);
        $this->assertEquals(15, $data['paginatorInfo']['total']);
    }

    public function test_it_indicates_no_more_pages_when_on_last_page(): void
    {
        // Arrange
        Transaction::factory()->count(5)->create();
        $query = new GetTransactionsQuery(perPage: 10);

        // Act
        $response = $this->handler->handle($query);
        $jsonResponse = $response->toResponse();
        $data = json_decode($jsonResponse->getContent(), true);

        // Assert
        $this->assertFalse($data['paginatorInfo']['hasMorePages']);
        $this->assertEquals(1, $data['paginatorInfo']['currentPage']);
        $this->assertEquals(1, $data['paginatorInfo']['lastPage']);
    }

    public function test_it_returns_empty_data_when_no_transactions(): void
    {
        // Arrange
        $query = new GetTransactionsQuery(perPage: 50);

        // Act
        $response = $this->handler->handle($query);
        $jsonResponse = $response->toResponse();
        $data = json_decode($jsonResponse->getContent(), true);

        // Assert
        $this->assertEmpty($data['data']);
        $this->assertEquals(0, $data['paginatorInfo']['total']);
        $this->assertFalse($data['paginatorInfo']['hasMorePages']);
    }

    public function test_it_includes_brand_and_category_in_response(): void
    {
        // Arrange
        $transaction = Transaction::factory()->create();
        $query = new GetTransactionsQuery(perPage: 50);

        // Act
        $response = $this->handler->handle($query);
        $jsonResponse = $response->toResponse();
        $data = json_decode($jsonResponse->getContent(), true);

        // Assert
        $this->assertArrayHasKey('brand', $data['data'][0]);
        $this->assertArrayHasKey('category', $data['data'][0]['brand']);
        $this->assertEquals($transaction->brand->name, $data['data'][0]['brand']['name']);
        $this->assertEquals($transaction->brand->category->name, $data['data'][0]['brand']['category']['name']);
    }

    public function test_it_respects_different_per_page_values(): void
    {
        // Arrange
        Transaction::factory()->count(100)->create();

        // Act & Assert
        $query1 = new GetTransactionsQuery(perPage: 10);
        $response1 = $this->handler->handle($query1);
        $data1 = json_decode($response1->toResponse()->getContent(), true);
        $this->assertCount(10, $data1['data']);
        $this->assertEquals(10, $data1['paginatorInfo']['perPage']);

        $query2 = new GetTransactionsQuery(perPage: 25);
        $response2 = $this->handler->handle($query2);
        $data2 = json_decode($response2->toResponse()->getContent(), true);
        $this->assertCount(25, $data2['data']);
        $this->assertEquals(25, $data2['paginatorInfo']['perPage']);

        $query3 = new GetTransactionsQuery(perPage: 100);
        $response3 = $this->handler->handle($query3);
        $data3 = json_decode($response3->toResponse()->getContent(), true);
        $this->assertCount(100, $data3['data']);
        $this->assertEquals(100, $data3['paginatorInfo']['perPage']);
    }

    public function test_response_returns_json_response(): void
    {
        // Arrange
        Transaction::factory()->create();
        $query = new GetTransactionsQuery(perPage: 50);

        // Act
        $response = $this->handler->handle($query);
        $jsonResponse = $response->toResponse();

        // Assert
        $this->assertEquals(200, $jsonResponse->getStatusCode());
        $this->assertEquals('application/json', $jsonResponse->headers->get('Content-Type'));
    }

    public function test_it_works_with_search_filters(): void
    {
        // Arrange
        Transaction::factory()->create(['note' => 'Coffee purchase']);
        Transaction::factory()->create(['note' => 'Grocery shopping']);
        
        request()->merge(['filter' => ['search' => 'coffee']]);
        $query = new GetTransactionsQuery(perPage: 50);

        // Act
        $response = $this->handler->handle($query);
        $jsonResponse = $response->toResponse();
        $data = json_decode($jsonResponse->getContent(), true);

        // Assert
        $this->assertCount(1, $data['data']);
        $this->assertStringContainsString('Coffee', $data['data'][0]['note']);
    }
}

