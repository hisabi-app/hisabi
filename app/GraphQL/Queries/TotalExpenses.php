<?php

namespace App\GraphQL\Queries;

use App\Models\Transaction;

class TotalExpenses
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

        return $query->sum('amount');
    }
}
