<?php

namespace App\Http\Commands\User\UpdateUserProfileCommand;

use App\Domains\User\Services\UserService;
use Illuminate\Support\Facades\DB;

class UpdateUserProfileCommandHandler
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function handle(UpdateUserProfileCommand $command): UpdateUserProfileCommandResponse
    {
        return DB::transaction(function () use ($command) {
            $user = $this->userService->updateProfile($command->userId, $command->data);
            return new UpdateUserProfileCommandResponse($user);
        });
    }
}
