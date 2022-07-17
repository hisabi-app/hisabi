<?php

namespace App\GraphQL\Queries;

use App\Contracts\HasPreviousRange;
use App\Models\Transaction;
use App\Domain\Metrics\ValueMetric;

class TotalIncome extends ValueMetric
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args)
    {
        $rangeData = app('findRangeByKey', ["key" => $args['range']]);

        $query = Transaction::query()->income();

        if($rangeData) {
            $query->whereBetween('created_at', [$rangeData->start(), $rangeData->end()]);
        }

        $previous = 0;

        if(is_a($rangeData, HasPreviousRange::class)) {
            $previous = Transaction::query()
                ->income()
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
