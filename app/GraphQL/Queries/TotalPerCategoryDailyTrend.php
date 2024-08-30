<?php

namespace App\GraphQL\Queries;

use Carbon\Carbon;
use App\Models\Transaction;
use App\Domain\Ranges\LastMonth;
use Illuminate\Support\Facades\DB;
use App\Domain\Ranges\CurrentMonth;
use App\Domain\Metrics\RelationTrendMetric;

class TotalPerCategoryDailyTrend extends RelationTrendMetric
{
    protected $name = 'Daily Trend by Category';
    
    protected $relationGraphqlQuery = 'allCategories';
    protected $relationDisplayUsing = 'name';
    protected $relationForeignKey = 'id';

    public function ranges()
    {
        return [
            new CurrentMonth,
            new LastMonth,
        ];
    }

    public function __invoke($_, array $args)
    {
        $rangeData = app('findRangeByKey', ["key" => $args['range']]);
        $categoryId = $args['id'];

        $transactions = $this->getTransactionsByCategoryIdGroupedByDays($categoryId, $rangeData);

        return $this->prepareDailyResults($rangeData, $transactions);
    }

    /**
     * @param mixed $categoryId
     * @param mixed $rangeData
     * @return mixed
     */
    public function getTransactionsByCategoryIdGroupedByDays(mixed $categoryId, mixed $rangeData)
    {
        return Transaction::whereHas('brand.category', function ($query) use ($categoryId) {
            $query->where('id', $categoryId);
        })
            ->whereBetween('transactions.created_at', [$rangeData->start(), $rangeData->end()])
            ->select(DB::raw("date_format(created_at, '%Y-%m-%d') as label, SUM(transactions.amount) as value"))
            ->groupBy(DB::raw("label"))
            ->orderBy('label')
            ->get()
            ->keyBy('label');
    }

    /**
     * @param mixed $rangeData
     * @param $transactions
     * @return array
     */
    public function prepareDailyResults(mixed $rangeData, $transactions): array
    {
        $startDate = Carbon::parse($rangeData->start());
        $endDate = Carbon::parse($rangeData->end());
        $currentDate = $startDate->copy();
        $results = [];

        while ($currentDate->lte($endDate)) {
            $date = $currentDate->format('Y-m-d');
            $results[] = [
                'label' => $date,
                'value' => $transactions->get($date)->value ?? 0,
            ];
            $currentDate->addDay();
        }

        return $results;
    }
}
