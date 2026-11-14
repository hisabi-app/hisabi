<?php

namespace App\Http\Queries\Home\GetHomeQuery;

use App\Domains\Home\Services\HomeService;

class GetHomeQueryHandler
{
    public function __construct(
        private readonly HomeService $homeService
    ) {}

    public function handle(GetHomeQuery $query): GetHomeQueryResponse
    {
        return new GetHomeQueryResponse($this->homeService->getHome($query->user));
    }
}
