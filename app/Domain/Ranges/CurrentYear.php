<?php

namespace App\Domain\Ranges;

use App\Contracts\HasPreviousRange;

class CurrentYear extends Range implements HasPreviousRange
{
    public function start()
    {
        return now()->startOfYear()->format("Y-m-d");
    }
    
    public function end()
    {
        return now()->endOfYear()->format("Y-m-d");
    }

    public function previousRangeStart()
    {
        return now()->subYear()->startOfYear()->format("Y-m-d");
    }

    public function previousRangeEnd()
    {
        return now()->subYear()->endOfYear()->format("Y-m-d");
    }
}