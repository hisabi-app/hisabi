<?php

namespace App\Http\Commands\Brand\DeleteBrandCommand;

use App\Domains\Brand\Services\BrandService;
use Illuminate\Support\Facades\DB;

class DeleteBrandCommandHandler
{
    public function __construct(
        private readonly BrandService $brandService
    ) {}

    public function handle(DeleteBrandCommand $command): DeleteBrandCommandResponse
    {
        return DB::transaction(function () use ($command) {
            $brand = $this->brandService->delete($command->id);
            return new DeleteBrandCommandResponse($brand);
        });
    }
}
