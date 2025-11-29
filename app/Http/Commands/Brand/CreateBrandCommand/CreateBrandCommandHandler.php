<?php

namespace App\Http\Commands\Brand\CreateBrandCommand;

use App\Domains\Brand\Services\BrandService;
use Illuminate\Support\Facades\DB;

class CreateBrandCommandHandler
{
    public function __construct(
        private readonly BrandService $brandService
    ) {}

    public function handle(CreateBrandCommand $command): CreateBrandCommandResponse
    {
        return DB::transaction(function () use ($command) {
            $brand = $this->brandService->create($command->data);
            return new CreateBrandCommandResponse($brand);
        });
    }
}
