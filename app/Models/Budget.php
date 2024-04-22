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

    public function getStartAtDateAttribute()
    {
        return $this->getCurrentWindowStartAndEndDates()[0]->format('Y-m-d');
    }

    public function getEndAtDateAttribute()
    {
        return $this->getCurrentWindowStartAndEndDates()[1]->format('Y-m-d');
    }

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
