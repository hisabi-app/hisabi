<?php

namespace App\Domains\Brand\Services;

use App\Domains\Brand\Models\Brand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
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

    public function getAll(): Collection
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
            ->get();
    }

    public function create(array $data): Brand
    {
        return Brand::create($data);
    }

    public function update(int $id, array $data): Brand
    {
        $brand = Brand::findOrFail($id);
        $brand->update($data);
        return $brand;
    }
}
