<?php

namespace App\Models;

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

    /**
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * @return bool
     */
    public function getIsSavingAttribute(): bool
    {
        return $this->saving;
    }

    /**
     * @return string
     */
    public function getTotalSpentPercentageAttribute(): string
    {
        return (int) number_format($this->totalTransactionsAmount / $this->amount * 100, 2);
    }

    /**
     * @return string
     */
    public function getTotalMarginPerDayAttribute(): string
    {
        $days = now()->diffInDays($this->end_at_date);
        $remainingAmount = $this->amount - $this->totalTransactionsAmount;

        if($days < 0 || $remainingAmount <= 0) {
            return 0;
        }

        return $days == 0 ? number_format($remainingAmount, 2) : number_format($remainingAmount / $days, 2);
    }

    /**
     * @return float
     */
    public function getRemainingDaysAttribute(): float
    {
        return now()->diffInDays($this->end_at_date);
    }

    /**
     * @return string
     */
    public function getRemainingToSpendAttribute(): string
    {
        return $this->amount - $this->totalTransactionsAmount;
    }

    /**
     * @return int
     */
    public function getElapsedDaysPercentageAttribute(): int
    {
        [$startAt, $endAt] = $this->getCurrentWindowStartAndEndDates();
        $totalDays = $startAt->diffInDays($endAt);
        
        if ($totalDays == 0) {
            return 0;
        }
        
        $elapsedDays = $startAt->diffInDays(now());
        $percentage = ($elapsedDays / $totalDays) * 100;
        
        // Clamp between 0 and 100
        return (int) max(0, min(100, $percentage));
    }

    /**
     * @return string
     */
    public function getStartAtDateAttribute(): string
    {
        return $this->getCurrentWindowStartAndEndDates()[0]->format('Y-m-d');
    }

    /**
     * @return string
     */
    public function getEndAtDateAttribute(): string
    {
        return $this->getCurrentWindowStartAndEndDates()[1]->format('Y-m-d');
    }

    /**
     * @return mixed
     */
    public function getTotalTransactionsAmountAttribute()
    {
        $categories = $this->categories()->with('transactions')->get();
        [$startAt, $endAt] = $this->getCurrentWindowStartAndEndDates();

        return $categories->sum(function ($category) use ($startAt, $endAt){
            return $category->transactions()
                ->whereBetween('transactions.created_at', [$startAt, $endAt])
                ->sum('amount');
        });
    }

    /**
     * @return array|void
     */
    private function getCurrentWindowStartAndEndDates()
    {
        if($this->reoccurrence === self::CUSTOM) {
            return [$this->start_at, $this->end_at];
        }

        $unit = $this->getUnitMapping();
        $intervalString = $this->period . ' ' . $unit;
        $ranges = CarbonPeriod::create($this->start_at->startOfDay(), $intervalString, now()->copy()->add($unit, $this->period))->toArray();

        foreach (array_reverse($ranges) as $range) {
            if(now()->isAfter($range)) {
                return [$range->copy(), $range->copy()->add($unit, $this->period)];
            }
        }
    }

    /**
     * @return string
     */
    private function getUnitMapping(): string
    {
        return [
            self::DAILY => 'day',
            self::WEEKLY => 'week',
            self::MONTHLY => 'month',
            self::YEARLY => 'year',
        ][$this->reoccurrence];
    }
}
