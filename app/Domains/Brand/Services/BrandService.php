<?php

namespace App\Domains\Brand\Services;

use App\Domains\Brand\Models\Brand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class BrandService
{
    public function getPaginated(int $perPage = 50): LengthAwarePaginator
    {
        return QueryBuilder::for(Brand::class)
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function($q) use ($value) {
                        $q->where('name', 'LIKE', "%$value%")
                            ->orWhereHas('category', function($builder) use($value) {
                                $builder->where('name', 'LIKE', "%$value%");
                            });
                    });
                }),
                AllowedFilter::exact('category_id'),
            ])
            ->allowedIncludes(['category'])
            ->allowedSorts(['id', 'name'])
            ->defaultSort('-id')
            ->with(['category'])
            ->withCount('transactions')
            ->paginate($perPage);
    }
}
