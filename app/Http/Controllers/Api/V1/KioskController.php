<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Queries\Kiosk\GetKioskSummaryQuery\GetKioskSummaryQuery;
use App\Http\Queries\Kiosk\GetKioskSummaryQuery\GetKioskSummaryQueryHandler;
use Illuminate\Http\JsonResponse;

class KioskController extends Controller
{
    public function __construct(
        private readonly GetKioskSummaryQueryHandler $getKioskSummaryQueryHandler
    ) {}

    public function index(): JsonResponse
    {
        $query = new GetKioskSummaryQuery();

        return $this->getKioskSummaryQueryHandler->handle($query)->toResponse();
    }
}
