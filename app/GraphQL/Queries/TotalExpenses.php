<?php

namespace App\GraphQL\Queries;

use App\Models\Transaction;
use App\Domain\Metrics\ValueMetric;
use App\Contracts\HasPreviousRange;

class TotalExpenses extends ValueMetric
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args)
    {
        $rangeData = app('findRangeByKey', ["key" => $args['range']]);

        $query = Transaction::query()->expenses();

        if($rangeData) {
            $query->whereBetween('created_at', [$rangeData->start(), $rangeData->end()]);
        }

        $previous = 0;

        if(is_a($rangeData, HasPreviousRange::class)) {
            $previous = Transaction::query()
                ->expenses()
                ->whereBetween('created_at', [
                    $rangeData->previousRangeStart(), 
                    $rangeData->previousRangeEnd()
                ])->sum('amount');
        }

        return [
            'value' => $query->sum('amount'),
            'previous' => $previous
        ];
    }
}
