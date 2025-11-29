<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Commands\Brand\CreateBrandCommand\CreateBrandCommand;
use App\Http\Commands\Brand\CreateBrandCommand\CreateBrandCommandHandler;
use App\Http\Commands\Brand\UpdateBrandCommand\UpdateBrandCommand;
use App\Http\Commands\Brand\UpdateBrandCommand\UpdateBrandCommandHandler;
use App\Http\Commands\Brand\DeleteBrandCommand\DeleteBrandCommand;
use App\Http\Commands\Brand\DeleteBrandCommand\DeleteBrandCommandHandler;
use App\Http\Queries\Brand\GetBrandsQuery\GetBrandsQuery;
use App\Http\Queries\Brand\GetBrandsQuery\GetBrandsQueryHandler;
use App\Http\Queries\Brand\GetAllBrandsQuery\GetAllBrandsQuery;
use App\Http\Queries\Brand\GetAllBrandsQuery\GetAllBrandsQueryHandler;
use App\Http\Requests\Api\V1\CreateBrandRequest;
use App\Http\Requests\Api\V1\UpdateBrandRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function __construct(
        private readonly GetBrandsQueryHandler $getBrandsQueryHandler,
        private readonly GetAllBrandsQueryHandler $getAllBrandsQueryHandler,
        private readonly CreateBrandCommandHandler $createBrandCommandHandler,
        private readonly UpdateBrandCommandHandler $updateBrandCommandHandler,
        private readonly DeleteBrandCommandHandler $deleteBrandCommandHandler
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = new GetBrandsQuery(
            perPage: (int) $request->get('perPage', 50)
        );

        return $this->getBrandsQueryHandler->handle($query)->toResponse();
    }

    public function all(): JsonResponse
    {
        $query = new GetAllBrandsQuery();

        return $this->getAllBrandsQueryHandler->handle($query)->toResponse();
    }

    public function store(CreateBrandRequest $request): JsonResponse
    {
        $command = new CreateBrandCommand(
            data: $request->validated()
        );

        return $this->createBrandCommandHandler->handle($command)->toResponse();
    }

    public function update(UpdateBrandRequest $request, int $id): JsonResponse
    {
        $command = new UpdateBrandCommand(
            id: $id,
            data: $request->validated()
        );

        return $this->updateBrandCommandHandler->handle($command)->toResponse();
    }

    public function destroy(int $id): JsonResponse
    {
        $command = new DeleteBrandCommand(
            id: $id
        );

        return $this->deleteBrandCommandHandler->handle($command)->toResponse();
    }
}
