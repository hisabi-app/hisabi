<?php

namespace App\Domains\Transaction\Services;

use App\Domains\Transaction\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class TransactionService
{
    public function getPaginated(int $perPage = 50): LengthAwarePaginator
    {
        return QueryBuilder::for(Transaction::class)
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function($q) use ($value) {
                        $q->where('amount', 'LIKE', "%$value%")
                            ->orWhere('note', 'LIKE', "%$value%")
                            ->orWhereHas('brand', function($builder) use($value) {
                                $builder->where('name', 'LIKE', "%$value%");
                            });
                    });
                }),
            ])
            ->allowedIncludes(['brand.category'])
            ->allowedSorts(['id', 'amount', 'created_at'])
            ->defaultSort('-id')
            ->with(['brand.category'])
            ->paginate($perPage);
    }
}

