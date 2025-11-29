<?php

namespace App\Http\Commands\Brand\UpdateBrandCommand;

use App\Domains\Brand\Services\BrandService;
use Illuminate\Support\Facades\DB;

class UpdateBrandCommandHandler
{
    public function __construct(
        private readonly BrandService $brandService
    ) {}

    public function handle(UpdateBrandCommand $command): UpdateBrandCommandResponse
    {
        return DB::transaction(function () use ($command) {
            $brand = $this->brandService->update($command->id, $command->data);
            return new UpdateBrandCommandResponse($brand);
        });
    }
}
