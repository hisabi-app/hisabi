<?php

namespace App\Http\Commands\Brand\CreateBrandCommand;

use App\Domains\Brand\Models\Brand;
use Illuminate\Http\JsonResponse;

readonly class CreateBrandCommandResponse
{
    public function __construct(
        private Brand $brand
    ) {}

    public function toResponse(): JsonResponse
    {
        return response()->json([
            'brand' => $this->brand->load('category')->loadCount('transactions'),
        ], 201);
    }
}
