<?php

namespace App\Http\Commands\Home\DismissSuggestionCommand;

use App\Models\User;

class DismissSuggestionCommand
{
    public function __construct(
        public readonly User $user,
        public readonly string $key
    ) {}
}
