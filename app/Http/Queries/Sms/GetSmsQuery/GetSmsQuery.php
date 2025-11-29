<?php

namespace App\Http\Queries\Sms\GetSmsQuery;

class GetSmsQuery
{
    public function __construct(
        public readonly int $perPage
    ) {}
}
