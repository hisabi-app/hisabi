<?php

namespace App\Http\Commands\User\UpdateUserProfileCommand;

use App\Models\User;
use Illuminate\Http\JsonResponse;

readonly class UpdateUserProfileCommandResponse
{
    public function __construct(
        private User $user
    ) {}

    public function toResponse(): JsonResponse
    {
        return response()->json([
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
        ], 200);
    }
}
