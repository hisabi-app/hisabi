<?php

namespace App\GraphQL\Queries;

class Notebook
{
    public function __invoke($_, array $args)
    {
        $user = auth()->user();
        
        return [
            'content' => $user->notebook_content ?? '',
            'updated_at' => $user->updated_at->toIso8601String(),
        ];
    }
}

