<?php

namespace Tests\Feature\Budgets;

use App\Domains\Transaction\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class BudgetPageTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_history_is_hidden_without_transactions(): void
    {
        $this->assertHasHistory(false);
    }

    public function test_history_is_hidden_with_less_than_a_month_of_transactions(): void
    {
        Carbon::setTestNow('2026-09-28 12:00:00');
        Transaction::factory()->create(['created_at' => '2026-09-01 10:00:00']);

        $this->assertHasHistory(false);
    }

    public function test_history_is_shown_with_more_than_a_month_of_transactions(): void
    {
        Carbon::setTestNow('2026-09-28 12:00:00');
        Transaction::factory()->create(['created_at' => '2026-08-20 10:00:00']);

        $this->assertHasHistory(true);
    }

    private function assertHasHistory(bool $expected): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/budgets')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Budget/Index')->where('hasHistory', $expected));
    }
}
