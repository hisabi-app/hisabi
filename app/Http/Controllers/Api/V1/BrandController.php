<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Queries\Brand\GetBrandsQuery\GetBrandsQuery;
use App\Http\Queries\Brand\GetBrandsQuery\GetBrandsQueryHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function __construct(
        private readonly GetBrandsQueryHandler $getBrandsQueryHandler
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = new GetBrandsQuery(
            perPage: (int) $request->get('perPage', 50)
        );

        return $this->getBrandsQueryHandler->handle($query)->toResponse();
    }
}
