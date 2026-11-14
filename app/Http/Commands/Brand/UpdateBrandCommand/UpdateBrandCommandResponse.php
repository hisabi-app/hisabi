<?php

namespace App\Http\Commands\Brand\UpdateBrandCommand;

use App\Domains\Brand\Models\Brand;
use App\Http\Resources\BrandResource;
use Illuminate\Http\JsonResponse;

readonly class UpdateBrandCommandResponse
{
    public function __construct(
        private Brand $brand
    ) {}

    public function toResponse(): JsonResponse
    {
        return response()->json([
            'brand' => new BrandResource($this->brand->load('category')->loadCount('transactions')),
        ]);
    }
}
