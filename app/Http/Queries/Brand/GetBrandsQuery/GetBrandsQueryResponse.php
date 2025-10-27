<?php

namespace App\Http\Queries\Brand\GetBrandsQuery;

use App\Http\Resources\BrandResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

readonly class GetBrandsQueryResponse
{
    public function __construct(
        private LengthAwarePaginator $brands
    ) {}

    public function toResponse(): JsonResponse
    {
        return response()->json([
            'data' => BrandResource::collection($this->brands->items()),
            'paginatorInfo' => [
                'hasMorePages' => $this->brands->hasMorePages(),
                'currentPage' => $this->brands->currentPage(),
                'lastPage' => $this->brands->lastPage(),
                'perPage' => $this->brands->perPage(),
                'total' => $this->brands->total(),
            ],
        ]);
    }
}
