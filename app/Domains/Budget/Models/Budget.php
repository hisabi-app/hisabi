<?php

namespace App\Domains\Budget\Models;

use App\Domains\Category\Models\Category;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Budget extends Model
{
    use HasFactory;

    const CUSTOM = "CUSTOM";
    const DAILY = "DAILY";
    const WEEKLY = "WEEKLY";
    const MONTHLY = "MONTHLY";
    const YEARLY = "YEARLY";

    protected $guarded = [];

    protected $appends = [
        'is_saving',
        'total_spent_percentage',
        'total_margin_per_day',
        'start_at_date',
        'end_at_date',
        'total_transactions_amount',
        'remaining_days',
        'remaining_to_spend',
        'elapsed_days_percentage',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function getIsSavingAttribute(): bool
    {
        return $this->saving;
    }

    public function getTotalSpentPercentageAttribute(): string
    {
        return (int) number_format($this->totalTransactionsAmount / $this->amount * 100, 2);
    }

    public function getTotalMarginPerDayAttribute(): string
    {
        $days = now()->diffInDays($this->end_at_date);
        $remainingAmount = $this->amount - $this->totalTransactionsAmount;

        if ($days < 0 || $remainingAmount <= 0) {
            return 0;
        }

        return $days == 0 ? number_format($remainingAmount, 2) : number_format($remainingAmount / $days, 2);
    }

    public function getRemainingDaysAttribute(): float
    {
        return now()->diffInDays($this->end_at_date);
    }

    public function getRemainingToSpendAttribute(): string
    {
        return $this->amount - $this->totalTransactionsAmount;
    }

    public function getElapsedDaysPercentageAttribute(): int
    {
        [$startAt, $endAt] = $this->getCurrentWindowStartAndEndDates();
        $totalDays = $startAt->diffInDays($endAt);

        if ($totalDays == 0) {
            return 0;
        }

        $elapsedDays = $startAt->diffInDays(now());
        $percentage = ($elapsedDays / $totalDays) * 100;

        return (int) max(0, min(100, $percentage));
    }

    public function getStartAtDateAttribute(): string
    {
        return $this->getCurrentWindowStartAndEndDates()[0]->format('Y-m-d');
    }

    public function getEndAtDateAttribute(): string
    {
        return $this->getCurrentWindowStartAndEndDates()[1]->format('Y-m-d');
    }

    public function getTotalTransactionsAmountAttribute()
    {
        // Several appended attributes read this; query once per model instance.
        return once(function () {
            [$startAt, $endAt] = $this->getCurrentWindowStartAndEndDates();

            return $this->categories()
                ->join('brands', 'categories.id', '=', 'brands.category_id')
                ->join('transactions', 'brands.id', '=', 'transactions.brand_id')
                ->whereBetween('transactions.created_at', [$startAt, $endAt])
                ->sum('transactions.amount');
        });
    }

    /**
     * The budget's windows that start between $from and $to (and have started by now), oldest first.
     * Windows before start_at are included too, stepping back from start_at by the same period.
     * A custom budget has a single window.
     *
     * @return array<int, array{0: \Carbon\Carbon, 1: \Carbon\Carbon}>
     */
    public function getWindowsStartingBetween(Carbon $from, Carbon $to): array
    {
        if ($this->reoccurrence === self::CUSTOM) {
            return [[$this->start_at->copy(), $this->end_at->copy()]];
        }

        $unit = $this->getUnitMapping();
        $anchor = $this->start_at->copy()->startOfDay();

        $earlierStarts = [];
        for ($steps = 1; ($start = $this->stepBack($anchor, $unit, $steps))->gte($from); $steps++) {
            array_unshift($earlierStarts, $start);
        }

        $starts = [...$earlierStarts, ...CarbonPeriod::create($anchor, $this->period . ' ' . $unit, $to->copy()->min(now()))->toArray()];
        $starts = array_filter($starts, fn ($start) => $start->gte($from) && $start->lte($to) && now()->isAfter($start));

        return array_map(
            fn ($start) => [$start->copy(), $start->copy()->add($unit, $this->period)],
            array_values($starts)
        );
    }

    private function stepBack(Carbon $anchor, string $unit, int $steps): Carbon
    {
        // Keep month-end anchors on the month end instead of overflowing into the next month
        return match ($unit) {
            'month' => $anchor->copy()->subMonthsNoOverflow($this->period * $steps),
            'year' => $anchor->copy()->subYearsNoOverflow($this->period * $steps),
            default => $anchor->copy()->sub($unit, $this->period * $steps),
        };
    }

    private function getCurrentWindowStartAndEndDates()
    {
        if ($this->reoccurrence === self::CUSTOM) {
            return [$this->start_at, $this->end_at];
        }

        $unit = $this->getUnitMapping();
        $intervalString = $this->period . ' ' . $unit;
        $ranges = CarbonPeriod::create($this->start_at->startOfDay(), $intervalString, now()->copy()->add($unit, $this->period))->toArray();

        foreach (array_reverse($ranges) as $range) {
            if (now()->isAfter($range)) {
                return [$range->copy(), $range->copy()->add($unit, $this->period)];
            }
        }
    }

    private function getUnitMapping(): string
    {
        return [
            self::DAILY => 'day',
            self::WEEKLY => 'week',
            self::MONTHLY => 'month',
            self::YEARLY => 'year',
        ][$this->reoccurrence];
    }

    protected static function newFactory()
    {
        return \Database\Factories\BudgetFactory::new();
    }
}
