<?php

namespace App\Http\Queries\Brand\GetAllBrandsQuery;

use App\Http\Resources\BrandResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

readonly class GetAllBrandsQueryResponse
{
    public function __construct(
        private Collection $brands
    ) {}

    public function toResponse(): JsonResponse
    {
        return response()->json([
            'data' => BrandResource::collection($this->brands),
        ]);
    }
}
