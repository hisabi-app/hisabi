<?php

namespace App\Domain\Ranges;

use App\Contracts\HasPreviousRange;

class CurrentMonth extends Range implements HasPreviousRange
{
    public function start()
    {
        return now()->startOfMonth()->format("Y-m-d");
    }
    
    public function end()
    {
        return now()->endOfMonth()->format("Y-m-d");
    }

    public function previousRangeStart()
    {
        return now()->subMonth()->startOfMonth()->format("Y-m-d");
    }

    public function previousRangeEnd()
    {
        return now()->subMonth()->endOfMonth()->format("Y-m-d");
    }
}