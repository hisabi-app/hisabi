<?php

namespace App\Http\Queries\Sms\GetSmsQuery;

use App\Domains\Sms\Services\SmsService;

class GetSmsQueryHandler
{
    public function __construct(
        private readonly SmsService $smsService
    ) {}

    public function handle(GetSmsQuery $query): GetSmsQueryResponse
    {
        $sms = $this->smsService->getPaginated(
            perPage: $query->perPage
        );

        return new GetSmsQueryResponse($sms);
    }
}
