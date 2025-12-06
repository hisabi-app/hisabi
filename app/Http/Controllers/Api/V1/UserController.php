<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Commands\User\UpdateUserProfileCommand\UpdateUserProfileCommand;
use App\Http\Commands\User\UpdateUserProfileCommand\UpdateUserProfileCommandHandler;
use App\Http\Requests\Api\V1\UpdateUserProfileRequest;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        private readonly UpdateUserProfileCommandHandler $updateUserProfileCommandHandler
    ) {}

    public function updateProfile(UpdateUserProfileRequest $request): JsonResponse
    {
        $command = new UpdateUserProfileCommand(
            userId: $request->user()->id,
            data: $request->validated()
        );

        return $this->updateUserProfileCommandHandler->handle($command)->toResponse();
    }
}
