<?php

namespace App\Http\Queries\Home\GetHomeQuery;

use App\Models\User;

class GetHomeQuery
{
    public function __construct(
        public readonly User $user
    ) {}
}
