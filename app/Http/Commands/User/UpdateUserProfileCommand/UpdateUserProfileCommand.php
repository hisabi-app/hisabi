<?php

namespace App\Http\Commands\User\UpdateUserProfileCommand;

readonly class UpdateUserProfileCommand
{
    public function __construct(
        public int $userId,
        public array $data
    ) {}
}
