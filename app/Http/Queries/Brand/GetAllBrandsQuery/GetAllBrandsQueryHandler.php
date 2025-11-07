<?php

namespace App\Http\Queries\Brand\GetAllBrandsQuery;

use App\Domains\Brand\Services\BrandService;

class GetAllBrandsQueryHandler
{
    public function __construct(
        private readonly BrandService $brandService
    ) {}

    public function handle(GetAllBrandsQuery $query): GetAllBrandsQueryResponse
    {
        $brands = $this->brandService->getAll();

        return new GetAllBrandsQueryResponse($brands);
    }
}
