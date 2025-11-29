<?php

namespace App\Domains\Budget\Services;

use App\Domains\Budget\Models\Budget;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\QueryBuilder;

class BudgetService
{
    public function getAll(): Collection
    {
        return QueryBuilder::for(Budget::class)
            ->allowedSorts(['id', 'name', 'amount', 'start_at'])
            ->get();
    }
}
