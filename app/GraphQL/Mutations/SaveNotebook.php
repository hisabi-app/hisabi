<?php

namespace App\GraphQL\Mutations;

class SaveNotebook
{
    public function __invoke($_, array $args)
    {
        $user = auth()->user();
        
        $user->update([
            'notebook_content' => $args['content'],
        ]);
        
        return [
            'content' => $user->notebook_content,
            'updated_at' => $user->updated_at->toIso8601String(),
        ];
    }
}

