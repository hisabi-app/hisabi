<?php

namespace Tests\Feature\Budgets;

use App\Domains\Brand\Models\Brand;
use App\Domains\Budget\Models\Budget;
use App\Domains\Budget\Services\BudgetService;
use App\Domains\Category\Models\Category;
use App\Domains\Transaction\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CurrentBudgetTotalsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-14 18:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_current_budgets_load_their_totals_in_one_query(): void
    {
        foreach (range(1, 5) as $i) {
            $category = Category::factory()->create();
            $brand = Brand::factory()->create(['category_id' => $category->id]);
            Transaction::factory()->create(['brand_id' => $brand->id, 'amount' => 100 * $i, 'created_at' => '2026-11-10 09:00:00']);
            // Outside every budget's current window
            Transaction::factory()->create(['brand_id' => $brand->id, 'amount' => 999, 'created_at' => '2026-10-10 09:00:00']);

            $reoccurrence = $i % 2 ? Budget::MONTHLY : Budget::WEEKLY;
            Budget::factory()->create(['amount' => 1000, 'start_at' => '2026-01-05', 'reoccurrence' => $reoccurrence])->categories()->attach($category->id);
        }

        DB::enableQueryLog();
        $budgets = app(BudgetService::class)->getCurrent();
        $budgets->each(fn (Budget $budget) => $budget->status());
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Budgets, their categories, and every total at once
        $this->assertLessThanOrEqual(3, $queries);

        // The same totals as working them out one budget at a time
        foreach ($budgets as $budget) {
            $this->assertEquals(Budget::find($budget->id)->total_transactions_amount, $budget->total_transactions_amount);
        }
    }
}
