<?php

namespace App\Http\Queries\Kiosk\GetKioskSpeechQuery;

readonly class GetKioskSpeechQuery
{
    public function __construct(
        public string $speechId
    ) {}
}
