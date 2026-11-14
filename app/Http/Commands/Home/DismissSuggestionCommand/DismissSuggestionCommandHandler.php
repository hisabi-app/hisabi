<?php

namespace App\Http\Commands\Home\DismissSuggestionCommand;

use Illuminate\Support\Facades\DB;

class DismissSuggestionCommandHandler
{
    public function handle(DismissSuggestionCommand $command): DismissSuggestionCommandResponse
    {
        return DB::transaction(function () use ($command) {
            $command->user->dismissForThisMonth($command->key);

            return new DismissSuggestionCommandResponse($command->key);
        });
    }
}
