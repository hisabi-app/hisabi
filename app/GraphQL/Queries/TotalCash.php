<?php

namespace App\GraphQL\Queries;

use App\Models\Transaction;
use App\Domain\Metrics\ValueMetric;

class TotalCash extends ValueMetric
{
    public function ranges()
    {
        return null;
    }

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args)
    {
        $income = Transaction::income()->sum('amount');
        $expenses = Transaction::expenses()->sum('amount');
        $investment = Transaction::investment()->sum('amount');
        $savings = Transaction::savings()->sum('amount');

        return [
            'value' => $income - ($expenses + $investment + $savings)
        ];
    }
}
