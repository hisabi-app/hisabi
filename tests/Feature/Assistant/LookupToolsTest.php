<?php

namespace Tests\Feature\Assistant;

use App\Ai\AnswerCards;
use App\Ai\Tools\Breakdown;
use App\Ai\Tools\Budgets;
use App\Ai\Tools\Spending;
use App\Ai\Tools\Transactions;
use App\Domains\Assistant\Services\LookupService;
use App\Domains\Brand\Models\Brand;
use App\Domains\Budget\Models\Budget;
use App\Domains\Category\Models\Category;
use App\Domains\Transaction\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;
use Tests\TestCase;

class LookupToolsTest extends TestCase
{
    use RefreshDatabase;

    private AnswerCards $cards;
    private Brand $carrefour;
    private Brand $spinneys;
    private Brand $talabat;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-17 18:00:00');

        $this->cards = new AnswerCards;

        $groceries = Category::factory()->create(['name' => 'Groceries', 'color' => 'green', 'icon' => 'ShoppingCart']);
        $restaurants = Category::factory()->create(['name' => 'Restaurants', 'color' => 'orange']);
        $this->carrefour = Brand::factory()->create(['name' => 'Carrefour', 'category_id' => $groceries->id]);
        $this->spinneys = Brand::factory()->create(['name' => 'Spinneys', 'category_id' => $groceries->id, 'aliases' => ['SPINNEYS DUBAI']]);
        $this->talabat = Brand::factory()->create(['name' => 'Talabat', 'category_id' => $restaurants->id]);

        // This month
        $this->spend($this->carrefour, 300, '2026-11-02 10:00:00', 'Birthday cake for Mum');
        $this->spend($this->carrefour, 200, '2026-11-10 10:00:00');
        $this->spend($this->spinneys, 150, '2026-11-15 10:00:00');
        $this->spend($this->talabat, 80, '2026-11-16 20:00:00');
        // Last month: up to the same point, and after it
        $this->spend($this->carrefour, 700, '2026-10-05 10:00:00');
        $this->spend($this->carrefour, 999, '2026-10-25 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_spending_this_month_is_compared_with_the_same_point_last_month(): void
    {
        $result = $this->lookUp(Spending::class, ['from' => '2026-11-01', 'to' => '2026-11-17', 'category' => 'groceries']);

        $this->assertSame(650, (int) $result['total']);
        $this->assertSame(700, (int) $result['previous']);
        $this->assertSame('50 less than this time in October', $result['comparison']);

        $card = $this->cards->pick([$result['card']])[0];
        $this->assertSame(AnswerCards::AMOUNT, $card['type']);
        $this->assertSame('Spent on Groceries', $card['label']);
        $this->assertSame(['difference' => 50.0, 'direction' => 'less', 'label' => 'than this time in October'], $card['comparison']);
        // Running totals for the month chart: this month to today, last month in full
        $this->assertCount(17, $card['daily']);
        $this->assertEquals(650, end($card['daily']));
        $this->assertCount(31, $card['previous_daily']);
    }

    public function test_a_whole_month_is_compared_with_the_month_before(): void
    {
        $result = $this->lookUp(Spending::class, ['from' => '2026-10-01', 'to' => '2026-10-31']);

        $this->assertSame(1699, (int) $result['total']);
        $this->assertNull($result['comparison']);
    }

    public function test_other_spans_are_compared_with_as_many_days_before(): void
    {
        $result = $this->lookUp(Spending::class, ['from' => '2026-11-09', 'to' => '2026-11-15']);

        $this->assertSame(350, (int) $result['total']);
        $this->assertSame(300, (int) $result['previous']);
        $this->assertSame('50 more than the week before', $result['comparison']);
        // Not a month, so no month chart
        $this->assertSame([], $this->cards->pick([$result['card']])[0]['daily']);
    }

    public function test_brands_are_found_by_alias_and_by_a_small_slip(): void
    {
        $byAlias = $this->lookUp(Spending::class, ['from' => '2026-11-01', 'to' => '2026-11-17', 'brand' => 'Spinneys Dubai Mall']);
        $bySlip = $this->lookUp(Spending::class, ['from' => '2026-11-01', 'to' => '2026-11-17', 'brand' => 'carefour']);

        $this->assertSame(150, (int) $byAlias['total']);
        $this->assertSame(500, (int) $bySlip['total']);
        $this->assertSame('Spent at Carrefour', $this->cards->pick([$bySlip['card']])[0]['label']);
    }

    public function test_an_unknown_name_comes_back_with_the_closest_names_and_no_card(): void
    {
        $result = $this->lookUp(Spending::class, ['from' => '2026-11-01', 'to' => '2026-11-17', 'category' => 'Restaurantz and cafes']);

        $this->assertStringContainsString('No category matches', $result['error']);
        $this->assertContains('Restaurants', $result['closest']);
        $this->assertSame([], $this->cards->pick(['c1']));
    }

    public function test_nothing_spent_stages_no_card(): void
    {
        $result = $this->lookUp(Spending::class, ['from' => '2026-09-01', 'to' => '2026-09-30']);

        $this->assertSame(0, (int) $result['total']);
        $this->assertNull($result['card']);
    }

    public function test_dates_are_checked_and_never_run_past_today(): void
    {
        $this->assertSame('Dates must be YYYY-MM-DD.', $this->lookUp(Spending::class, ['from' => 'last week', 'to' => '2026-11-17'])['error']);
        $this->assertArrayHasKey('error', $this->lookUp(Spending::class, ['from' => '2026-12-01', 'to' => '2026-12-31']));
        $this->assertSame('2026-11-17', $this->lookUp(Spending::class, ['from' => '2026-11-01', 'to' => '2026-11-30'])['to']);
    }

    public function test_breakdown_ranks_the_brands_inside_a_category(): void
    {
        $result = $this->lookUp(Breakdown::class, ['from' => '2026-11-01', 'to' => '2026-11-17', 'by' => 'brand', 'category' => 'Groceries']);

        $this->assertEquals([['name' => 'Carrefour', 'total' => 500, 'count' => 2], ['name' => 'Spinneys', 'total' => 150, 'count' => 1]], $result['rows']);

        $card = $this->cards->pick([$result['card']])[0];
        $this->assertSame('Groceries', $card['label']);
        $this->assertSame(['name' => 'Carrefour', 'total' => 500.0, 'color' => 'green', 'icon' => 'ShoppingCart'], $card['rows'][0]);
    }

    public function test_breakdown_by_category_covers_one_kind_of_money(): void
    {
        $salary = Brand::factory()->create(['category_id' => Category::factory()->create(['name' => 'Salary', 'type' => Category::INCOME])->id]);
        $this->spend($salary, 20000, '2026-11-01 09:00:00');

        $result = $this->lookUp(Breakdown::class, ['from' => '2026-11-01', 'to' => '2026-11-17', 'by' => 'category']);

        $this->assertSame(['Groceries', 'Restaurants'], array_column($result['rows'], 'name'));
    }

    public function test_budgets_come_with_their_status(): void
    {
        $budget = Budget::factory()->create(['name' => 'Food', 'amount' => 1000, 'start_at' => '2026-01-01', 'reoccurrence' => Budget::MONTHLY]);
        $budget->categories()->attach(Category::where('name', 'Groceries')->first());

        $result = $this->lookUp(Budgets::class, ['name' => 'food']);

        $this->assertSame('Food', $result['budgets'][0]['name']);
        $this->assertSame(650, (int) $result['budgets'][0]['spent']);
        $this->assertSame(350, (int) $result['budgets'][0]['left']);
        $this->assertSame('Food', $this->cards->pick([$result['card']])[0]['budgets'][0]['name']);
        $this->assertNull($this->lookUp(Budgets::class, ['name' => 'Holiday'])['card']);
    }

    public function test_transactions_list_the_largest_without_notes(): void
    {
        $result = $this->lookUp(Transactions::class, ['from' => '2026-11-01', 'to' => '2026-11-17', 'sort' => 'largest', 'limit' => 2]);

        $this->assertSame(4, $result['count']);
        $this->assertSame(730, (int) $result['total']);
        $this->assertSame([300, 200], array_map('intval', array_column($result['shown'], 'amount')));
        $this->assertStringNotContainsString('Birthday', json_encode($result));

        $card = $this->cards->pick([$result['card']])[0];
        $this->assertSame('Biggest', $card['label']);
        $this->assertSame('Carrefour', $card['rows'][0]['brand']);
        $this->assertSame('2026-11-02', $card['rows'][0]['date']);
    }

    public function test_cards_are_picked_in_order_without_made_up_or_repeated_ids(): void
    {
        $first = $this->cards->add(AnswerCards::BARS, ['label' => 'A', 'rows' => []]);
        $second = $this->cards->add(AnswerCards::BARS, ['label' => 'B', 'rows' => []]);

        $this->assertSame(['B', 'A'], array_column($this->cards->pick([$second, 'c99', $second, $first]), 'label'));
    }

    private function lookUp(string $tool, array $arguments): array
    {
        $result = (new $tool(app(LookupService::class), $this->cards))->handle(new Request($arguments));

        return json_decode((string) $result, true);
    }

    private function spend(Brand $brand, float $amount, string $at, ?string $note = null): void
    {
        Transaction::factory()->create(['brand_id' => $brand->id, 'amount' => $amount, 'created_at' => $at, 'note' => $note]);
    }
}
