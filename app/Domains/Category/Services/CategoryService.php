<?php

namespace App\Domains\Category\Services;

use App\Domains\Category\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class CategoryService
{
    public function getAll(): Collection
    {
        return QueryBuilder::for(Category::class)
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('name', 'LIKE', "%$value%");
                }),
                AllowedFilter::exact('type'),
            ])
            ->allowedSorts(['id', 'name', 'type'])
            ->defaultSort('-id')
            ->withCount('transactions')
            ->get();
    }
}
