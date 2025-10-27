<?php

namespace App\Http\Queries\Brand\GetBrandsQuery;

class GetBrandsQuery
{
    public function __construct(
        public readonly int $perPage
    ) {}
}
