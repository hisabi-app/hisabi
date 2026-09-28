<?php

namespace App\Http\Queries\Budget\GetBudgetsHistoryQuery;

class GetBudgetsHistoryQuery
{
    public function __construct(
        public readonly ?string $from = null,
        public readonly ?string $to = null
    ) {}
}
