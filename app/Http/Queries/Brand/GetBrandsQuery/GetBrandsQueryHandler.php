<?php

namespace App\Http\Queries\Brand\GetBrandsQuery;

use App\Domains\Brand\Services\BrandService;

class GetBrandsQueryHandler
{
    public function __construct(
        private readonly BrandService $brandService
    ) {}

    public function handle(GetBrandsQuery $query): GetBrandsQueryResponse
    {
        $brands = $this->brandService->getPaginated(
            perPage: $query->perPage
        );

        return new GetBrandsQueryResponse($brands);
    }
}
