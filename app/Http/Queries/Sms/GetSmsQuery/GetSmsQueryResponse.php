<?php

namespace App\Http\Queries\Sms\GetSmsQuery;

use App\Http\Resources\SmsResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

readonly class GetSmsQueryResponse
{
    public function __construct(
        private LengthAwarePaginator $sms
    ) {}

    public function toResponse(): JsonResponse
    {
        return response()->json([
            'data' => SmsResource::collection($this->sms->items()),
            'paginatorInfo' => [
                'hasMorePages' => $this->sms->hasMorePages(),
                'currentPage' => $this->sms->currentPage(),
                'lastPage' => $this->sms->lastPage(),
                'perPage' => $this->sms->perPage(),
                'total' => $this->sms->total(),
            ],
        ]);
    }
}
